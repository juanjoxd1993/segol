<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
	protected $fillable = [
		'company_id',
		'client_id',
		'original_client_id',
		'client_name',
		'client_address',
		'voucher_type_id',
		'serie_number',
		'voucher_number',
		'issue_date',
		'currency_id',
		'payment_id',
		'ose',
		'igv_percentage',
		'total_perception',
		'total',
		'taxed_operation',
		'igv',
		'user',
		'created_at',
		'updated_at',
	];

	use SoftDeletes;
	protected $dates = ['deleted_at'];

	public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function client()
	{
		return $this->belongsTo(Client::class);
	}

	public function voucher_type()
	{
		return $this->belongsTo(VoucherType::class);
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}

	public function payment()
	{
		return $this->belongsTo(Payment::class);
	}

	public function voucher_details()
	{
		return $this->hasMany(VoucherDetail::class);
	}

	public function credit_note_reason()
	{
		return $this->belongsTo(CreditNoteReason::class);
	}
}
