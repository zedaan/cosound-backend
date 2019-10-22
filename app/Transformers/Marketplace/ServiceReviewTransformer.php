<?php

namespace App\Transformers\Marketplace;

use League\Fractal\TransformerAbstract;

use App\Models\{User, ServiceReview};

class ServiceReviewTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(ServiceReview $review)
    {
        $userArray = [];

        $user = User::find($review->user_id);
        if ($user) {

            $userArray = [
                'id' => $user->id,
                'avatar' => $user->avatar,
                'thumbnail' => $user->thumbnail,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name
            ];
        }

        return [
            'id' => $review->id,
            'rating' => (int) $review->rating,
            'description' => $review->description,
            'creator' => $userArray,
            'created_at' => $review->created_at
        ];
    }
}
