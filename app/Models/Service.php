<?php

namespace App\Models;

use App\Models\ServiceReview;
use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{   
    use UuidModelTrait;
    
    public $incrementing = false;

    protected $fillable = ['user_id', 'category_id', 'sub_category_id', 'title', 'description', 'about', 'key_points',
        'price', 'delivery_time', 'delivery_time_unit', 'sub_category_id'];

    protected $casts = [
        'approved' => 'boolean'
    ];

    public function calculateRating()
    {
        $reviewsQuery = ServiceReview::whereServiceId($this->id);


        $count = $reviewsQuery->count();
        $average = number_format(
            (float)$reviewsQuery->sum('rating') / $count, 2, '.', ''
        );

        $this->rating = $average;
        $this->review_count = $count;
        $this->save();
    }

    // Accessors & Mutators    
    // public function setKeyPointsAttribute($value) 
    // {
    //     $this->attributes['key_points'] = json_encode($value);
    // }

    public function getKeyPointsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getDeliveryTimeUnitAttribute($value)
    {
        return ucfirst($this->delivery_time > 1 ? $value . "s" : $value);
    }

    public function category()
    {
        return $this->hasOne(ServiceCategory::class, 'id', 'category_id');
    }

    public function subCategory()
    {
        return $this->hasOne(ServiceSubCategory::class, 'id', 'sub_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    public function totalSales()
    {
        $sales = $this->hasMany(OrderItem::class);
        $price = $sales->sum('price');
        $tax = $sales->sum('tax');

        return $price + $tax;
    }
}
