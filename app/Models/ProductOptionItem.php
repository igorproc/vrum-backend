<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo as PDOBelongsTo;

class ProductOptionItem extends Model
{
    use HasFactory;

    public function group(): PDOBelongsTo
    {
        return $this->belongsTo(ProductOptionGroup::class, 'product_option_group_id');
    }

    protected $primaryKey = 'id';
    protected $fillable = [
        'product_option_group_id',
        'label',
        'value',
        'created_at',
        'updated_at'
    ];
    protected $visible = [
        'id',
        'product_option_group_id',
        'label',
        'value',
        'created_at',
        'updated_at'
    ];
}
