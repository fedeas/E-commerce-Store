<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'mpn',
        'ean',
        'title',
        'slug',
        'brand_id',
        'category_id',
        'primary_supplier_id',
        'status',
        'vat_rate',
        'short_description',
        'description',
        'specs',
        'weight_kg',
        'dimensions',
        'main_image',
        'meta_description',
        'meta_keywords',
        'last_imported_at',
    ];

    protected $casts = [
        'specs' => 'array',
        'vat_rate' => 'decimal:2',
        'weight_kg' => 'decimal:3',
        'last_imported_at' => 'datetime',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function primarySupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    public function price(): HasOne
    {
        return $this->hasOne(ProductPrice::class);
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class, 'matched_product_id');
    }
}
