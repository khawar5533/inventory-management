<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Category; // Make sure this is included

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
}
