<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\HasMany as PDOHasMany;

class ProductVariantGroup extends Model
{
    use HasFactory;

    public function items(): PDOHasMany
    {
        return $this->hasMany(ProductVariantItem::class, 'product_variant_group_id');
    }

    protected $fillable = [
        'product_id',
        'sku',
        'image_url',
        'price',
        'created_at',
        'updated_at',
    ];
}
