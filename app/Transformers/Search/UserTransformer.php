<?php

namespace App\Transformers\Search;

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
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'type' => $user->type,
            'artist_name' => $user->artist_name,
            'address' => $user->address,
            'avatar' => $user->avatar,
            'thumbnail' => $user->thumbnail
        ];
    }
}