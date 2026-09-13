<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_id',
        'currency',
        'amount',
        'type',
        'billing_period',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
