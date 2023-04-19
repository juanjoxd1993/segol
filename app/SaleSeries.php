<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleSeries extends Model
{
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	
	//public function company() {
	//	return $this->hasMany(Company::class);
	//}


	

}
