<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'contact_type',
        'user_id', // Add this for scoping contacts to users
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = preg_replace('/\D/', '', $value);
    }
}
