<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function ubigeo() {
        return $this->belongsTo(Ubigeo::class);
    }

    public function perception_agent() {
        return $this->belongsTo(Rate::class);
    }
}
