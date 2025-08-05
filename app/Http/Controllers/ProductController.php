<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

 public function index()
{
    return response()->json(Product::all());
}
    public function loadProdyctForm()
{
    // This will render the Blade layout and mount the Vue component named 'AddRoom'
    return view('layouts.app', ['defaultComponent' => 'Product']);
}


public function store(Request $request)
{
    
    $validated = $request->validate([
        'name'              => 'required|string|max:255',
        'reference_number'  => 'nullable|string|max:255',
        'rfid_code'         => 'nullable|string|max:255',
        'unit_description'  => 'nullable|string|max:255',
        'price'             => 'nullable|numeric|min:0',
        'comment'           => 'nullable|string',
        'reorder_threshold' => 'nullable|numeric|min:0',
        'category_id'       => 'nullable|integer',

        // New fields:
        'weight_value'      => 'nullable|numeric|min:0',
        'weight_unit'       => 'nullable|string|max:50',
        'length'            => 'nullable|numeric|min:0',
        'width'             => 'nullable|numeric|min:0',
        'height'            => 'nullable|numeric|min:0',
    ]);

    try {
        $product = \App\Models\Product::create($validated);

        return response()->json([
            'message' => 'Product added successfully',
            'product' => $product
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to save product',
            'details' => $e->getMessage()
        ], 500);
    }
}

public function updateProduct(Request $request, $id)
{
    // Validate input
    $validated = $request->validate([
        'name'              => 'required|string|max:255',
        'category_id'       => 'nullable|exists:product_categories,id', // ✅ fixed table name
        'reference_number'  => 'nullable|string|max:255',
        'rfid_code'         => 'nullable|string|max:255',
        'unit_description'  => 'nullable|string|max:255',
        'price'             => 'nullable|numeric|min:0',
        'weight_value'      => 'nullable|numeric|min:0',
        'weight_unit'       => 'nullable|string|in:oz,g,kg,mg',
        'length'            => 'nullable|numeric|min:0',
        'width'             => 'nullable|numeric|min:0',
        'height'            => 'nullable|numeric|min:0',
        'comment'           => 'nullable|string',
        'reorder_threshold' => 'nullable|numeric|min:0',
    ]);

    // Find the product
    $product = Product::findOrFail($id);

    // Update the product
    $product->update($validated);

    return response()->json([
        'message' => 'Product updated successfully!',
        'product' => $product->load('category'), // optionally load related category
    ]);
}



public function productList()
{
    $products = Product::with('category')->get();

    return response()->json($products);
}

public function deleteProduct($id)
{
    // Find the product
    $product = Product::findOrFail($id);

    // Delete the product
    $product->delete();

    // Return success response
    return response()->json([
        'message' => 'Product deleted successfully!',
    ]);
}



}
