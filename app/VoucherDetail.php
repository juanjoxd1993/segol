<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherDetail extends Model
{
	use SoftDeletes;
	protected $dates = ['deleted_at'];

	public function voucher() {
		return $this->belongsTo(Voucher::class);
	}

	public function unit() {
		return $this->belongsTo(Unit::class);
	}
}
