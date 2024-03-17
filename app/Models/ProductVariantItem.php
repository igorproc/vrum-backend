<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo as PDOBelongsTo;

class ProductVariantItem extends Model
{
    use HasFactory;
    protected $table = 'product_variant_items';

    public function items(): PDOBelongsTo
    {
        return $this->belongsTo(ProductVariantGroup::class, 'product_variant_group_id');
    }

    protected $visible = [
        'id',
        'product_variant_group_id',
        'option_group_id',
        'option_item_id',
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'product_variant_group_id',
        'option_group_id',
        'option_item_id',
        'created_at',
        'updated_at',
    ];
}
