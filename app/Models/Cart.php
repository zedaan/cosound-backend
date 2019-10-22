<?php

namespace App\Models;

use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public $timestamps = true;

    public function service()
    {
    	return $this->belongsTo(Service::class);
    }

    public function subTotal($userId)
    {
    	$sum = 0;
    	$ids = self::whereUserId($userId)->pluck('service_id');
    	if (empty($ids) || count($ids) <= 0) return $sum;

    	$ids = $ids->toArray();
    	return Service::whereIn('id', $ids)->sum('price');
    }
}
