<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];
    public const CASH = 1;
    public const CREDIT = 2;

    public function voucher() {
        return $this->belongsTo(Voucher::class);
    }
}
