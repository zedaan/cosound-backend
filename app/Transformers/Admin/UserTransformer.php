<?php

namespace App\Transformers\Admin;

use League\Fractal\TransformerAbstract;

use App\Models\User;

class UserTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(User $user)
    {   
        return [
            'id' => $user->id,
            // 'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'type' => $user->type,
            'artist_name' => $user->artist_name,
            'address' => $user->address,
            'avatar' => $user->avatar,
            'thumbnail' => $user->thumbnail,
            'followers_count' => $user->followersCount,
            // 'is_confirmed' => $user->isConfirmed,
            'is_admin' => $user->admin,
            'created_at' => $user->created_at,
        ];
    }
}