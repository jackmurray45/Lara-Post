<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Auth;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $appends = ['is_following', 'is_self'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getIsFollowingAttribute()
    {

        return !Auth::guest()  ? 
            in_array(Auth::user()->id, $this->followers()->pluck('following_user_id')->toArray()) : 
            -1;
    }

    public function getProfilePicAttribute($value)
    {
        return $this->attributes['profile_pic'] =  $this->attributes['profile_pic'] ? "/storage/$value" : null;
    }

    public function getBannerPicAttribute($value)
    {
        return $this->attributes['banner_pic'] =  $this->attributes['banner_pic'] ? "/storage/$value" : null;
    }

    public function getIsSelfAttribute()
    {
        return !Auth::guest() ? (Auth::user()->id == $this->id) : false;
    }

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function followers()
    {
        return $this->belongsToMany('App\User', 'follows', 'followed_user_id', 'following_user_id');
    }
    
    public function following()
    {
        return $this->belongsToMany('App\User', 'follows', 'following_user_id', 'followed_user_id');
    }

    public function followingPosts()
    {
        return $this->hasManyThrough(
            'App\Post',
            'App\Follow',
            'following_user_id',
            'user_id',
            'id',
            'followed_user_id'
        );
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    

}
