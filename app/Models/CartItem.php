<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class CartItem extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = 'cart_items';

    protected $fillable = [
        'cart_token',
        'product_id',
        'variant_id',
        'quantity',
        'created_at',
        'updated_at',
    ];
}
