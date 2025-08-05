<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductLot;

class ProductLotController extends Controller
{
    /**
     * Display a listing of all product lots with related product and box.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $lots = ProductLot::with(['product:id,name', 'box:id,label'])->get();
        return response()->json($lots);
    }

    /**
     * Load the Blade view with the Vue component 'ProductLot'.
     *
     * @return \Illuminate\View\View
     */
    public function loadLotForm()
    {
        return view('layouts.app', ['defaultComponent' => 'ProductLot']);
    }

    /**
     * Store a newly created product lot in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'      => 'required|exists:products,id',
            'lot_number'      => 'required|string|max:255',
            'expiration_date' => 'nullable|date',
            'condition'       => 'required|in:new-sterile,open-box',
            'quantity'        => 'required|numeric|min:0',
            'box_id'          => 'required|exists:boxes,id',
        ]);

        $lot = ProductLot::create($validated);

        return response()->json([
            'message' => 'Product lot created successfully.',
            'lot'     => $lot
        ]);
    }

    /**
     * Update the specified product lot in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  ProductLot ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'lot_number'       => 'required|string|max:255',
            'expiration_date'  => 'nullable|date',
            'condition'        => 'nullable|string|max:255',
            'quantity'         => 'required|numeric|min:0',
            'box_id'           => 'required|exists:boxes,id',
        ]);

        $lot = ProductLot::find($id);

        if (!$lot) {
            return response()->json(['message' => 'Product lot not found'], 404);
        }

        $lot->update($validated);

        return response()->json([
            'message' => 'Product lot updated successfully',
            'data'    => $lot
        ]);
    }

    /**
     * Soft delete the specified product lot from the database.
     *
     * @param  int  $id  ProductLot ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $lot = ProductLot::find($id);

        if (!$lot) {
            return response()->json(['message' => 'Product lot not found'], 404);
        }

        $lot->delete();

        return response()->json(['message' => 'Product lot soft deleted successfully']);
    }
}
