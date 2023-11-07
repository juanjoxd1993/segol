<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recursive extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function employ()
    {
        return $this->belongsTo(Employee::class, 'employ_id');
    }
}
