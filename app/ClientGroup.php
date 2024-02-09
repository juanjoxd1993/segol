<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientGroup extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function clients() {
        return $this->hasMany(Client::class);
    }
}
