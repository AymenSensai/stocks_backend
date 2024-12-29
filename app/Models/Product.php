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
        'opening_stock',
        'reorder_point',
        'category',
        'selling_price',
        'cost_price',
        'image',
        'user_id',
    ];

    protected $appends = ['image'];

    /**
     * Get the user that owns the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image
            ? url('storage/' . $this->image)
            : null;
    }
}
