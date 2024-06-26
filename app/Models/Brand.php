<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\HasMany as PDOHasMany;
use Laravel\Sanctum\HasApiTokens;

class Brand extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = 'brands';

    public function product(): PDOHasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    protected $fillable = [
        'name',
        'image_url',
        'created_at',
        'updated_at',
    ];
}
