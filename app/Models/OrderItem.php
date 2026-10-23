<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_price_id',
        'price',
        'license_type',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The linked ProductPrice plan for this order item.
     *
     * Named `productPrice` (not `price`) because a `price` decimal column
     * already exists on this model — defining a `price()` relation would be
     * permanently shadowed by the attribute, so `$item->price` would return the
     * decimal amount instead of the related ProductPrice model.
     */
    public function productPrice()
    {
        return $this->belongsTo(ProductPrice::class, 'product_price_id');
    }
}
