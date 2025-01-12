<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'user_id',
        'total_value',
        'status',
        'order_data',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity', 'subtotal');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
