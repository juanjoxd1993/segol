<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
	use SoftDeletes;
	protected $dates = ['deleted_at'];
	
    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function asists() {
        return $this->hasMany(Asist::class, 'employ_id');
    }

    public function benefits() {
        return $this->hasMany(Benefit::class, 'employ_id');
    }
}
