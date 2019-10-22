<?php

namespace App\Models;

use Hash;

use Laravel\Cashier\Billable;

use Alsofronie\Uuid\UuidModelTrait;

use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Billable, Notifiable, UuidModelTrait;

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'first_name', 'last_name', 'type', 'artist_name', 'bio', 'dob', 'address',
        'latitude', 'longitude', 'postal_code', 'phone_numbers', 'social_links', 'avatar', 'confirmed_at', 
        'confirmation_code'
    ];

    protected $casts = [
        'social_links' => 'longText',
        'admin' => 'boolean'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'confirmation_code', 'updated_at'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst(strtolower($value));
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst(strtolower($value));
    }

    public function getLocationAttribute($value)
    {
        return json_decode($value);
    }

    public function getPhoneNumbersAttribute($value)
    {
        return json_decode($value);
    }

    public function getSocialLinksAttribute($value)
    {
        return json_decode($value);
    }

    public function getAvatarAttribute($value) {
        return $value !== null ? env('AWS_URL') . '/' . $value : null;
    }

    public function getThumbnailAttribute($value) {
        return $value !== null ? env('AWS_URL') . '/' . $value : null;
    }

    public function getTypeAttribute($value) {
        return ucfirst($value);
    }

    public function getIsConfirmedAttribute($value) {
        return $this->confirmation_code ? false : true;
    }


    /**
     * Relations
     */

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function country()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'follow_id', 'user_id')
                    ->withPivot('follow_id', 'user_id');
    }

    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follow_id')
                    ->withPivot('follow_id', 'user_id');
    }

    public function isFollowing($user_id)
    {
        return $this->followings()
            ->where('follow_id', $user_id)
            ->exists();
    }

    public function isFollowedBy($user_id)
    {
        return $this->followers()
            ->where('user_id', $user_id)
            ->exists();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function uploads()
    {
        return $this->hasMany(Upload::class)->where('uploadable_type', __NAMESPACE__ . '\Post');
    }

    public function likes()
    {
        return $this->belongsToMany(Post::class, 'likes');
    }

    public function hasLiked($post_id)
    {
        return $this->likes()
            ->where('post_id', $post_id)
            ->exists();
    }

    // Marketplace relations

    public function offeredServices()
    {
        return $this->hasMany(Service::class);
    }

    public function cart()
    {
        return $this->belongsToMany(Service::class, 'carts');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function purchasedItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
