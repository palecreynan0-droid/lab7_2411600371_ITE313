<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',   // <-- added
        'name',
        'sku',
        'description',
        'price',
        'quantity',
        'reorder_level',
        'unit',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // added
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}