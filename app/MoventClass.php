<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoventClass extends Model
{    
    public function warehouse_movements() {
        return $this->hasMany(WarehouseMovement::class);
    }
}
