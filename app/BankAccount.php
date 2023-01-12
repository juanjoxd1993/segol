<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function bank() {
        return $this->belongsTo(Bank::class);
    }

    public function bank_account_type() {
        return $this->belongsTo(BankAccountType::class);
    }
}
