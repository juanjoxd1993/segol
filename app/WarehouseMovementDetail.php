<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseMovementDetail extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function warehouse_movement() {
        return $this->belongsTo(WarehouseMovement::class);
    }

    public function article() {
        return $this->belongsTo(Article::class, 'article_code');
    }
}
