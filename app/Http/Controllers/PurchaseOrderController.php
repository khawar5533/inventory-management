<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Illuminate\Validation\ValidationException;
class PurchaseOrderController extends Controller
{   
   //Show the list of purchase order
    public function index()
    {
        return response()->json(['data' => PurchaseOrder::all()]);
    }
    // load the order form template
    public function loadOrderForm()
    {
        return view('layouts.app', ['defaultComponent' => 'PurchaseOrder']);
    }
    // create purchase order
     public function store(Request $request)
    {
        // Step 1: Validate input
        $validated = $request->validate([
            'order_number'   => 'required|string|unique:purchase_orders,order_number',
            'customer_name'  => 'required|string|max:255',
            'status'         => 'required|in:pending,partial,completed,cancelled',
            'notes'          => 'nullable|string'
        ]);

        // Step 2: Create and save purchase order
        $purchaseOrder = PurchaseOrder::create($validated);

        // Step 3: Return success response
        return response()->json([
            'message' => 'Purchase order created successfully.',
            'data' => $purchaseOrder
        ], 201);
    }

    //update purchase order 
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::find($id);

        if (!$purchaseOrder) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        try {
            $input = $request->except('order_number');

            $validated = validator($input, [
                'customer_name' => 'required|string|max:255',
                'status'        => 'required|in:pending,partial,completed,cancelled',
                'notes'         => 'nullable|string',
            ])->validate();

            $purchaseOrder->update($validated);

            return response()->json(['message' => 'Purchase order updated successfully']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
    //Delete Function
    public function destroy($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);

        if (!$purchaseOrder) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        $purchaseOrder->delete(); // ✅ now soft-deletes

        return response()->json(['message' => 'Purchase order soft deleted successfully']);
    }


}
