<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Cicle;
use App\Employee;
use App\Http\Controllers\Controller;

class PlanillaBoletaReportController extends Controller
{
    public function index()
    {
        $ciclos = Cicle::select('id', 'año', 'mes')->get();
        $empleados = Employee::select('id', 'first_name')->get();
        return view('backend.planilla_boleta_report')->with(compact('ciclos', 'empleados'));
    }

    public function list(Request $request)
    {
        // Recibe el nombre del empleado y el nombre del ciclo desde el request
        $nombreEmpleado = $request->input('empleado_nombre');
        $nombreCiclo = $request->input('ciclo_nombre');
   
        // Busca al empleado por su nombre
        $empleado = Employee::where('first_name', $nombreEmpleado)->first();

        if (!$empleado) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        // Obtiene el último ciclo para usar su fecha final como referencia
        $ultimoCiclo = Cicle::orderBy('fecha_final', 'desc')->first();

        // Prepara la respuesta básica del empleado
        $respuesta = [
            'empleado_id' => $empleado->id,
            'empleado_nombre' => $empleado->first_name,
        ];

        if (!empty($nombreCiclo)) {
            // Si se ha proporcionado un nombre de ciclo, busca solo ese ciclo
            list($año, $mes) = explode(' - ', $nombreCiclo);
            $ciclo = Cicle::where('año', $año)->where('mes', $mes)->first();

            if ($ciclo) {
                $respuesta['ciclos'] = [[
                    'ciclo_id' => $ciclo->id,
                    'ciclo_nombre' => $ciclo->año . ' - ' . $ciclo->mes,
                ]];
            } else {
                $respuesta['ciclos'] = [];
            }
        } else {
            // Si el ciclo viene vacío, obtiene todos los ciclos que sean mayores o iguales a la fecha de inicio del empleado y menores o iguales a la fecha final del último ciclo
            $ciclos = Cicle::where('fecha_inicio', '>=', $empleado->fecha_inicio)
                ->where('fecha_final', '<=', $ultimoCiclo->fecha_final)
                ->get()
                ->map(function ($ciclo) {
                    return [
                        'ciclo_id' => $ciclo->id,
                        'ciclo_nombre' => $ciclo->año . ' - ' . $ciclo->mes,
                    ];
                });

            $respuesta['ciclos'] = $ciclos;
        }

        return response()->json($respuesta);
    }



}
