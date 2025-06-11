@extends('layouts.app')

@section('app_name', config('app.name'))

@section('custom_head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    window.appBaseUrl = '{{ url("/") }}';
    // Debug temporal
    console.log('Carbon month:', {{ \Carbon\Carbon::now()->month }});
    console.log('Carbon year:', {{ \Carbon\Carbon::now()->year }});
</script>
@endsection

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-bar-chart icon-gradient bg-secondary"></i>
            </div>
            <div>Total KG por Proveedor
                <div class="page-title-subheading">
                    Suma total de kilogramos agrupados por proveedor
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <a class="m-2 btn btn-primary" href="{{ route('material_kilo.index') }}">
                <i class="fa fa-list mr-2"></i>Volver a Material Kilos
            </a>
        </div>
    </div>
@endsection

@section('main_content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        
        <!-- Filtros por Mes y Año -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-filter mr-2"></i>Filtros
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filtrosForm" class="row">                            <div class="col-md-3">
                                <label for="filtro_mes">Mes:</label>
                                <select id="filtro_mes" name="mes" class="form-control" required>
                                    <option value="1" {{ \Carbon\Carbon::now()->month == 1 ? 'selected' : '' }}>Enero</option>
                                    <option value="2" {{ \Carbon\Carbon::now()->month == 2 ? 'selected' : '' }}>Febrero</option>
                                    <option value="3" {{ \Carbon\Carbon::now()->month == 3 ? 'selected' : '' }}>Marzo</option>
                                    <option value="4" {{ \Carbon\Carbon::now()->month == 4 ? 'selected' : '' }}>Abril</option>
                                    <option value="5" {{ \Carbon\Carbon::now()->month == 5 ? 'selected' : '' }}>Mayo</option>
                                    <option value="6" {{ \Carbon\Carbon::now()->month == 6 ? 'selected' : '' }}>Junio</option>
                                    <option value="7" {{ \Carbon\Carbon::now()->month == 7 ? 'selected' : '' }}>Julio</option>
                                    <option value="8" {{ \Carbon\Carbon::now()->month == 8 ? 'selected' : '' }}>Agosto</option>
                                    <option value="9" {{ \Carbon\Carbon::now()->month == 9 ? 'selected' : '' }}>Septiembre</option>
                                    <option value="10" {{ \Carbon\Carbon::now()->month == 10 ? 'selected' : '' }}>Octubre</option>
                                    <option value="11" {{ \Carbon\Carbon::now()->month == 11 ? 'selected' : '' }}>Noviembre</option>
                                    <option value="12" {{ \Carbon\Carbon::now()->month == 12 ? 'selected' : '' }}>Diciembre</option>
                                </select>
                            </div>                            <div class="col-md-3">
                                <label for="filtro_año">Año:</label>
                                <select id="filtro_año" name="año" class="form-control" required>
                                    @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                        <option value="{{ $year }}" {{ $year == \Carbon\Carbon::now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="aplicarFiltros" class="btn btn-primary mr-2">
                                    <i class="fa fa-search mr-1"></i>Aplicar Filtros
                                </button>
                                <button type="button" id="limpiarFiltros" class="btn btn-secondary">
                                    <i class="fa fa-times mr-1"></i>Limpiar
                                </button>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="guardarMetricas" class="btn btn-success">
                                    <i class="fa fa-save mr-1"></i>Guardar Métricas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen total -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-users mr-2"></i>Total Proveedores
                        </h5>
                        <h3 class="card-text">{{ $totales_por_proveedor->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-weight mr-2"></i>Total KG General
                        </h5>
                        <h3 class="card-text">{{ number_format($totales_por_proveedor->sum('total_kg_proveedor'), 2) }} kg</h3>
                    </div>
                </div>
            </div>
        </div>        <table id="table_total_kg_proveedor"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">ID Proveedor</th>
                    <th class="text-center">Nombre Proveedor</th>
                    <th class="text-center">Total KG</th>
                    <th class="text-center">Cantidad de Registros</th>
                    <th class="text-center">Porcentaje del Total</th>
                    <th class="text-center bg-warning">RG1</th>
                    <th class="text-center bg-warning">RL1</th>
                    <th class="text-center bg-warning">DEV1</th>
                    <th class="text-center bg-warning">ROK1</th>
                    <th class="text-center bg-warning">RET1</th>
                </tr>
                <tr>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar ID" /></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Proveedor" /></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>            <tbody>
                @php
                    $total_general = $totales_por_proveedor->sum('total_kg_proveedor');
                @endphp
                @foreach ($totales_por_proveedor as $total)
                    @php
                        $porcentaje = $total_general > 0 ? ($total->total_kg_proveedor / $total_general) * 100 : 0;
                        // Obtener métricas existentes si las hay
                        $metricas = isset($metricas_por_proveedor[$total->id_proveedor]) ? $metricas_por_proveedor[$total->id_proveedor] : null;
                    @endphp
                    <tr data-proveedor-id="{{ $total->id_proveedor }}">
                        <td class="text-center">{{ $total->id_proveedor }}</td>
                        <td class="text-center">{{ $total->nombre_proveedor }}</td>
                        <td class="text-center">
                            <span class="badge badge-success badge-lg">
                                {{ number_format($total->total_kg_proveedor, 2) }} kg
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">
                                {{ number_format($total->cantidad_registros) }} registros
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar 
                                    @if($porcentaje >= 50) bg-success
                                    @elseif($porcentaje >= 25) bg-warning
                                    @else bg-info
                                    @endif" 
                                    role="progressbar" 
                                    style="width: {{ $porcentaje }}%;" 
                                    aria-valuenow="{{ $porcentaje }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    {{ number_format($porcentaje, 1) }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="rg1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->rg1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="rg1">
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="rl1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->rl1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="rl1">
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="dev1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->dev1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="dev1">
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="rok1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->rok1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="rok1">
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm metrica-input" 
                                   name="ret1" 
                                   step="0.01" 
                                   placeholder="0.00"
                                   value="{{ $metricas ? $metricas->ret1 : '' }}"
                                   data-proveedor="{{ $total->id_proveedor }}"
                                   data-metrica="ret1">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('custom_footer')
    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/total_kg_proveedor.js') }}?v={{ config('app.version') }}"></script>
@endsection
