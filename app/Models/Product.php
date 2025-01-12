<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['name', 'unit_price', 'description', 'available_quantity','SKU'];

    protected $casts = [
        'available_quantity' => 'integer',
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product')->withPivot('quantity', 'subtotal');
    }
}
