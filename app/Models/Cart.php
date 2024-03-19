<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Cart extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = 'carts';

    protected $fillable = [
        'cart_token',
        'is_guest_cart',
        'user_id',
        'created_at',
        'updated_at',
    ];
}
