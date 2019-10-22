<?php

namespace App\Transformers\Marketplace;

use League\Fractal\TransformerAbstract;

use App\Models\{User, OrderItem};

class PurchasedServiceTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(OrderItem $orderItem)
    {
        $service = $orderItem->service;
        $user = $service->user;

        $category = $service->category;
        $subCategory = $service->subCategory;

        return [
            'id' => $orderItem->id,
            'service_id' => $service->id,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'sub_category' => [
                'id' => $subCategory->id,
                'name' => $subCategory->name,
                'slug' => $subCategory->slug,
            ],
            'title' => $service->title,
            'description' => $service->description,
            'media' => $service->media,
            // 'is_approved' => $service->approved,
            'is_featured' => $service->featured,
            'user' => [
                'id' => $user->id,
                'avatar' => $user->avatar,
                'thumbnail' => $user->thumbnail,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'type' => $user->type,
                'artist_name' => $user->artist_name,
            ],
        ];
    }
}