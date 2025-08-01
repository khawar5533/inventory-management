<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category; 

class CategoryController extends Controller
{

public function loadCategoryForm()
{
    // This will render the Blade layout and mount the Vue component named 'AddRoom'
    return view('layouts.app', ['defaultComponent' => 'Category']);
}

public function index()
{
    $categories = Category::with('parent') // Optional: eager load parent category
                    ->orderBy('created_at', 'desc')
                    ->get();

    return response()->json($categories);
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'parent_id' => 'nullable|exists:product_categories,id',
    ]);

    // Check if a category with the same name exists (ignoring parent)
    $exists = Category::where('name', $request->name)->exists();

    if ($exists) {
        return response()->json([
            'message' => 'Category with this name already exists.',
        ], 422);
    }

    $category = Category::create([
        'name' => $request->name,
        'parent_id' => $request->parent_id,
    ]);

    return response()->json([
        'message' => 'Category added successfully.',
        'category' => $category->load('parent'), // Optional: if you defined a parent() relation
    ]);
}


public function update(Request $request, Category $category)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'parent_id' => 'nullable|exists:product_categories,id', // adjust table name if needed
    ]);

    // Check for duplicate name (excluding current category)
    $exists = Category::where('name', $request->name)
        ->where('parent_id', $request->parent_id)
        ->where('id', '!=', $category->id)
        ->exists();

    if ($exists) {
        return response()->json([
            'message' => 'A category with the same name already exists under the selected parent.'
        ], 409); // Conflict
    }

    // Proceed with update
    $category->update([
        'name' => $request->name,
        'parent_id' => $request->parent_id,
    ]);

    return response()->json([
        'message' => 'Category updated successfully.',
        'category' => $category->load('parent')
    ]);
}

public function destroy(Category $category)
{
    $category->delete();

    return response()->json([
        'message' => 'Category deleted successfully.',
    ]);
}

}
