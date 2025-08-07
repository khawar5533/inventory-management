<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

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

