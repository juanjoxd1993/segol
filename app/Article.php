<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function warehouse_type() {
        return $this->belongsTo(WarehouseType::class);
    }

    public function sale_unit() {
        return $this->belongsTo(Unit::class);
    }

    public function warehouse_unit() {
        return $this->belongsTo(Unit::class);
    }

    public function operation() {
        return $this->belongsTo(OperationType::class);
    }

    public function family() {
        return $this->belongsTo(Classification::class);
    }

    public function group() {
        return $this->belongsTo(Classification::class);
    }

    public function subgroup() {
        return $this->belongsTo(Classification::class);
    }

	public function sale_detail() {
		return $this->belongsTo(SaleDetail::class);
	}
}
