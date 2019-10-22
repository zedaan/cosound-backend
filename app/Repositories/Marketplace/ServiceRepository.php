<?php

namespace App\Repositories\Marketplace;

use App\Models\Service;

use App\Contracts\Marketplace\ServiceContract;

class ServiceRepository implements ServiceContract
{
	/**
	 * {@inheritdoc}
	 */
	public function create($data)
	{
		$serviceData = [];
		$serviceData = [
            'user_id' => array_get($data, 'user_id'),
            'category_id' => array_get($data, 'category_id'),
            'sub_category_id' => array_get($data, 'sub_category_id'),

            'title' => array_get($data, 'title'),
            'description' => array_get($data, 'description'),
            'about' => array_get($data, 'about'),
            'key_points' => array_get($data, 'key_points'),

            'price' => array_get($data, 'price'),
            'delivery_time' => array_get($data, 'delivery_time'),
            'delivery_time_unit' => array_get($data, 'delivery_time_unit'),

			'image' => array_get($data, 'image'),
			'featured_images' => array_get($data, 'featured_images', []),
		];

        $service = new Service;
		
		$service->fill($serviceData)->save();

		return $service;
	}
}
