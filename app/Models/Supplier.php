<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'storefront_code',
        'website_url',
        'api_url',
        'product_data_url',
        'feed_type',
        'purchase_markup_percent',
        'sale_markup_percent',
        'available_text',
        'unavailable_text',
        'update_quantities',
        'update_purchase_price',
        'update_sale_price',
        'purchase_price_vat_mode',
        'sale_price_vat_mode',
        'auth_type',
        'username',
        'password',
        'api_token',
        'active',
        'last_sync_at',
        'last_bridge_sync_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'update_quantities' => 'boolean',
        'update_purchase_price' => 'boolean',
        'update_sale_price' => 'boolean',
        'purchase_markup_percent' => 'decimal:2',
        'sale_markup_percent' => 'decimal:2',
        'last_sync_at' => 'datetime',
        'last_bridge_sync_at' => 'datetime',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'primary_supplier_id');
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }
}
