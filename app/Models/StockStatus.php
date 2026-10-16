<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockStatus extends Model
{
    protected $fillable = [
        'name',
        'skroutz_xml_name',
        'is_available',
        'is_purchasable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'is_purchasable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
