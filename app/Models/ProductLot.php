<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class ProductLot extends Model
{
    use HasFactory;
    use SoftDeletes;
    // Define the table name if it's not the default "product_lots"
    protected $table = 'product_lots';

    // Define fillable fields for mass assignment
   protected $fillable = [
    'product_id', 'lot_number', 'expiration_date', 'condition', 'quantity', 'box_id'];
    // Define relationships (example: a lot belongs to a product)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'lot_id');
    }
}
