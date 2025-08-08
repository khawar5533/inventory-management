<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{   

    public function store(Request $request)
    {
        $order = PurchaseOrder::create([
            'order_number'   => 'PO-' . now()->format('Ymd-His'),
            'customer_name'  => auth()->user()->name,
            'status'         => $request->status ?? 'pending',
            'notes'          => $request->notes ?? 'Auto-generated from cart',
            'total_amount'   => 0 // start with zero
        ]);

        return response()->json([
            'message' => 'Purchase order created successfully.',
            'order'   => $order
        ], 201);
    }

    public function storeItems(Request $request, PurchaseOrder $order)
    {
        $items = $request->input('items');

        if (!is_array($items) || count($items) === 0) {
            return response()->json(['message' => 'No items provided.'], 422);
        }

        $savedItems = [];

        foreach ($items as $item) {
            $subtotal = floatval($item['quantity']) * floatval($item['unit_price']);

            $savedItems[] = $order->items()->create([
                'product_id'         => $item['product_id'],
                'lot_id'             => $item['lot_id'],
                'quantity'           => $item['quantity'],
                'unit_price'         => $item['unit_price'],
                'subtotal'           => $subtotal, // insert subtotal
                'purchase_order_id'  => $order->id
            ]);
        }

        return response()->json([
            'message' => 'Items added successfully.',
            'order'   => $order->load('items')
        ]);
    }


}
