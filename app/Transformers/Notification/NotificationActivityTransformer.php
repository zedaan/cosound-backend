<?php

namespace App\Transformers\Notification;

use League\Fractal\TransformerAbstract;

class NotificationActivityTransformer extends TransformerAbstract
{
	public function transform($activity)
    {
        $user = $activity->user;
        $comment = $activity->comment;
        // $post = $activity->post;

        return filterNullKeys([
            // 'post' => [
            //     'id' =>  $post->id
            // ],
            'comment' => $comment,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'avatar' => $user->avatar,
                'thumbnail' => $user->thumbnail,
            ]
        ]);
    }
}