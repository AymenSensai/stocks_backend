<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Fillable attributes
    protected $fillable = [
        'contact_id',
        'transaction_date',
        'notes',
        'user_id',
    ];

    // Relationships

    /**
     * Get the contact associated with the order.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the products associated with the order.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')
                    ->withPivot('quantity') // Only 'quantity' since price is removed
                    ->withTimestamps();
    }

    public function scopeSoldQuantities($query)
    {
        return $query->whereHas('products', function ($q) {
            $q->where('pivot.quantity', '<', 0); // Negative quantity indicates sales
        });
    }

    public function scopePurchasedQuantities($query)
    {
        return $query->whereHas('products', function ($q) {
            $q->where('pivot.quantity', '>', 0); // Positive quantity indicates purchases
        });
    }
}
