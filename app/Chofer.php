<?php

namespace App; 

use Illuminate\Database\Eloquent\Model;

class Chofer extends Model
{
    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'choferes';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'documento',
        'brevete',
        'tipo'
    ];

    // Aquí puedes agregar relaciones, accesores, y otros métodos específicos del modelo.
}
