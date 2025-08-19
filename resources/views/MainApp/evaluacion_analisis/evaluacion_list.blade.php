@extends('layouts.app')

<!-- Modals para cada tipo (2 columnas) -->
@foreach (['Resultados agua' => 'modal_resultados_agua', 'Tendencias superficie' => 'modal_tendencias_superficie', 'Tendencias micro' => 'modal_tendencias_micro'] as $tipo => $modalId)
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog"
        aria-labelledby="{{ $modalId }}Label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="@if($tipo === 'Tendencias superficie') {{ route('evaluacion_analisis.tendencias_superficie.guardar') }} @elseif($tipo === 'Tendencias micro') {{ route('evaluacion_analisis.tendencias_micro.guardar') }} @else {{ route('evaluacion_analisis.guardar_analitica') }} @endif">
                    @csrf
                    <input type="hidden" name="num_tienda" class="num_tienda_input">
                    <input type="hidden" name="modo_edicion" class="modo_edicion_input" value="agregar">
                    <input type="hidden" name="id_registro" class="id_registro_input">
                    <input type="hidden" name="analitica_id" class="analitica_id_input">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="{{ $modalId }}Label">Agregar Analítica - {{ $tipo }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>

                    @if ($tipo === 'Resultados agua')
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row mb-2">
                                    <div class="col-12"><strong>Tienda:</strong> <span
                                            class="nombreTiendaDisplay"></span></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de muestra</label>
                                            <input type="date" name="fecha_muestra"
                                                class="form-control fecha_muestra_input" required>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Año</label>
                                                <input type="text" name="anio" class="form-control anio_input"
                                                    readonly>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Mes</label>
                                                <input type="text" name="mes" class="form-control mes_input"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Número de muestras</label>
                                            <input type="number" name="numero_muestras" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Precio reducido agua</label>
                                            <input type="text" name="precio_reducido_agua" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Código</label>
                                            <input type="text" name="codigo" class="form-control">
                                        </div>

                                        <h6 class="mt-3">Resultados (Correcto / Falso)</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Protinco</label>
                                            <input type="text" name="protinco" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Asesor</label>
                                            <input type="text" name="asesor" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Tipo referencia</label>
                                            <input type="text" name="tipo_referencia" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>E.O</label>
                                            <input type="text" name="eo" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Descripción centro</label>
                                            <input type="text" name="descripcion_centro" class="form-control">
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                                <!-- fila para resultados -->
                                <div class="row mt-3">
                                    @php $resultFields = ['calificacion','estaraca','avia','mix','plan','cal','condicion','clara']; @endphp
                                    @foreach ($resultFields as $rf)
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="d-block font-weight-bold">{{ ucfirst($rf) }}</label>
                                                <div class="row align-items-end">
                                                    <div class="col-md-6">
                                                        <label class="small mb-1">Valor</label>
                                                        <input type="text" name="{{ $rf }}_valor"
                                                            class="form-control form-control-sm"
                                                            placeholder="Valor {{ ucfirst($rf) }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="small mb-1">Resultado</label>
                                                        <select name="{{ $rf }}_resultado"
                                                            class="form-control form-control-sm">
                                                            <option value="">-- Seleccionar --</option>
                                                            <option value="correcto">Correcto</option>
                                                            <option value="falso">Falso</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar Analítica</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    @elseif ($tipo === 'Tendencias superficie')
            <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                    <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Tienda (num)</label>
                        <input type="text" name="num_tienda" class="form-control num_tienda_input" readonly>
                        <input type="hidden" name="tienda_id" class="tienda_id_input">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Fecha muestras</label>
                                                <input type="date" name="fecha_muestra" class="form-control fecha_muestra_input">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Año</label>
                                                <input type="text" name="anio" class="form-control anio_input" readonly>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Mes</label>
                                                <input type="text" name="mes" class="form-control mes_input" readonly>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Semana</label>
                                                <input type="text" name="semana" class="form-control semana_input" readonly>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Código Centro</label>
                                                <input type="text" name="codigo_centro" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Descripción Centro</label>
                                                <input type="text" name="descripcion_centro" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Provincia</label>
                                                <input type="text" name="provincia" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Número de muestras</label>
                                                <input type="number" name="numero_muestras" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Número factura</label>
                                                <input type="text" name="numero_factura" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Código referencia</label>
                                                <input type="text" name="codigo_referencia" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Referencias</label>
                                                <input type="text" name="referencias" class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <h6 class="mb-2">Resultados microbiológicos</h6>
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-6">
                                                <label>Aerobios mesófilos a 30 C (valor)</label>
                                                <input type="text" name="aerobios_mesofilos_30c_valor" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Resultado</label>
                                                <select name="aerobios_mesofilos_30c_result" class="form-control">
                                                    <option value="">-- Seleccionar --</option>
                                                    <option value="correcto">Correcto</option>
                                                    <option value="incorrecto">Incorrecto</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-6">
                                                <label>Enterobacterias (valor)</label>
                                                <input type="text" name="enterobacterias_valor" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Resultado</label>
                                                <select name="enterobacterias_result" class="form-control">
                                                    <option value="">-- Seleccionar --</option>
                                                    <option value="correcto">Correcto</option>
                                                    <option value="incorrecto">Incorrecto</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-6">
                                                <label>Listeria monocytogenes (valor)</label>
                                                <input type="text" name="listeria_monocytogenes_valor" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Resultado</label>
                                                <select name="listeria_monocytogenes_result" class="form-control">
                                                    <option value="">-- Seleccionar --</option>
                                                    <option value="correcto">Correcto</option>
                                                    <option value="incorrecto">Incorrecto</option>
                                                </select>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Acción Correctiva</label>
                                                <input type="text" name="accion_correctiva" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Repetición N1</label>
                                                <input type="text" name="repeticion_n1" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Repetición N2</label>
                                                <input type="text" name="repeticion_n2" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Proveedor</label>
                                                <select name="proveedor_id" class="form-control proveedor_select">
                                                    <option value="">-- Seleccionar proveedor --</option>
                                                    @foreach ($proveedores as $prov)
                                                        <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_proveedor }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar Analítica</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    @elseif ($tipo === 'Tendencias micro')
                        <div class="modal-body">
                            <!-- Mostrar errores de validación -->
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Tienda (num)</label>
                                                <input type="text" name="num_tienda" class="form-control num_tienda_input" readonly>
                                                <input type="hidden" name="tienda_id" class="tienda_id_input">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Fecha toma muestras</label>
                                                <input type="date" name="fecha_toma_muestras" class="form-control fecha_muestra_input" required>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Año</label>
                                                <input type="text" name="anio" class="form-control anio_input" readonly>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Mes</label>
                                                <input type="text" name="mes" class="form-control mes_input" readonly>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Semana</label>
                                                <input type="text" name="semana" class="form-control semana_input" readonly>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Código</label>
                                                <input type="text" name="codigo" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Nombre</label>
                                                <input type="text" name="nombre" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Provincia</label>
                                                <input type="text" name="provincia" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Número de muestra</label>
                                                <input type="number" name="numero_muestra" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Número factura</label>
                                                <input type="text" name="numero_factura" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Código producto</label>
                                                <input type="text" name="codigo_producto" class="form-control codigo_producto_input">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Nombre producto</label>
                                                <input type="text" name="nombre_producto" class="form-control nombre_producto_input" readonly>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Código proveedor</label>
                                                <input type="text" name="codigo_proveedor" class="form-control codigo_proveedor_input">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Nombre proveedor</label>
                                                <input type="text" name="nombre_proveedor" class="form-control nombre_proveedor_input" readonly>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>TE proveedor</label>
                                                <input type="text" name="te_proveedor" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Lote</label>
                                                <input type="text" name="lote" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Tipo</label>
                                                <input type="text" name="tipo" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Referencia</label>
                                                <input type="text" name="referencia" class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <h6 class="mb-2">Resultados microbiológicos</h6>
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-6">
                                                <label>Aerobiotico (valor)</label>
                                                <input type="text" name="aerobiotico_valor" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Resultado</label>
                                                <select name="aerobiotico_resultado" class="form-control">
                                                    <option value="">-- Seleccionar --</option>
                                                    <option value="correcto">Correcto</option>
                                                    <option value="incorrecto">Incorrecto</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-6">
                                                <label>Entero (valor)</label>
                                                <input type="text" name="entero_valor" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Resultado</label>
                                                <select name="entero_resultado" class="form-control">
                                                    <option value="">-- Seleccionar --</option>
                                                    <option value="correcto">Correcto</option>
                                                    <option value="incorrecto">Incorrecto</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-6">
                                                <label>E.coli (valor)</label>
                                                <input type="text" name="ecoli_valor" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Resultado</label>
                                                <select name="ecoli_resultado" class="form-control">
                                                    <option value="">-- Seleccionar --</option>
                                                    <option value="correcto">Correcto</option>
                                                    <option value="incorrecto">Incorrecto</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-6">
                                                <label>S (valor)</label>
                                                <input type="text" name="s_valor" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Resultado</label>
                                                <select name="s_resultado" class="form-control">
                                                    <option value="">-- Seleccionar --</option>
                                                    <option value="correcto">Correcto</option>
                                                    <option value="incorrecto">Incorrecto</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-6">
                                                <label>Salmonella (valor)</label>
                                                <input type="text" name="salmonella_valor" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Resultado</label>
                                                <select name="salmonella_resultado" class="form-control">
                                                    <option value="">-- Seleccionar --</option>
                                                    <option value="correcto">Correcto</option>
                                                    <option value="incorrecto">Incorrecto</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar Tendencia Micro</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    @else
                        {{-- Modal por defecto para otros tipos --}}
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Asesor Externo - Nombre</label>
                                            <input type="text" name="asesor_externo_nombre" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Asesor Externo - Empresa</label>
                                            <input type="text" name="asesor_externo_empresa" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Fecha Real de la Analítica</label>
                                            <input type="date" name="fecha_real_analitica" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Periodicidad</label>
                                            <select name="periodicidad" class="form-control">
                                                <option value="1 mes">1 mes</option>
                                                <option value="3 meses">3 meses</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Proveedor</label>
                                            <select name="proveedor_id" class="form-control proveedor_select">
                                                <option value="">-- Seleccionar proveedor --</option>
                                                @foreach ($proveedores as $prov)
                                                    <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_proveedor }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar Analítica</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endforeach
@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-list icon-gradient bg-secondary"></i>
            </div>
            <div>Listado de Evaluaciones
                <div class="page-title-subheading">Tabla de analíticas y acción de agregar</div>
            </div>
        </div>
    </div>
@endsection

@section('main_content')
    <div class="col-12 bg-white p-3">
        <div class="mb-4"></div>
        {{-- Flash success message area --}}
        @if(session('success'))
            <div id="flash-success" class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        {{-- Formulario de filtros --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fa fa-filter mr-2"></i>Filtros de búsqueda
                    <button class="btn btn-sm btn-outline-secondary float-right" type="button" data-toggle="collapse" data-target="#filtrosCollapse" aria-expanded="false">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                </h6>
            </div>
            <div class="collapse @if(request()->hasAny(['num_tienda', 'nombre_tienda', 'tipo_analitica'])) show @endif" id="filtrosCollapse">
                <div class="card-body">
                    <form method="GET" action="{{ route('evaluacion_analisis.list') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="num_tienda">Número de Tienda</label>
                                    <input type="text" name="num_tienda" id="num_tienda" class="form-control" 
                                           value="{{ request('num_tienda') }}" placeholder="Buscar por número...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre_tienda">Nombre de Tienda</label>
                                    <input type="text" name="nombre_tienda" id="nombre_tienda" class="form-control" 
                                           value="{{ request('nombre_tienda') }}" placeholder="Buscar por nombre...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_analitica">Tipo de Analítica</label>
                                    <select name="tipo_analitica" id="tipo_analitica" class="form-control">
                                        <option value="">-- Todos --</option>
                                        <option value="Resultados agua" {{ request('tipo_analitica') == 'Resultados agua' ? 'selected' : '' }}>Resultados agua</option>
                                        <option value="Tendencias superficie" {{ request('tipo_analitica') == 'Tendencias superficie' ? 'selected' : '' }}>Tendencias superficie</option>
                                        <option value="Tendencias micro" {{ request('tipo_analitica') == 'Tendencias micro' ? 'selected' : '' }}>Tendencias micro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search mr-1"></i>Buscar
                                </button>
                                <a href="{{ route('evaluacion_analisis.list') }}" class="btn btn-secondary ml-2">
                                    <i class="fa fa-times mr-1"></i>Limpiar filtros
                                </a>
                                @if(request()->hasAny(['num_tienda', 'nombre_tienda', 'tipo_analitica']))
                                    <span class="badge badge-info ml-2">
                                        <i class="fa fa-filter mr-1"></i>Filtros activos
                                    </span>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <table class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Num Tienda</th>
                    <th class="text-center">Nombre Tienda</th>
                    <th class="text-center">Tipo Analítica</th>
                    <th class="text-center">Fecha Real</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Periodicidad</th>
                    <th class="text-center">Proveedor</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analiticas as $a)
                    @php
                        $tieneResultados = false;
                        $fechaRealizacion = null;
                        
                        // Verificar si existen resultados según el tipo y analítica específica
                        if ($a->tipo_analitica === 'Resultados agua') {
                            // Para resultados agua, usar la relación directa si existe
                            $tieneResultados = $a->created_at !== null;
                            $fechaRealizacion = $a->created_at ? $a->created_at->format('d/m/Y') : null;
                        } elseif ($a->tipo_analitica === 'Tendencias superficie') {
                            // Buscar tendencia superficie específica de esta analítica
                            $tienda = \App\Models\Tienda::where('num_tienda', $a->num_tienda)->first();
                            if ($tienda) {
                                // Buscar por analitica_id si existe, sino por tienda y fecha aproximada
                                $resultado = \App\Models\TendenciaSuperficie::where('tienda_id', $tienda->id)
                                    ->when($a->id, function($query) use ($a) {
                                        return $query->where('analitica_id', $a->id);
                                    })
                                    ->when(!$a->id, function($query) use ($a) {
                                        // Fallback: buscar por fecha cercana
                                        $fechaAnalisis = \Carbon\Carbon::parse($a->fecha_real_analitica);
                                        return $query->whereDate('created_at', '>=', $fechaAnalisis->subDays(1))
                                                   ->whereDate('created_at', '<=', $fechaAnalisis->addDays(1));
                                    })
                                    ->first();
                                $tieneResultados = $resultado && $resultado->created_at;
                                $fechaRealizacion = $resultado ? $resultado->created_at->format('d/m/Y') : null;
                            }
                        } elseif ($a->tipo_analitica === 'Tendencias micro') {
                            // Buscar tendencia micro específica de esta analítica
                            $tienda = \App\Models\Tienda::where('num_tienda', $a->num_tienda)->first();
                            if ($tienda) {
                                // Buscar por analitica_id si existe, sino por tienda y fecha aproximada
                                $resultado = \App\Models\TendenciaMicro::where('tienda_id', $tienda->id)
                                    ->when($a->id, function($query) use ($a) {
                                        return $query->where('analitica_id', $a->id);
                                    })
                                    ->when(!$a->id, function($query) use ($a) {
                                        // Fallback: buscar por fecha cercana
                                        $fechaAnalisis = \Carbon\Carbon::parse($a->fecha_real_analitica);
                                        return $query->whereDate('created_at', '>=', $fechaAnalisis->subDays(1))
                                                   ->whereDate('created_at', '<=', $fechaAnalisis->addDays(1));
                                    })
                                    ->first();
                                $tieneResultados = $resultado && $resultado->created_at;
                                $fechaRealizacion = $resultado ? $resultado->created_at->format('d/m/Y') : null;
                            }
                        }
                    @endphp
                    <tr class="{{ $tieneResultados ? 'table-success' : '' }}">
                        <td class="text-center">{{ $a->num_tienda }}</td>
                        <td class="text-center">{{ $a->tienda_nombre ?? (optional($a->tienda)->nombre_tienda ?? '-') }}</td>
                        <td class="text-center">{{ $a->tipo_analitica }}</td>
                        <td class="text-center">{{ $a->fecha_real_analitica }}</td>

                        <!-- Estado: muestra realizada o pendiente -->
                        <td class="text-center">
                            @if($tieneResultados)
                                <span class="badge badge-success">
                                    <i class="fa fa-check mr-1"></i>Realizada el {{ $fechaRealizacion }}
                                </span>
                            @else
                                <span class="badge badge-warning">Pendiente</span>
                            @endif
                        </td>

                        <td class="text-center">{{ $a->periodicidad }}</td>
                        <td class="text-center">{{ optional($a->proveedor)->nombre_proveedor ?? '-' }}</td>

                        <!-- Acciones: Editar (si existe) y Agregar -->
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                @if($tieneResultados)
                                                <a href="#" class="btn btn-sm btn-warning mr-1 btn-editar-analitica-eval"
                                                    data-analitica-id="{{ $a->id }}"
                                                    data-tipo="{{ $a->tipo_analitica }}" data-tienda="{{ $a->num_tienda }}"
                                                    data-nombre="{{ $a->tienda_nombre ?? (optional($a->tienda)->nombre_tienda ?? '') }}"
                                                    data-prov="{{ $a->proveedor_id }}"
                                                    data-prov-nombre="{{ optional($a->proveedor)->nombre_proveedor ?? '' }}"
                                                    data-modo="editar">
                                        <i class="fa fa-edit mr-1"></i>Editar
                                    </a>
                                @endif

                                <a href="#" class="btn btn-sm btn-primary btn-agregar-analitica-eval"
                                    data-analitica-id="{{ $a->id }}"
                                    data-tipo="{{ $a->tipo_analitica }}" data-tienda="{{ $a->num_tienda }}"
                                    data-nombre="{{ $a->tienda_nombre ?? (optional($a->tienda)->nombre_tienda ?? '') }}"
                                    data-prov="{{ $a->proveedor_id }}"
                                    data-prov-nombre="{{ optional($a->proveedor)->nombre_proveedor ?? '' }}"
                                    data-modo="agregar">
                                    <i class="fa fa-plus mr-1"></i>{{ $tieneResultados ? 'Agregar Nueva' : 'Agregar Analítica' }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {{ $analiticas->links() }}
        </div>
    </div>
@endsection
@section('custom_footer')
    <style>
        /* Estilo para filas de analíticas realizadas */
        .table-success {
            background-color: #d4edda !important;
        }
        
        /* Hover effect para las filas */
        .table-success:hover {
            background-color: #c3e6cb !important;
        }
        
        /* Badge de estado realizada */
        .badge-success {
            background-color: #28a745;
        }
        
        /* Separación entre botones */
        .btn-group .btn {
            margin-right: 5px;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>
    <script>
    var tendenciasGuardarUrl = "{{ route('evaluacion_analisis.tendencias_superficie.guardar') }}";
    var tiendaMap = {!! \App\Models\Tienda::pluck('id','num_tienda')->toJson() !!};
        // Evitar duplicar jQuery/Bootstrap: usar los cargados en includes/head_common.blade.php
        $(document).on('click', '.btn-agregar-analitica-eval, .btn-editar-analitica-eval', function(e) {
            e.preventDefault();
            var tipo = $(this).data('tipo');
            var tienda = $(this).data('tienda');
            var nombre = $(this).data('nombre');
            var prov = $(this).data('prov');
            var provNombre = $(this).data('prov-nombre');
            var modo = $(this).data('modo') || 'agregar';
            var esEdicion = modo === 'editar';
            
            var modalMap = {
                'Resultados agua': '#modal_resultados_agua',
                'Tendencias superficie': '#modal_tendencias_superficie',
                'Tendencias micro': '#modal_tendencias_micro'
            };
            var target = modalMap[tipo] || '#modal_resultados_agua';
            var $modal = $(target);

            // Limpiar posibles backdrops o modales abiertos que queden en DOM
            $('.modal-backdrop').remove();
            $('.modal').not($modal).each(function() {
                $(this).removeClass('show').attr('aria-hidden', 'true').css('display', 'none');
            });
            $('body').removeClass('modal-open');

            // Configurar modo de edición
            $modal.find('.modo_edicion_input').val(modo);
            $modal.find('.num_tienda_input').val(tienda);
            // Si el botón tiene data-analitica-id, setearlo
            var analiticaIdFromBtn = $(this).data('analitica-id') || $(this).data('analiticaId') || $(this).data('analitica');
            if (analiticaIdFromBtn) {
                $modal.find('.analitica_id_input').val(analiticaIdFromBtn);
            }
            
            // Cambiar título del modal
            var nuevoTitulo = esEdicion ? 'Editar Analítica - ' + tipo : 'Agregar Analítica - ' + tipo;
            $modal.find('.modal-title').text(nuevoTitulo);
            
            // Cambiar texto del botón submit
            var $submitBtn = $modal.find('button[type="submit"]');
            $submitBtn.text(esEdicion ? 'Actualizar Analítica' : 'Guardar Analítica');

            if (prov) $modal.find('.proveedor_select').val(prov);
            
            // Si es modo edición, cargar datos existentes
            if (esEdicion) {
                cargarDatosExistentes($modal, tipo, tienda);
            } else {
                // Limpiar formulario para modo agregar
                // NO limpiar el _token CSRF ni el id_registro ni num_tienda ni el campo de modo
                $modal.find('input, select, textarea').not('.num_tienda_input, .modo_edicion_input, .id_registro_input, .analitica_id_input, input[name="_token"]').val('');
            }
            
            $modal.modal({
                show: true,
                backdrop: true
            });

            // Si es modal resultados agua, rellenar nombre tienda y escuchar fecha
            if (target === '#modal_resultados_agua') {
                $modal.find('.nombreTiendaDisplay').text(nombre || tienda);
                // si ya hay una fecha, actualizar año/mes
                var fechaVal = $modal.find('.fecha_muestra_input').val();
                if (fechaVal) {
                    var d = new Date(fechaVal);
                    $modal.find('.anio_input').val(d.getFullYear());
                    $modal.find('.mes_input').val(('0' + (d.getMonth() + 1)).slice(-2));
                }
            }

            // Si es modal Tendencias superficie, rellenar num_tienda y proveedor
            if (target === '#modal_tendencias_superficie') {
                $modal.find('input.num_tienda_input').val(tienda);
                // si el modal contiene un select proveedor, setearlo
                if (prov) $modal.find('select.proveedor_select').val(prov);
                // ajustar la action del form al endpoint de guardar tendencias superficie
                try {
                    $modal.find('form').attr('action', tendenciasGuardarUrl);
                } catch (err) { /* no-op */ }
                // rellenar tienda_id si tenemos el mapping num_tienda -> id
                try {
                    if (tiendaMap[tienda]) {
                        $modal.find('.tienda_id_input').val(tiendaMap[tienda]);
                    } else {
                        $modal.find('.tienda_id_input').val('');
                    }
                } catch (err) { /* no-op */ }
                // Si ya hay fecha en el campo, realizar el mismo parseo "split" que en la otra vista
                var raw = $modal.find('.fecha_muestra_input').val() || '';
                var first = raw.split(/\s+|T|\|/)[0];
                if (first) {
                    var parts = first.split('-');
                    if (parts.length >= 3) {
                        var y = parseInt(parts[0], 10);
                        var m = parseInt(parts[1], 10);
                        var d = parseInt(parts[2], 10);
                        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                            $modal.find('.anio_input').val(y);
                            $modal.find('.mes_input').val(('0' + m).slice(-2));
                            var dateObj = new Date(y, m - 1, d);
                            var onejan = new Date(y,0,1);
                            var week = Math.ceil((((dateObj - onejan) / 86400000) + onejan.getDay()+1)/7);
                            $modal.find('.semana_input').val(week);
                        }
                    }
                }
            }

            // Si es modal Tendencias micro, rellenar num_tienda
            if (target === '#modal_tendencias_micro') {
                $modal.find('input.num_tienda_input').val(tienda);
                // rellenar tienda_id si tenemos el mapping num_tienda -> id
                try {
                    if (tiendaMap[tienda]) {
                        $modal.find('.tienda_id_input').val(tiendaMap[tienda]);
                    } else {
                        $modal.find('.tienda_id_input').val('');
                    }
                } catch (err) { /* no-op */ }
                
                // Precargar datos del proveedor si están disponibles
                if (prov && provNombre) {
                    $modal.find('.codigo_proveedor_input').val(prov);
                    $modal.find('.nombre_proveedor_input').val(provNombre);
                }
                
                // Si ya hay fecha en el campo, realizar el mismo parseo "split" que en la otra vista
                var raw = $modal.find('.fecha_muestra_input').val() || '';
                var first = raw.split(/\s+|T|\|/)[0];
                if (first) {
                    var parts = first.split('-');
                    if (parts.length >= 3) {
                        var y = parseInt(parts[0], 10);
                        var m = parseInt(parts[1], 10);
                        var d = parseInt(parts[2], 10);
                        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                            $modal.find('.anio_input').val(y);
                            $modal.find('.mes_input').val(('0' + m).slice(-2));
                            var dateObj = new Date(y, m - 1, d);
                            var onejan = new Date(y,0,1);
                            var week = Math.ceil((((dateObj - onejan) / 86400000) + onejan.getDay()+1)/7);
                            $modal.find('.semana_input').val(week);
                        }
                    }
                }
            }
        });

        // Función para cargar datos existentes cuando se edita
        function cargarDatosExistentes($modal, tipo, tienda) {
            var endpoint = '';
            if (tipo === 'Resultados agua') {
                endpoint = '{{ route("evaluacion_analisis.obtener_datos") }}';
            } else if (tipo === 'Tendencias superficie') {
                endpoint = '{{ route("evaluacion_analisis.obtener_datos") }}';
            } else if (tipo === 'Tendencias micro') {
                endpoint = '{{ route("evaluacion_analisis.obtener_datos") }}';
            }
            
            if (endpoint) {
                $.get(endpoint, {
                    num_tienda: tienda,
                    tipo: tipo
                }).done(function(response) {
                    if (response.success && response.data) {
                        var data = response.data;
                        
                        // Rellenar campos según el tipo
                        Object.keys(data).forEach(function(key) {
                            var $input = $modal.find('[name="' + key + '"]');
                            if ($input.length) {
                                $input.val(data[key]);
                            }
                        });
                        
                        // Guardar ID para el update
                        if (data.id) {
                            $modal.find('.id_registro_input').val(data.id);
                        }
                        // Si la respuesta incluye analitica_id, guardarlo en el campo oculto
                        if (data.analitica_id) {
                            $modal.find('.analitica_id_input').val(data.analitica_id);
                        }
                    }
                }).fail(function() {
                    alert('Error al cargar los datos existentes');
                });
            }
        }

        // Limpieza al cerrar modales
        $(document).on('hidden.bs.modal', '.modal', function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Resetear formulario al cerrar
            $(this).find('.modo_edicion_input').val('agregar');
            $(this).find('.id_registro_input').val('');
            $(this).find('.modal-title').text($(this).find('.modal-title').text().replace('Editar', 'Agregar'));
            $(this).find('button[type="submit"]').text('Guardar Analítica');
        });

        // Actualizar año/mes al cambiar fecha de muestra
        $(document).on('change', '.fecha_muestra_input', function() {
            var $row = $(this).closest('.modal');
            var raw = $(this).val() || '';
            // para compatibilidad: si viene con T o | u otros, tomar la primera parte
            var first = raw.split(/\s+|T|\|/)[0];
            if (!first) return;
            var parts = first.split('-');
            if (parts.length >= 3) {
                var y = parseInt(parts[0], 10);
                var m = parseInt(parts[1], 10);
                var d = parseInt(parts[2], 10);
                if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                    $row.find('.anio_input').val(y);
                    $row.find('.mes_input').val(('0' + m).slice(-2));
                    var dateObj = new Date(y, m - 1, d);
                    var onejan = new Date(y,0,1);
                    var week = Math.ceil((((dateObj - onejan) / 86400000) + onejan.getDay()+1)/7);
                    $row.find('.semana_input').val(week);
                }
            }
        });
        
        // Lookup de productos por código
        $(document).on('blur', '.codigo_producto_input', function() {
            var codigo = $(this).val().trim();
            if (!codigo) {
                $(this).closest('.modal').find('.nombre_producto_input').val('');
                return;
            }
            
            var $nombreInput = $(this).closest('.modal').find('.nombre_producto_input');
            
            $.get("{{ route('evaluacion_analisis.buscar_producto') }}", {
                codigo: codigo
            }).done(function(response) {
                if (response.success) {
                    $nombreInput.val(response.producto.descripcion);
                } else {
                    $nombreInput.val('Producto no encontrado');
                }
            }).fail(function() {
                $nombreInput.val('Error al buscar producto');
            });
        });
        
        // Lookup de proveedores por código
        $(document).on('blur', '.codigo_proveedor_input', function() {
            var codigo = $(this).val().trim();
            if (!codigo) {
                $(this).closest('.modal').find('.nombre_proveedor_input').val('');
                return;
            }
            
            var $nombreInput = $(this).closest('.modal').find('.nombre_proveedor_input');
            
            $.get("{{ route('evaluacion_analisis.buscar_proveedor') }}", {
                codigo: codigo
            }).done(function(response) {
                if (response.success) {
                    $nombreInput.val(response.proveedor.nombre);
                } else {
                    $nombreInput.val('Proveedor no encontrado');
                }
            }).fail(function() {
                $nombreInput.val('Error al buscar proveedor');
            });
        });

        // Auto-dismiss flash success message after 4 seconds
        $(function(){
            var $flash = $('#flash-success');
            if ($flash.length) {
                setTimeout(function(){
                    $flash.alert('close');
                }, 4000);
            }
        });
    </script>
@endsection
