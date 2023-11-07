<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAddress extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    
    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function address_type() {
        return $this->belongsTo(AddressType::class);
    }

    public function ubigeo() {
        return $this->belongsTo(Ubigeo::class);
    }
}
