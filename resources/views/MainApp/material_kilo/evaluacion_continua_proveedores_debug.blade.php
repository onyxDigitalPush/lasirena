@extends('layouts.app')

@section('app_name', config('app.name'))

@section('main_content')
<div class="container">
    <h2>Debug de Variables</h2>
    
    <div class="row">
        <div class="col-12">
            <h4>Información de Debug:</h4>
            <ul>
                <li>Mes: 
                    @if(isset($mes))
                        {{ gettype($mes) }} - {{ is_string($mes) ? $mes : 'NOT STRING' }}
                    @else
                        NO DEFINIDO
                    @endif
                </li>
                <li>Año: 
                    @if(isset($año))
                        {{ gettype($año) }} - {{ is_numeric($año) ? $año : 'NOT NUMERIC' }}
                    @else
                        NO DEFINIDO
                    @endif
                </li>
                <li>Proveedor: 
                    @if(isset($proveedor))
                        {{ gettype($proveedor) }} - {{ is_string($proveedor) ? $proveedor : 'NOT STRING' }}
                    @else
                        NO DEFINIDO
                    @endif
                </li>
                <li>ID Proveedor: 
                    @if(isset($idProveedor))
                        {{ gettype($idProveedor) }} - {{ is_string($idProveedor) ? $idProveedor : 'NOT STRING' }}
                    @else
                        NO DEFINIDO
                    @endif
                </li>
                <li>Totales por Proveedor: 
                    @if(isset($totales_por_proveedor))
                        {{ gettype($totales_por_proveedor) }} - Count: {{ $totales_por_proveedor->count() }}
                    @else
                        NO DEFINIDO
                    @endif
                </li>
                <li>Proveedores Disponibles: 
                    @if(isset($proveedores_disponibles))
                        {{ gettype($proveedores_disponibles) }} - Count: {{ $proveedores_disponibles->count() }}
                    @else
                        NO DEFINIDO
                    @endif
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
