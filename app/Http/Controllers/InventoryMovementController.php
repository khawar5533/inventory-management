<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryMovement;
use App\Models\ProductLot;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    
    // Load Blade view with Vue Product component
    public function loadAvailableItems()
    {
        return view('layouts.app', ['defaultComponent' => 'GetItems']);
    }
    public function loadCheckOut()
    {
        return view('layouts.app', ['defaultComponent' => 'CheckOut']);
    }
    // Display all list items
    public function availableItems()
    {
        $movements = InventoryMovement::with([
            'productLot.product',
            'productLot.box.rack.room.floor.location'
        ])
        ->whereHas('productLot', fn($q) => $q->where('quantity', '>', 0))
        ->get();

        return response()->json($movements);
    }
    

    /**
     * Handle check-in (adding stock).
     */

    public function checkIn(Request $request)
    {
        $request->validate([
            'lot_id' => 'required|exists:product_lots,id',
            'quantity' => 'required|integer|min:1',
            'movement_date' => 'required|date',
            'note' => 'nullable|string',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'batch_number' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // Update ProductLot quantity
            $lot = ProductLot::find($request->lot_id);
            $lot->quantity += $request->quantity;
            $lot->save();

            // Log Inventory Movement
            InventoryMovement::create([
                'lot_id' => $lot->id,
                'type' => 'check-in',
                'quantity' => $request->quantity,
                'movement_date' => $request->movement_date,
                'purchase_order_id' => $request->purchase_order_id,
                'note' => $request->note,
                'batch_number' => $request->batch_number,
            ]);
        });

        return response()->json(['message' => 'Stock checked in successfully.']);
    }

    /**
     * Handle check-out (removing stock).
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'lot_id' => 'required|exists:product_lots,id',
            'quantity' => 'required|integer|min:1',
            'movement_date' => 'required|date',
            'note' => 'nullable|string',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
        ]);

        $lot = ProductLot::find($request->lot_id);

        if ($lot->quantity < $request->quantity) {
            return response()->json(['error' => 'Not enough stock in this lot.'], 400);
        }

        DB::transaction(function () use ($request, $lot) {
            // Deduct from ProductLot quantity
            $lot->quantity -= $request->quantity;
            $lot->save();

            // Log Inventory Movement
            InventoryMovement::create([
                'lot_id' => $lot->id,
                'type' => 'check-out',
                'quantity' => $request->quantity,
                'movement_date' => $request->movement_date,
                'purchase_order_id' => $request->purchase_order_id,
                'note' => $request->note,
            ]);
        });

        return response()->json(['message' => 'Stock checked out successfully.']);
    }
}

