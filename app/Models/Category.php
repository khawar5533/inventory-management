<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model
{
    protected $table = 'product_categories';
    protected $fillable = ['name', 'parent_id'];
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
