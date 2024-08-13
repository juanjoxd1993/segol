<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuidesDetail extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function guides() {
        return $this->belongsTo(Guide::class);
    }

    public function article() {
        return $this->belongsTo(Article::class, 'article_code');
    }
}
