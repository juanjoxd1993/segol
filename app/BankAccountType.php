<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccountType extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function bank_accounts() {
        return $this->hasMany(BankAccount::class);
    }
}
