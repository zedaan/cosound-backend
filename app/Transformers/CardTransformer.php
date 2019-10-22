<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class CardTransformer extends TransformerAbstract
{
	public function transform($user)
    {
        return [
            'card_brand' => $user->card_brand,
            'card_type' => $user->card_type,
            'card_last_four' => $user->card_last_four
        ];
    }
}