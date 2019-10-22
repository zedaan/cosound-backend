<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'total', 'tax', 'description', 'invoice'];

    public function setDescriptionAttribute($value) 
    {
        $this->attributes['description'] = json_encode($value);
    }

    public function getDescriptionAttribute($value)
    {
        return json_decode($value);
    }

    public function setInvoiceAttribute($value) 
    {
        $this->attributes['invoice'] = json_encode($value);
    }

    public function getInvoiceAttribute($value)
    {
        return json_decode($value);
    }

    // Relations

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
