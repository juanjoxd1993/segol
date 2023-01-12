<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNoteReason extends Model
{
    use SoftDeletes;

	protected $dates = ['deleted_at'];

    public function voucher() {
        return $this->belongsTo(Voucher::class);
    }
}
