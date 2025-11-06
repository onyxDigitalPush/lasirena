@extends('layouts.app')

@section('app_name', config('app.name'))

@section('custom_head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    window.appBaseUrl = '{{ url("/") }}';
</script>
@endsection

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-line-chart icon-gradient bg-secondary"></i>
            </div>
            <div>Evaluación Continua Proveedores
                <div class="page-title-subheading">
                    Valores Ponderados - Indicadores por Millón de KG
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <a class="m-2 btn btn-primary" href="{{ route('material_kilo.index') }}">
                <i class="fa fa-list mr-2"></i>Volver a Material Kilos
            </a>
            <a class="m-2 btn btn-info" href="{{ route('material_kilo.total_kg_proveedor') }}">
                <i class="fa fa-bar-chart mr-2"></i>Total KG por Proveedor
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
                        <form id="filtrosForm" class="row">
                            <div class="col-md-2">
                                <label for="filtro_mes">Mes:</label>
                                <select id="filtro_mes" name="mes" class="form-control" required>
                                    <option value="" {{ !$mes ? 'selected' : '' }}>Todos los meses</option>
                                    <option value="1" {{ $mes == 1 ? 'selected' : '' }}>Enero</option>
                                    <option value="2" {{ $mes == 2 ? 'selected' : '' }}>Febrero</option>
                                    <option value="3" {{ $mes == 3 ? 'selected' : '' }}>Marzo</option>
                                    <option value="4" {{ $mes == 4 ? 'selected' : '' }}>Abril</option>
                                    <option value="5" {{ $mes == 5 ? 'selected' : '' }}>Mayo</option>
                                    <option value="6" {{ $mes == 6 ? 'selected' : '' }}>Junio</option>
                                    <option value="7" {{ $mes == 7 ? 'selected' : '' }}>Julio</option>
                                    <option value="8" {{ $mes == 8 ? 'selected' : '' }}>Agosto</option>
                                    <option value="9" {{ $mes == 9 ? 'selected' : '' }}>Septiembre</option>
                                    <option value="10" {{ $mes == 10 ? 'selected' : '' }}>Octubre</option>
                                    <option value="11" {{ $mes == 11 ? 'selected' : '' }}>Noviembre</option>
                                    <option value="12" {{ $mes == 12 ? 'selected' : '' }}>Diciembre</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label for="filtro_año">Año:</label>
                                <select id="filtro_año" name="año" class="form-control" required>
                                    @for($year = \Carbon\Carbon::now()->year; $year >= 2020; $year--)
                                        <option value="{{ $year }}" {{ $year == $año ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_proveedor">Proveedor:</label>
                                <select id="filtro_proveedor" class="form-control">
                                    <option value="">Todos los proveedores</option>
                                    @if(isset($proveedores_disponibles))
                                        @foreach($proveedores_disponibles as $proveedor_item)
                                            <option value="{{ $proveedor_item->nombre_proveedor }}" 
                                                {{ $proveedor == $proveedor_item->nombre_proveedor ? 'selected' : '' }}>
                                                {{ $proveedor_item->nombre_proveedor }}
                                            </option>
                                        @endforeach
                                    @else
                                        @php
                                            // Fallback: usar los proveedores de la consulta filtrada
                                            $proveedores_fallback = $totales_por_proveedor->unique('nombre_proveedor')->sortBy('nombre_proveedor');
                                        @endphp
                                        @foreach($proveedores_fallback as $proveedor_item)
                                            <option value="{{ $proveedor_item->nombre_proveedor }}" 
                                                {{ $proveedor == $proveedor_item->nombre_proveedor ? 'selected' : '' }}>
                                                {{ $proveedor_item->nombre_proveedor }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @if($proveedor)
                                    <small class="form-text text-success">
                                        <i class="fa fa-check mr-1"></i>Filtrado por: {{ is_string($proveedor) ? $proveedor : '' }}
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-1">
                                <label for="filtro_id_proveedor">ID Proveedor:</label>
                                <input type="text" id="filtro_id_proveedor" class="form-control" 
                                       placeholder="Escribir ID..." value="{{ is_string($idProveedor) ? $idProveedor : '' }}">
                                @if($idProveedor)
                                    <small class="form-text text-success">
                                        <i class="fa fa-check mr-1"></i>Filtrado por ID: {{ is_string($idProveedor) ? $idProveedor : '' }}
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-2">
                                <label for="filtro_familia">Familia:</label>
                                <select id="filtro_familia" class="form-control">
                                    <option value="">Todas las familias</option>
                                    <option value="ELABORADOS" {{ $familia == 'ELABORADOS' ? 'selected' : '' }}>ELABORADOS</option>
                                    <option value="PRODUCTOS DEL MAR" {{ $familia == 'PRODUCTOS DEL MAR' ? 'selected' : '' }}>PRODUCTOS DEL MAR</option>
                                    <option value="CONSUMIBLES" {{ $familia == 'CONSUMIBLES' ? 'selected' : '' }}>CONSUMIBLES</option>
                                    <option value="Otros" {{ $familia == 'Otros' ? 'selected' : '' }}>Otros</option>
                                </select>
                                @if($familia)
                                    <small class="form-text text-success">
                                        <i class="fa fa-check mr-1"></i>Filtrado por: {{ is_string($familia) ? $familia : '' }}
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="aplicarFiltros" class="btn btn-primary mr-2">
                                    <i class="fa fa-search mr-1"></i>Aplicar Filtros
                                </button>
                                <button type="button" id="limpiarFiltros" class="btn btn-secondary mr-2">
                                    <i class="fa fa-times mr-1"></i>Limpiar Filtros
                                </button>
                                <button type="button" id="limpiarFiltrosTabla" class="btn btn-info">
                                    <i class="fa fa-refresh mr-1"></i>Limpiar Tabla
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen total -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-users mr-2"></i>Total Proveedores
                        </h5>
                        <h3 class="card-text" id="total-proveedores">{{ $totales_por_proveedor->count() }}</h3>
                        @if($proveedor || $idProveedor)
                            <small class="text-white-50">
                                @if($proveedor)
                                    Filtrado por: {{ is_string($proveedor) ? $proveedor : '' }}
                                @endif
                                @if($idProveedor)
                                    ID: {{ is_string($idProveedor) ? $idProveedor : '' }}
                                @endif
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-weight mr-2"></i>Total KG General
                        </h5>
                        <h3 class="card-text" id="total-kg-general">{{ number_format($totales_por_proveedor->sum('total_kg_proveedor'), 2) }} kg</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-calendar mr-2"></i>Período
                        </h5>                        <h3 class="card-text">
                            @php
                                $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                            @endphp
                            @if($mes)
                                {{ $meses[$mes] }} {{ $año }}
                            @else
                                Todo el año {{ $año }}
                            @endif
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-filter mr-2"></i>Filtros Activos
                        </h5>
                        <div class="card-text">
                            @if($proveedor || $idProveedor || $familia)
                                @if($proveedor)
                                    <small class="d-block">✓ Proveedor: {{ is_string($proveedor) ? $proveedor : '' }}</small>
                                @endif
                                @if($idProveedor)
                                    <small class="d-block">✓ ID: {{ is_string($idProveedor) ? $idProveedor : '' }}</small>
                                @endif
                                @if($familia)
                                    <small class="d-block">✓ Familia: {{ is_string($familia) ? $familia : '' }}</small>
                                @endif
                            @else
                                <small>Sin filtros aplicados</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de evaluación continua -->
        <table id="table_evaluacion_continua"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>                <tr>
                    <th class="text-center" rowspan="2">ID Proveedor</th>
                    <th class="text-center" rowspan="2">Nombre Proveedor</th>
                    <th class="text-center" rowspan="2">Total KG</th>
                    <th class="text-center bg-info text-white" colspan="6">
                        Valores por Millón de KG - 
                        @if($mes)
                            {{ $meses[$mes] ?? 'Mes' }} {{ $año }}
                        @else
                            Todo el año {{ $año }}
                        @endif
                    </th>
                    <th class="text-center bg-warning" colspan="6">
                        Valores Ponderados - 
                        @if($mes)
                            {{ $meses[$mes] ?? 'Mes' }} {{ $año }}
                        @else
                            Todo el año {{ $año }}
                        @endif
                    </th>
                </tr>
                <tr>
                    <th class="text-center bg-info text-white">RG</th>
                    <th class="text-center bg-info text-white">RL</th>
                    <th class="text-center bg-info text-white">DEV</th>
                    <th class="text-center bg-info text-white">ROK</th>
                    <th class="text-center bg-info text-white">RET</th>
                    <th class="text-center bg-info text-white">TOTAL</th>
                    <th class="text-center bg-warning">RG</th>
                    <th class="text-center bg-warning">RL</th>
                    <th class="text-center bg-warning">DEV</th>
                    <th class="text-center bg-warning">ROK</th>
                    <th class="text-center bg-warning">RET</th>
                    <th class="text-center bg-warning">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($totales_por_proveedor as $proveedor)
                    <tr data-proveedor-id="{{ $proveedor->id_proveedor }}">
                        <td class="text-center">{{ $proveedor->id_proveedor }}</td>
                        <td class="text-center">{{ $proveedor->nombre_proveedor }}</td>
                        <td class="text-center" data-total="{{ $proveedor->total_kg_proveedor }}">
                            <span class="badge badge-success badge-lg">
                                {{ $proveedor->total_kg_proveedor_fmt }} kg
                            </span>
                        </td>
                        
                        <!-- Indicadores por millón de KG -->
                        <td class="text-center">
                            <span class="badge badge-info">
                                {{ number_format($proveedor->rg_ind1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">
                                {{ number_format($proveedor->rl_ind1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">
                                {{ number_format($proveedor->dev_ind1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">
                                {{ number_format($proveedor->rok_ind1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">
                                {{ number_format($proveedor->ret_ind1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-primary badge-lg">
                                {{ number_format($proveedor->total_ind1, 2) }}
                            </span>
                        </td>
                        
                        <!-- Valores ponderados -->
                        <td class="text-center">
                            <span class="badge badge-warning">
                                {{ number_format($proveedor->rg_pond1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-warning">
                                {{ number_format($proveedor->rl_pond1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-warning">
                                {{ number_format($proveedor->dev_pond1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-warning">
                                {{ number_format($proveedor->rok_pond1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-warning">
                                {{ number_format($proveedor->ret_pond1, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-dark badge-lg">
                                {{ number_format($proveedor->total_pond1, 2) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('custom_footer')
    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/evaluacion_continua_proveedores.js') }}?v={{ config('app.version') }}"></script>
@endsection
