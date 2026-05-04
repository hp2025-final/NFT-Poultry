<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function prices()
    {
        return $this->hasMany(CustomerPrice::class);
    }
}
