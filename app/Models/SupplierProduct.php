<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    protected $fillable = [
        'supplier_id',
        'matched_product_id',
        'supplier_sku',
        'mpn',
        'ean',
        'brand_name',
        'title',
        'category_raw',
        'cost_ex_vat',
        'retail_inc_vat',
        'stock_quantity',
        'availability',
        'images',
        'raw_data',
        'match_status',
        'last_seen_at',
    ];

    protected $casts = [
        'cost_ex_vat' => 'decimal:4',
        'retail_inc_vat' => 'decimal:4',
        'stock_quantity' => 'integer',
        'images' => 'array',
        'raw_data' => 'array',
        'last_seen_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function matchedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'matched_product_id');
    }
}
