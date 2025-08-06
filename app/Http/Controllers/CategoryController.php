<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        'image' => 'nullable|image|max:2048'
    ]);

    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('categories', 'public');
    }
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
        'image' => $imagePath
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
        'parent_id' => 'nullable|exists:product_categories,id',
        'image' => 'nullable|image|max:2048'
    ]);

    // Check for duplicate category under the same parent (excluding current)
    $exists = Category::where('name', $request->name)
        ->where('parent_id', $request->parent_id)
        ->where('id', '!=', $category->id)
        ->exists();

    if ($exists) {
        return response()->json([
            'message' => 'A category with the same name already exists under the selected parent.'
        ], 409); // Conflict
    }

    // Handle image upload and old image deletion
    if ($request->hasFile('image')) {
        // Delete old image from storage if it exists
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        // Store new image in `storage/app/public/categories`
        $imagePath = $request->file('image')->store('categories', 'public');
        $category->image = $imagePath;
    }

    // Update other fields
    $category->name = $request->name;
    $category->parent_id = $request->parent_id;
    $category->save();

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
