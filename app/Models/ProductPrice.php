<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_id',
        'cost_ex_vat',
        'retail_inc_vat',
        'discount_inc_vat',
        'skroutz_inc_vat',
        'wholesale_inc_vat',
        'silver_inc_vat',
        'gold_inc_vat',
    ];

    protected $casts = [
        'cost_ex_vat' => 'decimal:4',
        'retail_inc_vat' => 'decimal:4',
        'discount_inc_vat' => 'decimal:4',
        'skroutz_inc_vat' => 'decimal:4',
        'wholesale_inc_vat' => 'decimal:4',
        'silver_inc_vat' => 'decimal:4',
        'gold_inc_vat' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
