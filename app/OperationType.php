<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationType extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function articles() {
        return $this->hasMany(Article::class);
    }
}
