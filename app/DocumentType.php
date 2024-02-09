<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentType extends Model
{
    use SoftDeletes;

	protected $dates = ['deleted_at'];

	public function company() {
		return $this->hasMany(Company::class);
	}

	public function client() {
		return $this->belongsTo(Client::class);
	}
}
