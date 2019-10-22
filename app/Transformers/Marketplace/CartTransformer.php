<?php

namespace App\Transformers\Marketplace;

use App\Models\Cart;
use League\Fractal\TransformerAbstract;

class CartTransformer extends TransformerAbstract
{
	public function transform(Cart $cart)
	{
		$service = $cart->service;
		$user = $service->user;

		return [
			'id' => $cart->id,
			'service_id' => $service->id,
			'title' => $service->title,
			'price' => $service->price,
			'rating' => $service->rating,
			'review_count' => $service->review_count,
			'media' => $service->media,
			'category' => [
 				'slug' => $service->category->slug
			],
			'sub_category' => [
				'slug' => $service->subCategory->slug
			],
			'user' => [
				'id' => $user->id,
				'avatar' => $user->avatar,
				'thumbnail' => $user->thumbnail,
			]
		];
	}
}