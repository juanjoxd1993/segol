<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VoucherDetail extends Model
{
	public function voucher() {
		return $this->belongsTo(Voucher::class);
	}

	public function unit() {
		return $this->belongsTo(Unit::class);
	}
}
