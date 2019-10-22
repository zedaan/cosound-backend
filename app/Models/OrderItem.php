<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{   
    protected $fillable = ['order_id', 'user_id', 'service_id', 'price', 'tax'];

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function service()
    {
    	return $this->belongsTo(Service::class);
    }
}
