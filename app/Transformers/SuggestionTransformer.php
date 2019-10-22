<?php

namespace App\Transformers;

use Auth;

use League\Fractal\TransformerAbstract;

class SuggestionTransformer extends TransformerAbstract
{
	public function transform($user)
    {
        $authUser = Auth::user();

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'artist_name' => $user->artist_name,
            'avatar' => $user->avatar,
            'thumbnail' => $user->thumbnail,
            'isFollowed' => $user->isFollowedBy($authUser->id)
        ];
    }
}