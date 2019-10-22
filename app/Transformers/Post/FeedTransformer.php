<?php

namespace App\Transformers\Post;

use Auth;

use League\Fractal\TransformerAbstract;

class FeedTransformer extends TransformerAbstract
{
	public function transform($feed)
    {
        $authUser = Auth::user();

        $user = $feed->user;
        $post = $feed->post;

        $parentPostData = (object) null;

        if ($post->parent_id) {
            $parentPost = $post->parent;
            $parentPostUser = $parentPost->postedBy;

            $parentPostData->id = $parentPost->id;
            $parentPostData->body = $parentPost->body;
            $parentPostData->media = $parentPost->media;

            $parentPostData->user_id = $parentPostUser->id;
            $parentPostData->first_name = $parentPostUser->first_name;
        }

        return [
            'id' => $post->id,
            'body' => $post->body,
            'created_at' => $post->created_at,
            'like_count' => $post->like_count,
            'repost_count' => $post->repost_count,
            'comment_count' => $post->comment_count,
            'media'    => $post->media,
            'isLiked' => $authUser->hasLiked($post->id),

            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'artist_name' => $user->artist_name,
            'type' => $user->type,
            'avatar' => $user->avatar,
            'thumbnail' => $user->thumbnail,
            
            'parent' => $parentPostData
        ];
    }

}