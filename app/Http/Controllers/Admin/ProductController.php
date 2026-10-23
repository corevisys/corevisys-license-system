<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('prices')->get();
        return Inertia::render('Admin/Products', ['products' => $products]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:products,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'prices' => 'required|array',
            'prices.*.currency' => 'required|string|size:3',
            'prices.*.amount' => 'required|numeric|min:0',
            'prices.*.type' => 'required|string|in:trial,full,subscription',
            'prices.*.billing_period' => 'nullable|integer',
        ]);

        $targetSlug = isset($data['slug']) && trim($data['slug']) !== ''
            ? Str::slug($data['slug'])
            : Str::slug($data['name']);

        if (!empty($data['id'])) {
            $existing = Product::findOrFail($data['id']);
            if ($existing->slug !== $targetSlug && $existing->licenses()->exists()) {
                throw ValidationException::withMessages([
                    'slug' => ['The product slug cannot be changed while licenses exist for this product.'],
                ]);
            }
        }

        $product = Product::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'name' => $data['name'],
                'slug' => $targetSlug,
                'description' => $data['description'],
                'is_active' => $data['is_active'],
            ]
        );

        // Sync prices
        $product->prices()->delete();
        foreach ($data['prices'] as $priceData) {
            $product->prices()->create($priceData);
        }

        return back()->with('success', 'Product updated successfully.');
    }

    public function delete(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }
}