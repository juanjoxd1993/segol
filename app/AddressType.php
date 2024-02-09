<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddressType extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    
    public function client_address() {
        return $this->belongsTo(ClientAddress::class);
    }
}
