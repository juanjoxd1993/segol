<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
	use SoftDeletes;
	protected $dates = ['deleted_at'];

	public function user() {
		return $this->belongsTo(User::class);
	}

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function vouchers() {
    	return $this->hasMany(Voucher::class);
    }

    public function addresses() {
    	return $this->hasMany(Address::class);
    }
}
