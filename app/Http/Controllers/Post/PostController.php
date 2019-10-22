<?php

namespace App\Http\Controllers\Post;

use Auth, DB, Exception, Storage, Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Transformers\Post\CommentTransformer;
use App\Http\Controllers\BaseApiController;
use App\Models\{Comment, Post, Upload, User};
use App\Contracts\PostContract;
use App\Jobs\{DeleteMedia, DeletePost, ExtractMediaMetadata, Notification};
use App\Services\GetStreamService;

class PostController extends BaseApiController
{
    protected $user, $postContract, $getStreamService;

    protected $model = 'App\Models\Post';
    protected $transformer = 'App\Transformers\Post\PostTransformer';
    protected $per_page = 50;

    protected $selfNotify = true;

    public function __construct(PostContract $postContract, GetStreamService $getStreamService)
    {
        $this->user = Auth::user();
        $this->postContract = $postContract;
        $this->getStreamService = $getStreamService;
    }

    public function save(Request $request)
    {
        $user = $this->user;

        $filesMaxCount = 5;

        $data = request([
    		'body',
        'files',
        'metadatas'
        ]);

        if (isset($data['files']) && $data['files'] == "null") {
            $data['files'] = [];
        }

    	$rules = [
            'body' => 'required_without:files|string|nullable',
            'files' => 'required_without:body|array|nullable|max:' . $filesMaxCount
        ];

        $messages = [
            'body.required_without' => 'Post body is required if file isn\'t present!',
            'files.required_without' => 'File is required if post body isn\'t present!',
        ];

        foreach(range(0, $filesMaxCount - 1) as $index) {
            $rules['files.' . $index] = 'file|mimes:jpeg,png,gif,mp3,mpga,wav,mp4,avi,wmv||max:14648'; 
            $messages['files.' . $index . '.mimes'] = 'File ' . ($index+1) . ' should be of one of the following types: :values';
        }
       
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }
        
        $savedFiles = [];

        DB::beginTransaction();

        try {
            $postData = [];
            $postData['user_id'] = $user->id;
            $postData['body'] = $data['body'] ?? null;

            $post = $this->postContract->createPost($postData);
            
            if ($data['files'] ?? null) {
                $index = 0;
                foreach($data['files'] as $file) {
                    $mime = $file->getMimeType();
                    $fileType = explode("/",$mime)[0];
                    
                    $path = Storage::put('uploads/' . $fileType, $file, 'public');
                    $savedFiles[] = $path;
                    
                    $upload = new Upload;
                    
                    $upload['path'] = $path;
                    $upload['file_type'] = $fileType;
                    $upload['user_id'] = $user->id;
                    if($data['metadatas'][$index] == "null" || $data['metadatas'][$index] == "undefined")
                      $upload['metadata'] = null;
                    else $upload['metadata'] = $data['metadatas'][$index];
                    $post->media()->save($upload);
                    dispatch((new ExtractMediaMetadata($upload->id))->onQueue($fileType));
                    $index += 1;
                }
            }

            $data = [
                'actor' => "App\Models\User:" . $user->id,
                'verb' => 'post',
                'object' => "App\Models\Post:" . $post->id,
                'foreign_id' => 'post:' . $post->id,
                'time' => (string) $post->created_at
            ];
        
            $this->getStreamService->addUserFeed($user->id, $data);
            
            DB::commit();
            
            return $this->response->item($post, $this->transformer);

        } catch (Exception $e) {
            \Log::info($e);
            DB::rollback();
            foreach ($savedFiles as $file) {
                dispatch((new DeleteMedia($file))->onQueue('delete'));
            }

            return $this->errorInternal("Post couldn't be saved due to some internal error. Please try again!");
        }
    }

    public function repost(Request $request, $postId)
    {
        $user = $this->user;

        $parentPost = Post::find($postId);
        if (! $parentPost) {
            return $this->errorNotFound('This post has been deleted');
        }

        // Checking if parent post is a repost
        $parentPost = $parentPost->parent_id ? Post::findOrFail($parentPost->parent_id) : $parentPost;

        DB::beginTransaction();

        try {
            $postData = [];

            $postData['user_id'] = $user->id;
            $postData['parent_id'] = $parentPost->id;

            $post = $this->postContract->createPost($postData);
            $parentPost->increment('repost_count');

            $data = [
                'actor' => "App\Models\User:" . $user->id,
                'verb' => 'repost',
                'object' => "App\Models\Post:" . $post->id,
                'foreign_id' => 'post:' . $post->id,
                'time' => (string) $post->created_at
            ];

            $this->getStreamService->addUserFeed($user->id, $data);

            if ($this->selfNotify || $parentPost->user_id !== $user->id) {
                $notification = [
                    'actor' => "App\Models\User:" . $user->id,
                    'verb' => 'repost',
                    'object' => "App\Models\Post:" . $parentPost->id,
                    'foreign_id' => 'post:' . $post->id,
                ];
    
                dispatch((new Notification($parentPost->user_id, $notification, "send"))->onQueue('notification'));   
            }

            DB::commit();
            return $this->response->item($post, $this->transformer);

        } catch (Exception $e) {

            DB::rollback();
            return $this->errorInternal("Repost operation failed due to some internal error. Please try again!");
        }
    }

    public function fetch(Request $request)
    {
        $user = $this->user;
        $perPage = $request->has('per_page') ? $request->input('per_page') : 20;

        $posts = $user->posts()->orderBy('created_at', 'desc');

        $posts = $posts->paginate($perPage);

        return $this->response->paginator($posts, $this->transformer);
    }

    public function publicPosts(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return $this->response->errorNotFound("Specified User doesn't exist");
        }

        $perPage = $request->has('per_page') ? $request->input('per_page') : 20;

        $posts = $user->posts()->orderBy('created_at', 'desc');
        $posts = $posts->paginate($perPage);

        return $this->response->paginator($posts, $this->transformer);
    }

    public function fetchById(Request $request, $postId)
    {
        $user = $this->user;

        $post = Post::find($postId);
        if (! $post) {
            return $this->errorNotFound("Post has been removed by user");
        }

        return $this->response->item($post, $this->transformer);
    }

    public function processStatus(Request $request, $postId)
    {
        $user = $this->user;

        $post = Post::findOrFail($postId);

        $medias = $post->media;
        $processed = true;

        foreach($medias as $media) {
            if ($media->metadata === null) {
                $processed = false;
                break;
            }
        }

        return response()->json([
            'data' => $processed
        ]);
    }

    public function deletePost(Request $request, $postId)
    {
        $user = $this->user;

        $post = Post::find($postId);
        if (! $post) {
            return $this->errorNotFound('This post has been deleted');
        }

        if ($post->user_id !== $user->id) {
            return $this->errorUnauthorized("Post doesn't belong to authenticated user!");
        }

        foreach ($post->childs as $childPost) {
            $this->getStreamService->removeUserFeed($childPost->user_id, 'post:' . $childPost->id);
            $childPost->delete();
        }

        $this->getStreamService->removeUserFeed($post->user_id, 'post:' . $post->id);

        $parentPost = $post->parent;
        if ($parentPost)
            if ($this->selfNotify || $parentPost->user_id !== $user->id)
                dispatch((new Notification($parentPost->user_id, 'post:' . $post->id, "remove"))->onQueue('notification'));

        $post->delete();
        dispatch((new DeletePost($post->id))->onQueue('delete'));

        return response()->json([
            'data' => $post
        ]);
    }

    public function like(Request $request, $postId)
    {
        $user = $this->user;
        $post = Post::find($postId);

        if (!$post)
            return $this->errorNotFound('This post has been deleted');

        DB::beginTransaction();

        try {
            if ($user->hasLiked($postId)) {
                $user->likes()->detach($postId);
                $post->decrement('like_count');

                if ($this->selfNotify || $post->user_id !== $user->id)
                    dispatch((new Notification($post->user_id, 'like:' . $user->id . '_' . $post->id, "remove"))->onQueue('notification'));
            } else {
                $user->likes()->attach($postId);
                $post->increment('like_count');

                if ($this->selfNotify || $post->user_id !== $user->id) {
                    $notification = [
                        'actor' => "App\Models\User:" . $user->id,
                        'verb' => 'like',
                        'object' => "App\Models\Post:" . $post->id,
                        'foreign_id' => 'like:' . $user->id . '_' . $post->id,
                    ];

                    dispatch((new Notification($post->user_id, $notification, "send"))->onQueue('notification'));
                }
            }

            DB::commit();

            return $this->response->item($post, $this->transformer);

        } catch(Exception $e) {
            DB::rollback();
            return $this->errorInternal("Like operation failed due to some internal error. Please try again!");
        }
    }

    public function embed(Request $request, $postId)
    {
        $searchString = "/face|baidu|crawl|crawler|facebook|facebookexternalhit|facebot|twitter|twitterbot|google|google-structured-data-testing-tool|postman/i";

        $searchResult = preg_match($searchString, $_SERVER["HTTP_USER_AGENT"]);

        if (! $searchResult) {
            $redirectUrl = env('FRONT_END_URL') . "/posts/" . $postId;
            return redirect($redirectUrl);
        }

        $post = Post::findOrFail($postId);

        // Checking is embeded post is a repost
        $post = $post->parent_id ? Post::findOrFail($post->parent_id) : $post;

        $description = $post->body ?? "Cosound";
        $imageUrl = asset("images/logo.png");
        $media = null;
        $url = env('APP_URL')."/posts/$postId/embed";

        if (count($post->media) > 0) {
            $media = $post->media[0];

            if ($media->file_type === 'image')
                $imageUrl = $media->metadata->thumbnail_normal ?? $media->path;

            else if ($media->file_type === 'audio')
                $imageUrl = $media->metadata->albumart ?? $imageUrl;

            else if ($media->file_type === 'video')
                $imageUrl = $media->metadata->thumbnail ?? $imageUrl;
        }

        return view('embed', compact('post', 'post_id', 'description', 'imageUrl', 'media', 'url'));
    }

    public function postComment(Request $request, $postId)
    {
        $user = $this->user;
        $data = request(['comment']);

        $rules = [
            'comment' => 'required|string',
        ];
        
        $validator = Validator::make($data, $rules);
        if ($validator->fails() ) {
            return response()->json([
                'message' => 'Invalid Request',
                'error' => $validator->messages()
            ], 400);
        }

        $post = Post::find($postId);

        if (!$post)
            return $this->errorNotFound('This post has been deleted');

        DB::beginTransaction();

        try {
            $comment = new Comment;

            $comment['body'] = $data['comment'];
            $comment['user_id'] = $user->id;

            $post->comments()->save($comment);

            $notification = [
                'actor' => "App\Models\User:" . $user->id,
                'verb' => 'comment',
                'object' => "App\Models\Post:" . $post->id,
                'comment' => str_limit($comment['body'], 30),
                'foreign_id' => 'comment:' . $comment->id,
            ];

            if ($this->selfNotify || $post->user_id !== $user->id)
                dispatch((new Notification($post->user_id, $notification, "send"))->onQueue('notification'));

            DB::commit();
            
            $perPage = 3;
            $comments = $post->comments()->orderBy('created_at', 'desc')->paginate($perPage);
            return $this->response->paginator($comments, new CommentTransformer);
        } catch(Exception $e) {
            DB::rollback();
            return $this->errorInternal("Comment couldn\'t be saved due to some internal error. Please try again!");
        }
    }

    public function getComments(Request $request, $postId)
    {
        $perPage = 3;

        $post = Post::find($postId);

        if (!$post)
            return $this->errorNotFound('This post has been deleted');

        $comments = $post->comments()->orderBy('created_at', 'desc')->paginate($perPage);
        
        return $this->response->paginator($comments, new CommentTransformer);
    }

    public function deleteComment(Request $request, $postId, $comment_id)
    {
        $user = $this->user;
        $minId = request(['minId']);
        
        $comment = Comment::findOrFail($comment_id);

        if ($comment->user_id !== $user->id) {
            return $this->errorUnauthorized("Comment doesn't belong to authenticated user!");
        }

        $post = $comment->commentable;

        DB::beginTransaction();

        try {
            $return = $comment->delete();

            if ($this->selfNotify || $post->user_id !== $user->id)
                dispatch((new Notification($post->user_id, 'comment:' . $comment->id, "remove"))->onQueue('notification'));

            DB::commit();

            if ( is_null($minId)) {
                return response()->json([
                    'data' => $return
                ]);
            }

            $newComment = $post->comments()
                                ->where("id", '<', $minId)
                                ->orderBy('created_at','desc')
                                ->first();

            if( is_null($newComment)) {
            return response()->json([
                    'data' => $return
                ]);
            }   
                                        
            return $this->response->item($newComment, new CommentTransformer);
        } catch(Exception $e) {
            DB::rollback();
            return $this->errorInternal("Comment couldn\'t be deleted due to some internal error. Please try again!");
        }
    }
    public function editMusicInfo(Request $request, $id) {
      $user = $this->user;
      $data = request([
        'id',
        'metadata'
      ]);

      DB::beginTransaction();

      try {
        $upload = Upload::find($data['id']);
        $upload->metadata = $data['metadata'];
        $upload->save();
        
        DB::commit();

        return response()->json([
            'data' => 'success'
        ]);
      } catch(Exception $e) {
        DB::rollback();
        return $this->errorInternal("Comment couldn\'t be deleted due to some internal error. Please try again!");
      }
    }
}
