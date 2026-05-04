<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = [];

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function equityTransactions()
    {
        return $this->hasMany(EquityTxn::class);
    }
}
