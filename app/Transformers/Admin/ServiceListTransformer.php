<?php

namespace App\Transformers\Admin;

use League\Fractal\TransformerAbstract;

use App\Models\Service;

class ServiceListTransformer extends TransformerAbstract
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
            'total_sales' => $service->totalSales(),
            'is_approved' => $service->approved,
            'created_at' => $service->created_at,
            'user' => [
                'id' => $user->id,
                'avatar' => $user->avatar,
                'thumbnail' => $user->thumbnail,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ],
        ];
    }
}