<?php

namespace App\Transformers\Notification;

use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract
{
	public function transform($notification)
    {
        $post = $notification->post;

        return [
            "id" => $notification->id,
            "activities" => $notification->activities,
            "activity_count" => $notification->activity_count,
            "actor_count" => $notification->actor_count,
            "updated_at" => $notification->updated_at,
            "is_read" => $notification->is_read,
            "is_seen" => $notification->is_seen,
            "post" => [
                'id' => $post->id
            ],
            "verb" => $notification->verb,
        ];
    }
}