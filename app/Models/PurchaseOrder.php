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
            'user_id',
            'order_number',
            'customer_name',
            'status',
            'notes',
            // add other fillable columns here
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

    // An order belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function lot()
    {
        return $this->belongsTo(ProductLot::class, 'lot_id', 'id');
    }
    
}

