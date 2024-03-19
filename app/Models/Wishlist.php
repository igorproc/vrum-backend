<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Wishlist extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = 'wishlists';

    protected $visible = [
        'wishlist_token'.
        'is_guest_cart',
        'user_id',
    ];

    protected $fillable = [
        'wishlist_token',
        'is_guest_cart',
        'user_id',
        'created_at',
        'updated_at',
    ];
}
