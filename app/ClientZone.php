<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientZone extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];
}
