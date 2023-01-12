<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoventType extends Model
{
    public function warehouse_movements() {
        return $this->hasMany(WarehouseMovement::class);
    }
}
