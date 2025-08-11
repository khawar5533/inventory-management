<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class OrderController extends Controller
{
    /**
     * Get all purchase orders with their items and stock quantity.
     * No filter on user_id since the column may not exist.
     */

public function getUserOrders()
{
    $orders = DB::table('purchase_orders as po')
        ->join('purchase_order_items as poi', 'po.id', '=', 'poi.purchase_order_id')
        ->join('product_lots as pl', 'poi.lot_id', '=', 'pl.id')
        ->leftJoin('products as p', 'pl.product_id', '=', 'p.id') // LEFT JOIN to avoid missing data
        ->select(
            'po.id as order_id',
            'po.order_number',
            'poi.lot_id',
            'p.name as product_name', // product name from products table
            'poi.quantity as ordered_quantity',
            'poi.unit_price',
            DB::raw('(poi.quantity * poi.unit_price) as subtotal'),
            'pl.quantity as stock_quantity'
        )
        ->orderBy('po.id', 'desc')
        ->get();

    return response()->json($orders);
}



    /**
     * Checkout an order:
     * - Deduct stock from product lots
     * - Mark the order as checked out
     */
public function checkoutOrder($orderId)
{
    DB::transaction(function () use ($orderId) {
        // Get all items in this order
        $items = DB::table('purchase_order_items')
            ->where('purchase_order_id', $orderId)
            ->get();

        foreach ($items as $item) {
            // 1 Insert a new check-out movement (quantity stays as is)
            DB::table('inventory_movements')->insert([
                'lot_id'            => $item->lot_id,
                'type'              => 'check-out',
                'quantity'          => $item->quantity,
                'movement_date'     => now(),
                'purchase_order_id' => $orderId,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // 2 Reduce quantity from the latest check-in record(s)
            $remainingQty = $item->quantity;

            $checkIns = DB::table('inventory_movements')
                ->where('lot_id', $item->lot_id)
                ->where('type', 'check-in')
                ->where('quantity', '>', 0)
                ->orderBy('movement_date', 'asc') // FIFO
                ->get();

            foreach ($checkIns as $checkIn) {
                if ($remainingQty <= 0) break;

                $deduct = min($checkIn->quantity, $remainingQty);

                DB::table('inventory_movements')
                    ->where('id', $checkIn->id)
                    ->update([
                        'quantity'   => $checkIn->quantity - $deduct,
                        'updated_at' => now(),
                    ]);

                $remainingQty -= $deduct;
            }
        }

        // 3 Mark order as completed (if column exists)
        if (Schema::hasColumn('purchase_orders', 'status')) {
            DB::table('purchase_orders')
                ->where('id', $orderId)
                ->update(['status' => 'completed']);
        }
    });

    return response()->json([
        'message' => 'Checkout complete, stock updated, and movement recorded.'
    ]);
}


}
