<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
	protected $fillable = ['ose'];

	use SoftDeletes;
	protected $dates = ['deleted_at'];
	
	public function company() {
		return $this->belongsTo(Company::class);
	}

	public function client() {
		return $this->belongsTo(Client::class);
	}

	public function voucher_type() {
		return $this->belongsTo(VoucherType::class);
	}

	public function currency() {
		return $this->belongsTo(Currency::class);
	}

	public function payment() {
		return $this->belongsTo(Payment::class);
	}

	public function voucher_details() {
		return $this->hasMany(VoucherDetail::class);
	}

	public function credit_note_reason() {
		return $this->belongsTo(CreditNoteReason::class);
	}
}
