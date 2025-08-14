<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

// Make sure this is included

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'reference_number',
        'rfid_code',
        'unit_description',
        'price',
        'weight_value',
        'weight_unit',
        'length',
        'width',
        'height',
        'comment',
        'image',
        'reorder_threshold',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // A product has many lots
    public function lots()
    {
        return $this->hasMany(ProductLot::class);
    }

    public static function getLowStockProducts()
    {
        return DB::table('products as p')
            ->leftJoin('product_lots as pl', 'p.id', '=', 'pl.product_id')
            ->leftJoin('inventory_movements as im', function ($join) {
                $join->on('pl.id', '=', 'im.lot_id')
                     ->where('im.type', '=', 'check-in');
            })
            ->select(
                'p.name as product_name',
                DB::raw('COALESCE(SUM(im.quantity), 0) as total_quantity'),
                'p.reorder_threshold',
                DB::raw("CASE 
                            WHEN COALESCE(SUM(im.quantity), 0) < p.reorder_threshold 
                            THEN 'low' 
                            ELSE 'ok' 
                         END as stock_status")
            )
            ->groupBy('p.id', 'p.name', 'p.reorder_threshold')
            ->get();
    }
}
