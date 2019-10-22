<?php

namespace App\Transformers\Admin;

use League\Fractal\TransformerAbstract;

use App\Models\Service;

class ServiceTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Service $service)
    {
        $user = $service->user;

        $category = $service->category;
        $subCategory = $service->subCategory;

        return [
            'id' => $service->id,
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
            'about' => $service->about,
            'key_points' => $service->key_points,
            'price' => $service->price,
            'delivery_time' => $service->delivery_time,
            'delivery_time_unit' => $service->delivery_time_unit,
            'rating' => $service->rating,
            'review_count' => $service->review_count,
            'media' => $service->media,
            'total_sales' => $service->totalSales(),
            'is_approved' => $service->approved,
            'is_featured' => $service->featured,
            'created_at' => $service->created_at,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
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