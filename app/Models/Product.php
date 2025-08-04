<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'weight_oz',
        'dimensions',
        'comment',
        'reorder_threshold',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
}
