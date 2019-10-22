<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Alsofronie\Uuid\UuidModelTrait;

class Upload extends Model
{
    use UuidModelTrait;
    
    public $incrementing = false;

    protected $fillable = [
        'path', 'file_type', 'user_id'
    ];

    protected $hidden = [
        'uploadable_id', 'uploadable_type', 'updated_at'
    ];

    public function getPathAttribute($value) {
        return env('AWS_URL') . '/' . $value;
    }

    public function setMetadataAttribute($value) {
        $this->attributes['metadata'] = json_encode($value);
    }

    public function getMetadataAttribute($value) {
        return json_decode($value);
    }

    public function uploadable()
    {
        return $this->morphTo();
    }
}
