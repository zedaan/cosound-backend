<?php

namespace App\Transformers\Post;

use League\Fractal\TransformerAbstract;

use App\Models\Comment;

class CommentTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Comment $comment)
    {
        $user = $comment->user;
        
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'avatar' => $user->avatar,
            'thumbnail' => $user->thumbnail,
            'created_at' => $comment->created_at
        ];
    }
}