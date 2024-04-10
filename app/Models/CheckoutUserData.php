<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutUserData extends Model
{
    use HasFactory;
    protected $table = 'checkout_user_data';

    protected $fillable = [
        'checkout_id',
        'name',
        'surname',
        'country',
        'city',
        'address',
        'email',
        'created_at',
        'updated_at',
    ];
}
