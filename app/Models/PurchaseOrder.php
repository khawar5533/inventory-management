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
        return $this->hasMany(PurchaseOrderItem::class);
    }
}

