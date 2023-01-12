<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

	public function sale_details() {
		return $this->hasMany(SaleDetail::class);
	}
}
