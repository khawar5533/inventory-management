<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function loadProdyctForm()
{
    // This will render the Blade layout and mount the Vue component named 'AddRoom'
    return view('layouts.app', ['defaultComponent' => 'Product']);
}


public function store(Request $request)
{
    // Validate the incoming request data
    $validated = $request->validate([
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'price'       => 'required|numeric|min:0',
        'quantity'    => 'required|integer|min:0',
        'category_id' => 'nullable|exists:product_categories,id',
    ]);

    // Create the product
    dd($validated);
    $product = Product::create($validated);

    // Return success response
    return response()->json([
        'message' => 'Product added successfully',
        'product' => $product,
    ]);
}

}
