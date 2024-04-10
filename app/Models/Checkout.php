<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;

    protected $table = 'checkouts';

    protected $fillable = [
        'order_id',
        'cart_token',
        'payment',
        'status',
        'created_at',
        'updated_at',
    ];
}
