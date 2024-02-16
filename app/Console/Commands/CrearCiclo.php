<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Cicle;
use Carbon\Carbon;

class CrearCiclo extends Command
{
    protected $signature = 'ciclo:crear';
    protected $description = 'Crea los ciclos faltantes y el ciclo del próximo mes';

    // Array de feriados por mes
    protected $feriadosPorMes = [
        1 => 1, 2 => 0, 3 => 2, 4 => 0,
        5 => 1, 6 => 2, 7 => 2, 8 => 2,
        9 => 0, 10 => 1, 11 => 1, 12 => 2,
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $ultimoCiclo = Cicle::orderBy('dia_final', 'desc')->first();
        $fechaInicio = $ultimoCiclo ? Carbon::createFromFormat('Y-m-d', $ultimoCiclo->dia_final)->addDay() : Carbon::now()->startOfMonth();

        // Crea los ciclos faltantes hasta la fecha actual y el ciclo del próximo mes
        $currentDate = Carbon::now();
        while ($fechaInicio->lessThanOrEqualTo($currentDate)) {
            $this->crearCiclo($fechaInicio);
            $fechaInicio->addMonthNoOverflow();
        }
    }

    protected function crearCiclo($fecha)
    {
        $nombreMes = $fecha->locale('es')->isoFormat('YYYY-MMM');
        $domingos = $this->calcularDomingos($fecha, $fecha->copy()->endOfMonth());
        $feriados = $this->feriadosPorMes[$fecha->month] ?? 0;
        $diasLaborables = $fecha->daysInMonth - $domingos - $feriados;

        $ciclo = new Cicle();
        $ciclo->año = $fecha->year;
        $ciclo->mes = $fecha->format('m');
        $ciclo->dias = $fecha->daysInMonth;
        $ciclo->domingos = $domingos;
        $ciclo->feriados = $feriados;
        $ciclo->laborables = $diasLaborables;
        $ciclo->name = $nombreMes;
        $ciclo->fecha_inicio = $fecha->toDateString();
        $ciclo->dia_final = $fecha->copy()->endOfMonth()->toDateString();
        $ciclo->fecha_final = $fecha->copy()->startOfMonth()->subDay()->toDateString();

        $ciclo->save();

        $this->info("Ciclo creado para {$nombreMes} con {$diasLaborables} días laborables.");
    }

    protected function calcularDomingos($fechaInicio, $fechaFinal)
    {
        $domingos = 0;
        for ($date = $fechaInicio->copy(); $date->lessThanOrEqualTo($fechaFinal); $date->addDay()) {
            if ($date->isSunday()) {
                $domingos++;
            }
        }
        return $domingos;
    }
}
