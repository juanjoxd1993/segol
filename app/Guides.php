<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guides extends Model
{
    use SoftDeletes;
	protected $dates = ['deleted_at'];

    public function movement_class() {
        return $this->belongsTo(MoventClass::class);
    }
    public function guides_serie() {
        return $this->belongsTo(GuidesSeries::class);
    }
    public function movement_type() {
        return $this->belongsTo(MoventType::class);
    }
    public function company() {
        return $this->belongsTo(Company::class);
    }
    public function warehouse_type() {
        return $this->belongsTo(WarehouseType::class);
    }

    public function movement_stock_type() {
        return $this->belongsTo(MovementStockType::class);
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

    public function warehouse_account_type() {
        return $this->belongsTo(WarehouseAccountType::class);
    }

    public function warehouse_document_type() {
        return $this->belongsTo(WarehouseDocumentType::class, 'referral_warehouse_document_type_id');
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function guides_details() {
        return $this->hasMany(GuidesDetail::class);
    }
}
