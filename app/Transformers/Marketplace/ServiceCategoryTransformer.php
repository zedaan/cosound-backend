<?php

namespace App\Transformers\Marketplace;

use League\Fractal\TransformerAbstract;

use App\Models\ServiceCategory;

class ServiceCategoryTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(ServiceCategory $category)
    {
        return [
            'value' => $category->id,
            'label' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sub_categories' => $category->subCategories,
        ];
    }
}