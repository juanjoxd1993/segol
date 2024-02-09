<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassificationType extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function classifications() {
        return $this->hasMany(Classification::class);
    }

    public function articles_family() {
        return $this->hasMany(Article::class);
    }

    public function articles_group() {
        return $this->hasMany(Article::class);
    }

    public function articles_subgroup() {
        return $this->hasMany(Article::class);
    }
}
