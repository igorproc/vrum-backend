<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo as PDOBelongsTo;
use Laravel\Sanctum\HasApiTokens;

class Product extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = 'products';

    public function brand(): PDOBelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function configurableVariantGroups(): PDOBelongsTo
    {
        return $this->belongsTo(ProductVariantGroup::class, 'product_id');
    }

    protected $fillable = [
        '__typename',
        'name',
        'description',
        'price',
        'product_image',
        'brand_id',
        'created_at',
        'updated_at',
    ];
}
