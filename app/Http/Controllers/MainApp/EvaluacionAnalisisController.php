<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EvaluacionAnalisisController extends Controller
{
    public function historialEvaluaciones(){
        //retornar vista de historial de evaluaciones
        return view('MainApp/evaluacion_analisis.historial_evaluaciones');
    }
}
