<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryMovement extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'lot_id', 'batch_number', 'type', 'quantity', 'movement_date', 'purchase_order_id'
    ];

    protected $casts = [
        'movement_date' => 'datetime',
    ];

    /**
     * Relationships
     */

    // Direct relation
    public function productLot()
    {
        return $this->belongsTo(ProductLot::class, 'lot_id');
    }

    // Access deeper relationships via productLot manually
    public function product()
    {
        return $this->hasOneThrough(Product::class, ProductLot::class, 'id', 'id', 'lot_id', 'product_id');
    }

    public function box()
    {
        return $this->hasOneThrough(Box::class, ProductLot::class, 'id', 'id', 'lot_id', 'box_id');
    }

    // The rest are not accessible directly using Eloquent relationships.
    // You can access them via:
    // $movement->productLot->box->rack->room->floor->location

    // OR define helper accessors for convenience:

    public function getRackAttribute()
    {
        return $this->box?->rack;
    }

    public function getRoomAttribute()
    {
        return $this->rack?->room;
    }

    public function getFloorAttribute()
    {
        return $this->room?->floor;
    }

    public function getLocationAttribute()
    {
        return $this->floor?->location;
    }
}
