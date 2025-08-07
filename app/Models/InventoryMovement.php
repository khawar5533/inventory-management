<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryMovement extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_movements';

   protected $fillable = [
    'lot_id', 'batch_number', 'type', 'quantity', 'movement_date', 'purchase_order_id'];

    protected $casts = [
        'movement_date' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function lot()
    {
        return $this->belongsTo(ProductLot::class, 'lot_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
}
