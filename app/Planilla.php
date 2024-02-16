<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Planilla extends Model
{
	use SoftDeletes;
	public $timestamps = false;
	protected $dates = ['deleted_at'];
	protected $fillable = [
        'año', 'mes', 'ciclo', 'dias', 'employ_id', 'employ_name', 'cargo', 'sueldo', 'familiar', 
        'otros', 'bruto', 'horas_extra', 'noc_25', 'noc_35', 'afp_id', 'afp_name', 'afp_base', 
        'afp_com', 'afp_prima', 'quincena', 'total_desc', 'neto', 'salud', 'sctr', 'total_apor',
    ];

	

}
