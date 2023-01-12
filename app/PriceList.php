<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceList extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function warehouse_type() {
        return $this->belongsTo(WarehouseType::class);
    }
    
    public function article() {
        return $this->belongsTo(Article::class);
    }
}
