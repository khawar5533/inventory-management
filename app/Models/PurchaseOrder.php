<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class PurchaseOrder extends Model
{
    use HasFactory;
    use SoftDeletes; // Add this trait
    protected $fillable = [
        'order_number',
        'customer_name',
        'status',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id')
                ->with(['product', 'lot']);
    }
     // Purchase order relationship
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // Product relationship
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}

