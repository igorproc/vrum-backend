<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class WishlistItem extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = 'wishlist_items';

    protected $fillable = [
        'wishlist_token',
        'product_id',
        'variant_id',
        'created_at',
        'updated_at',
    ];
}
