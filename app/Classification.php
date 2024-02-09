<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classification extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function classification_type() {
        return $this->belongsTo(ClassificationType::class);
    }
}
