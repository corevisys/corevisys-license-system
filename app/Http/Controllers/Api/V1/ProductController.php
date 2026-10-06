<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->with('prices')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function show($id)
    {
        $product = Product::where('is_active', true)
            ->with('prices')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }
}
