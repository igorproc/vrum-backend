<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutCart extends Model
{
    use HasFactory;

    protected $table = 'checkout_cart';

    protected $fillable = [
        'checkout_id',
        'product_id',
        'variant_id',
        'quantity',
        'created_at',
        'updated_at'
    ];
}
