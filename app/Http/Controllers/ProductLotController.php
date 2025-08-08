<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductLot;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

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
            'lot_number'      => 'required|regex:/^\d{1,4}$/',
            'expiration_date' => 'nullable|date',
            'lot_condition'   => 'nullable|in:new-sterile,open-box',
            'quantity'        => 'required|integer|min:1',
            'box_id'          => 'required|exists:boxes,id',
        ]);

        DB::beginTransaction();

        try {
            $today = now()->format('Ymd');
            $padded = str_pad($validated['lot_number'], 4, '0', STR_PAD_LEFT);
            $batchNumber = "BATCH-{$today}-{$padded}";

            //  Check if batch number already exists in product_lots
            $existingLot = ProductLot::where('lot_number', $batchNumber)->first();

            if ($existingLot) {
                // Check if inventory movement already exists for this batch
                $movementExists = InventoryMovement::where('batch_number', $batchNumber)
                    ->where('lot_id', $existingLot->id)
                    ->where('type', 'check-in')
                    ->exists();

                if ($movementExists) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'This batch already exists in inventory.'
                    ], 422);
                }

                //  Only add new movement if not already added
                InventoryMovement::create([
                    'lot_id'            => $existingLot->id,
                    'batch_number'      => $batchNumber,
                    'type'              => 'check-in',
                    'quantity'          => $validated['quantity'],
                    'movement_date'     => now(),
                    'purchase_order_id' => null,
                ]);

                DB::commit();
                return response()->json([
                    'message' => 'Stock added to existing lot.',
                    'lot'     => $existingLot,
                ]);
            }

            //  If no such batch exists, create new lot and movement
            $lot = ProductLot::create([
                'product_id'      => $validated['product_id'],
                'lot_number'      => $batchNumber,
                'expiration_date' => $validated['expiration_date'],
                'condition'       => $validated['lot_condition'] ?? 'new-sterile',
                'quantity'        => $validated['quantity'],
                'box_id'          => $validated['box_id'],
            ]);

            InventoryMovement::create([
                'lot_id'            => $lot->id,
                'batch_number'      => $batchNumber,
                'type'              => 'check-in',
                'quantity'          => $validated['quantity'],
                'movement_date'     => now(),
                'purchase_order_id' => null,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'New lot created and stock added.',
                'lot'     => $lot,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Lot creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred.',
                'error'   => $e->getMessage()
            ], 500);
        }
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
        $request->request->remove('lot_number');
        $request->request->remove('quantity');
        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'expiration_date'  => 'nullable|date',
            'condition'        => 'nullable|string|max:255',
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
     * Soft delete the specified product lot and inventory movement from the database.
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

        DB::beginTransaction();

        try {
            // Soft delete related inventory movements
            InventoryMovement::where('lot_id', $lot->id)->delete();

            // Soft delete the product lot itself
            $lot->delete();

            DB::commit();

            return response()->json(['message' => 'Product lot and related inventory movements deleted successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete records', 'error' => $e->getMessage()], 500);
        }
    }

}
