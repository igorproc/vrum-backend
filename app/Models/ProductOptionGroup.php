<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\HasMany as PDOHasMany;

class ProductOptionGroup extends Model
{
    use HasFactory;

    public function items(): PDOHasMany
    {
        return $this->hasMany(ProductOptionItem::class, 'product_option_group_id');
    }

    protected $fillable = [
        'product_id',
        'label',
        'created_at',
        'updated_at',
    ];
}
