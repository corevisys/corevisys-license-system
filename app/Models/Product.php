<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_active', 'type'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updating(function (Product $product) {
            if ($product->isDirty('slug') && $product->licenses()->exists()) {
                throw new \DomainException('The product slug cannot be changed while licenses exist for this product.');
            }
        });
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }
}
