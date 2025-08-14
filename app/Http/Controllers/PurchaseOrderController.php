<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\ProductLot;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    // Returns the Blade view
    public function dashboard()
    {
        return view('layouts.app', [
            'defaultComponent' => 'Content',
        ]);
    }

    // Returns JSON for Vue
    public function dashboardData()
    {
        return response()->json([
            'salesToday' => PurchaseOrder::salesToday(),
            'totalEarnings' => PurchaseOrder::totalEarnings(),
            'pendingOrders' => PurchaseOrder::pendingOrders(),
        ]);
    }

    public function store(Request $request)
    {
        $order = PurchaseOrder::create([
            'user_id' => auth()->id(),
            'order_number' => 'PO-'.now()->format('Ymd-His'),
            'customer_name' => auth()->user()->name,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes ?? 'Auto-generated from cart',
        ]);

        return response()->json([
            'message' => 'Purchase order created successfully.',
            'order' => $order,
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
                            $lotId = $movement->lot_id; //  Map to correct ProductLot ID
                        }
                    }
                }

                // If no valid ProductLot found, create one
                if (!$lotId) {
                    $lot = ProductLot::create([
                        'product_id' => $item['product_id'],
                        'lot_number' => 'LOT-'.now()->format('YmdHis').'-'.rand(1000, 9999),
                        'quantity' => $item['quantity'],
                    ]);
                    $lotId = $lot->id;
                }

                //  Create purchase order item with correct ProductLot ID
                PurchaseOrderItem::create([
                    'purchase_order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'lot_id' => $lotId,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Items added successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to insert items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // getgraph data
    public function getDashboardData()
    {
        $query = DB::table('purchase_orders as po')
            ->join('purchase_order_items as poi', 'po.id', '=', 'poi.purchase_order_id')
            ->selectRaw('MONTH(po.created_at) as month, SUM(poi.subtotal) as total')
            ->whereYear('po.created_at', now()->year)
            ->groupBy(DB::raw('MONTH(po.created_at)')); // repeat the raw expression

        // Debug SQL
        $sql = vsprintf(str_replace('?', '%s', $query->toSql()), $query->getBindings());
        // dd($sql);

        $sales = $query->pluck('total', 'month')->toArray();

        // fill missing months with 0
        $salesData = [];
        for ($i = 1; $i <= 12; ++$i) {
            $salesData[$i] = $sales[$i] ?? 0;
        }

        return response()->json([
            'salesThisMonth' => array_values($salesData),
        ]);
    }
}
