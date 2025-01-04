<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'sku',
        'stock', // Updated field
        'reorder_point',
        'category_id',
        'selling_price',
        'cost_price',
        'image',
        'user_id',
    ];

    // Define the relationship to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    protected $appends = ['image'];

    /**
     * Get the user that owns the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageAttribute()
    {
        return $this->attributes['image']
            ? url('storage/' . $this->attributes['image'])
            : null;
    }

    public function calculateEarnings()
    {
        return $this->orders()->wherePivot('quantity', '<', 0)
            ->get()
            ->sum(function ($order) {
                return $order->pivot->quantity * $this->selling_price;
            });
    }

    public function calculateSpendings()
    {
        return $this->orders()->wherePivot('quantity', '>', 0)
            ->get()
            ->sum(function ($order) {
                return $order->pivot->quantity * $this->cost_price;
            });
    }
}
