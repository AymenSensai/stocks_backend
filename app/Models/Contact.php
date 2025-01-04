<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // Fillable attributes
    protected $fillable = [
        'name',
        'phone_number',
        'contact_type',
    ];

    // Optionally, add a mutator for phone_number (if needed for formatting)
    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = preg_replace('/\D/', '', $value);  // removes non-digit characters
    }
}
