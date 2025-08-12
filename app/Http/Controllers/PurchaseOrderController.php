<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;   
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductLot;
use App\Models\InventoryMovement;
use Illuminate\Validation\ValidationException;


class PurchaseOrderController extends Controller
{   

    public function store(Request $request)
    {
        $order = PurchaseOrder::create([
            'user_id'       => auth()->id(),
            'order_number'   => 'PO-' . now()->format('Ymd-His'),
            'customer_name'  => auth()->user()->name,
            'status'         => $request->status ?? 'pending',
            'notes'          => $request->notes ?? 'Auto-generated from cart',
            
        ]);

        return response()->json([
            'message' => 'Purchase order created successfully.',
            'order'   => $order
        ], 201);
    }

    public function storeItems(Request $request, $orderId)
    {
        $items = $request->input('items');

        if (!is_array($items) || count($items) === 0) {
            return response()->json(['message' => 'No items provided.'], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($items as $item) {
                $lotId = null;

                //  If lot_id is sent, figure out if it's a ProductLot or InventoryMovement
                if (!empty($item['lot_id'])) {
                    // First check if it's already a valid ProductLot ID
                    $productLot = ProductLot::find($item['lot_id']);
                    if ($productLot) {
                        $lotId = $productLot->id;
                    } else {
                        // If not, maybe it's an InventoryMovement ID
                        $movement = InventoryMovement::find($item['lot_id']);
                        if ($movement && $movement->lot_id) {
                            $lotId = $movement->lot_id; // ✅ Map to correct ProductLot ID
                        }
                    }
                }

                // If no valid ProductLot found, create one
                if (!$lotId) {
                    $lot = ProductLot::create([
                        'product_id' => $item['product_id'],
                        'lot_number' => 'LOT-' . now()->format('YmdHis') . '-' . rand(1000, 9999),
                        'quantity'   => $item['quantity'],
                    ]);
                    $lotId = $lot->id;
                }

                //  Create purchase order item with correct ProductLot ID
                PurchaseOrderItem::create([
                    'purchase_order_id' => $orderId,
                    'product_id'        => $item['product_id'],
                    'lot_id'            => $lotId,
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'],
                    'subtotal'          => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Items added successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to insert items',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



}
