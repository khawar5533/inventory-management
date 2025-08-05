<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Return all products (no relationships loaded)
    public function index()
    {
        return response()->json(Product::all());
    }

    // Load Blade view with Vue Product component
    public function loadProdyctForm()
    {
        return view('layouts.app', ['defaultComponent' => 'Product']);
    }

    // Store a new product after validation
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
            'weight_value'      => 'nullable|numeric|min:0',
            'weight_unit'       => 'nullable|string|max:50',
            'length'            => 'nullable|numeric|min:0',
            'width'             => 'nullable|numeric|min:0',
            'height'            => 'nullable|numeric|min:0',
        ]);

        try {
            $product = Product::create($validated);

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

    // Update existing product by ID
    public function updateProduct(Request $request, $id)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'nullable|exists:product_categories,id',
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

        $product = Product::findOrFail($id);
        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully!',
            'product' => $product->load('category'),
        ]);
    }

    // Return products with related category data
    public function productList()
    {
        $products = Product::with('category')->get();
        return response()->json($products);
    }

    // Delete product by ID (soft delete if enabled)
    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully!',
        ]);
    }
    public function productItems()
    {
        // Return only id and name (for dropdown)
        $products = Product::select('id', 'name')->get();
        return response()->json($products);
    }
}
