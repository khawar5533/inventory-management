<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
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
            ->leftJoin('products as p', 'pl.product_id', '=', 'p.id')
            ->select(
                'po.id as order_id',
                'po.order_number',
                'po.status', 
                'poi.lot_id',
                'p.name as product_name',
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

            $orderTotal = 0;

            foreach ($items as $item) {
                // Add to total (assuming you have unit_price column)
                $orderTotal += $item->quantity * $item->unit_price;

                // 1. Insert a new check-out movement (quantity stays as is)
                DB::table('inventory_movements')->insert([
                    'lot_id'            => $item->lot_id,
                    'type'              => 'check-out',
                    'quantity'          => $item->quantity,
                    'movement_date'     => now(),
                    'purchase_order_id' => $orderId,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // 2. Reduce quantity from the latest check-in record(s)
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

            // 3. Mark order as completed and update total
            if (Schema::hasColumn('purchase_orders', 'status')) {
                DB::table('purchase_orders')
                    ->where('id', $orderId)
                    ->update([
                        'status' => 'completed',
                        'total'  => $orderTotal,
                        'updated_at' => now(),
                    ]);
            }
        });

        return response()->json([
            'message' => 'Checkout complete, total updated, stock adjusted, and movement recorded.'
        ]);
    }

    public function print($id)
    {
        $order = PurchaseOrder::with([
        'items.lot.product',
        'items.lot.box.rack.room.floor.location',
    ])->findOrFail($id);

        $pdf = PDF::loadView('orders.pdf', compact('order'));
        return $pdf->stream('order-'.$order->order_number.'.pdf');
    }

}



