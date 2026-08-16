<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'supplier_id',
        'warehouse_id',
        'name',
        'sku',
        'price',
        'stock',
        'description'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}