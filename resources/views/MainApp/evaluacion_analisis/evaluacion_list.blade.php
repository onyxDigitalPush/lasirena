@extends('layouts.app')

@section('custom_head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

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
                    <input type="hidden" name="fecha_teorica_original" class="fecha_teorica_original_input">
                    <input type="hidden" name="periodicidad_original" class="periodicidad_original_input">
                    <input type="hidden" name="proveedor_id_original" class="proveedor_id_original_input">
                    <input type="hidden" name="tipo_analitica_original" class="tipo_analitica_original_input">
                    <input type="hidden" name="asesor_externo_nombre_original" class="asesor_externo_nombre_original_input">
                    <input type="hidden" name="asesor_externo_empresa_original" class="asesor_externo_empresa_original_input">
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
                                        <div class="form-group">
                                            <label>Estado de Analítica</label>
                                            <select name="estado_analitica" class="form-control estado_analitica_input">
                                                <option value="sin_iniciar">Sin Iniciar</option>
                                                <option value="pendiente">Pendiente</option>
                                                <option value="realizada">Realizada</option>
                                            </select>
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
                                            <div class="form-group col-md-6">
                                                <label>Estado de Analítica</label>
                                                <select name="estado_analitica" class="form-control estado_analitica_input">
                                                    <option value="sin_iniciar">Sin Iniciar</option>
                                                    <option value="pendiente">Pendiente</option>
                                                    <option value="realizada">Realizada</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>¿Procede?</label>
                                                <select name="procede" class="form-control procede_input">
                                                    <option value="1">Sí</option>
                                                    <option value="0">No</option>
                                                </select>
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
                                                <div class="row">
                                                    <div class="col-9">
                                                        <select name="proveedor_id" class="form-control proveedor_select">
                                                            <option value="">-- Seleccionar proveedor --</option>
                                                            @foreach ($proveedores as $prov)
                                                                <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_proveedor }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-check mt-2">
                                                            <input type="checkbox" class="form-check-input proveedor_no_procede" name="proveedor_no_procede" value="1">
                                                            <label class="form-check-label">
                                                                No procede
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
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
                                            <div class="form-group col-md-6">
                                                <label>Estado de Analítica</label>
                                                <select name="estado_analitica" class="form-control estado_analitica_input">
                                                    <option value="sin_iniciar">Sin Iniciar</option>
                                                    <option value="pendiente">Pendiente</option>
                                                    <option value="realizada">Realizada</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Procede</label>
                                                <select name="procede" class="form-control">
                                                    <option value="">-- Seleccionar --</option>
                                                    <option value="1">Sí</option>
                                                    <option value="0">No</option>
                                                </select>
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
                                            <label>Fecha teorica de la Analítica</label>
                                            <input type="date" name="fecha_real_analitica" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Periodicidad</label>
                                            <div class="row">
                                                <div class="col-9">
                                                    <select name="periodicidad" class="form-control periodicidad_select">
                                                        <option value="">-- Seleccionar periodicidad --</option>
                                                        <option value="1 mes">1 mes</option>
                                                        <option value="3 meses">3 meses</option>
                                                        <option value="6 meses">6 meses</option>
                                                        <option value="anual">Anual</option>
                                                    </select>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check mt-2">
                                                        <input type="checkbox" class="form-check-input periodicidad_no_procede" name="periodicidad_no_procede" value="1">
                                                        <label class="form-check-label">
                                                            No procede
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Proveedor</label>
                                            <div class="row">
                                                <div class="col-9">
                                                    <select name="proveedor_id" class="form-control proveedor_select">
                                                        <option value="">-- Seleccionar proveedor --</option>
                                                        @foreach ($proveedores as $prov)
                                                            <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_proveedor }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check mt-2">
                                                        <input type="checkbox" class="form-check-input proveedor_no_procede" name="proveedor_no_procede" value="1">
                                                        <label class="form-check-label">
                                                            No procede
                                                        </label>
                                                    </div>
                                                </div>
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
                    <th class="text-center">Fecha Teorica</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Periodicidad</th>
                    <th class="text-center">Proveedor</th>
                    <th class="text-center">Procede</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analiticas as $a)
                    @php
                        // El estado se basa en el campo estado_analitica de la tabla analiticas
                        $estadoAnalitica = $a->estado_analitica ?? 'sin_iniciar';
                        $fechaRealizacion = null;
                        
                        // Si está marcada como realizada, buscar la fecha de cambio de estado o la fecha de realización
                        if ($estadoAnalitica === 'realizada') {
                            $fechaRealizacion = $a->fecha_cambio_estado ? $a->fecha_cambio_estado->format('d/m/Y') : 
                                               ($a->fecha_realizacion ? $a->fecha_realizacion->format('d/m/Y') : 
                                               ($a->updated_at ? $a->updated_at->format('d/m/Y') : null));
                        }
                        
                        // Verificar si tiene resultados guardados para mostrar información adicional
                        $tieneResultados = false;
                        if ($a->tipo_analitica === 'Resultados agua') {
                            $tieneResultados = $a->created_at !== null;
                        } elseif ($a->tipo_analitica === 'Tendencias superficie') {
                            $tienda = \App\Models\Tienda::where('num_tienda', $a->num_tienda)->first();
                            if ($tienda) {
                                $resultado = \App\Models\TendenciaSuperficie::where('tienda_id', $tienda->id)
                                    ->where('analitica_id', $a->id)->first();
                                $tieneResultados = $resultado !== null;
                            }
                        } elseif ($a->tipo_analitica === 'Tendencias micro') {
                            $tienda = \App\Models\Tienda::where('num_tienda', $a->num_tienda)->first();
                            if ($tienda) {
                                $resultado = \App\Models\TendenciaMicro::where('tienda_id', $tienda->id)
                                    ->where('analitica_id', $a->id)->first();
                                $tieneResultados = $resultado !== null;
                            }
                        }
                        // Calcular procede (0 si proveedor_no_procede o periodicidad_no_procede)
                        $procedeCalculado = (($a->proveedor_no_procede ?? 0) || ($a->periodicidad_no_procede ?? 0)) ? 0 : 1;
                    @endphp
                    <tr class="{{ $estadoAnalitica === 'realizada' ? 'table-success' : ($estadoAnalitica === 'pendiente' ? 'table-warning' : '') }}">
                        <td class="text-center">
                            {{ $a->num_tienda }}
                        </td>
                        <td class="text-center">
                            {{ $a->tienda_nombre ?? (optional($a->tienda)->nombre_tienda ?? '-') }}
                        </td>
                        <td class="text-center">{{ $a->tipo_analitica }}</td>
                        <td class="text-center">{{ $a->fecha_real_analitica }}</td>

                        <!-- Estado: muestra el estado basado en el campo estado_analitica -->
                        <td class="text-center">
                            @if($estadoAnalitica === 'realizada')
                                <span class="badge badge-success">
                                    <i class="fa fa-check mr-1"></i>Realizada
                                    @if($fechaRealizacion)
                                        el {{ $fechaRealizacion }}
                                    @endif
                                </span>
                            @elseif($estadoAnalitica === 'pendiente')
                                <span class="badge badge-warning">
                                    <i class="fa fa-clock mr-1"></i>Pendiente
                                    @if($tieneResultados)
                                        (con datos guardados)
                                    @endif
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    <i class="fa fa-pause mr-1"></i>Sin Iniciar
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($procedeCalculado === 1)
                                @if($a->periodicidad_no_procede)
                                    <span class="badge badge-secondary">No procede</span>
                                @else
                                    {{ $a->periodicidad ?: '-' }}
                                @endif
                            @else
                                <span class="badge badge-secondary">No procede</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($a->proveedor_no_procede)
                                <span class="badge badge-secondary">No procede</span>
                            @else
                                {{ optional($a->proveedor)->nombre_proveedor ?? '-' }}
                            @endif
                        </td>
                        
                        <!-- Procede (calculado dinámicamente) -->
                        <td class="text-center">
                            @php
                                $procedeCalculado = (($a->proveedor_no_procede ?? 0) || ($a->periodicidad_no_procede ?? 0)) ? 0 : 1;
                            @endphp
                            @if($procedeCalculado === 1)
                                <span class="badge badge-success">Sí</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>

                        <!-- Acciones: Condicionadas según el estado -->
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                @if($tieneResultados)
                                    {{-- Si ya tiene resultados guardados, solo mostrar editar --}}
                                    <a href="#" class="btn btn-sm btn-warning btn-editar-analitica-eval"
                                        data-analitica-id="{{ $a->id }}"
                                        data-tipo="{{ $a->tipo_analitica }}" data-tienda="{{ $a->num_tienda }}"
                                        data-nombre="{{ $a->tienda_nombre ?? (optional($a->tienda)->nombre_tienda ?? '') }}"
                                        data-prov="{{ $a->proveedor_id }}"
                                        data-prov-nombre="{{ optional($a->proveedor)->nombre_proveedor ?? '' }}"
                                        data-fecha-teorica="{{ $a->fecha_real_analitica }}"
                                        data-periodicidad="{{ $a->periodicidad }}"
                                        data-asesor-externo-nombre="{{ $a->asesor_externo_nombre ?? '' }}"
                                        data-asesor-externo-empresa="{{ $a->asesor_externo_empresa ?? '' }}"
                                        data-procede="{{ $a->procede ?? '' }}"
                                        data-modo="editar">
                                        <i class="fa fa-edit mr-1"></i>Editar
                                    </a>
                                @else
                                    {{-- Si no tiene resultados, mostrar agregar --}}
                                    <a href="#" class="btn btn-sm btn-primary btn-agregar-analitica-eval"
                                        data-analitica-id="{{ $a->id }}"
                                        data-tipo="{{ $a->tipo_analitica }}" data-tienda="{{ $a->num_tienda }}"
                                        data-nombre="{{ $a->tienda_nombre ?? (optional($a->tienda)->nombre_tienda ?? '') }}"
                                        data-prov="{{ $a->proveedor_id }}"
                                        data-prov-nombre="{{ optional($a->proveedor)->nombre_proveedor ?? '' }}"
                                        data-fecha-teorica="{{ $a->fecha_real_analitica }}"
                                        data-periodicidad="{{ $a->periodicidad }}"
                                        data-asesor-externo-nombre="{{ $a->asesor_externo_nombre ?? '' }}"
                                        data-asesor-externo-empresa="{{ $a->asesor_externo_empresa ?? '' }}"
                                        data-procede="{{ $a->procede ?? '' }}"
                                        data-modo="agregar">
                                        <i class="fa fa-plus mr-1"></i>Agregar Analítica
                                    </a>
                                @endif
                                
                                {{-- El botón duplicar solo se muestra cuando procede=1 (calculado dinámicamente) --}}
                                @php
                                    $procedeCalculado = (($a->proveedor_no_procede ?? 0) || ($a->periodicidad_no_procede ?? 0)) ? 0 : 1;
                                @endphp
                                @if($procedeCalculado == 1)
                                    <a href="#" class="btn btn-sm btn-info btn-duplicar-analitica ml-1" 
                                        data-analitica-id="{{ $a->id }}">
                                        <i class="fa fa-clone mr-1"></i>Duplicar
                                    </a>
                                @endif
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
        
        /* Estilo para filas de analíticas pendientes */
        .table-warning {
            background-color: #fff3cd !important;
        }
        
        /* Hover effect para las filas */
        .table-success:hover {
            background-color: #c3e6cb !important;
        }
        
        .table-warning:hover {
            background-color: #ffeaa7 !important;
        }
        
        /* Badge de estado realizada */
        .badge-success {
            background-color: #28a745;
        }
        
        /* Badge de estado pendiente */
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        /* Badge de estado sin iniciar */
        .badge-secondary {
            background-color: #6c757d;
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
        // Asegurar que los campos de procede / no_procede no sean required
        $(function() {
            $('select[name="procede"], select[name="periodicidad"], select[name="proveedor_id"], input[name="proveedor_no_procede"], input[name="periodicidad_no_procede"]').removeAttr('required');
        });
        $(document).on('click', '.btn-agregar-analitica-eval, .btn-editar-analitica-eval', function(e) {
            e.preventDefault();
            var tipo = $(this).data('tipo');
            var tienda = $(this).data('tienda');
            var nombre = $(this).data('nombre');
            var prov = $(this).data('prov');
            var provNombre = $(this).data('prov-nombre');
            var modo = $(this).data('modo') || 'agregar';
            var esEdicion = modo === 'editar';
            
            // Obtener datos adicionales desde los data attributes para la funcionalidad de auto-duplicar
            var fechaTeorica = $(this).data('fecha-teorica') || '';
            var periodicidad = $(this).data('periodicidad') || '';
            var asesorExternoNombre = $(this).data('asesor-externo-nombre') || '';
            var asesorExternoEmpresa = $(this).data('asesor-externo-empresa') || '';
            var procede = $(this).data('procede') || '';
            
            console.log('DEBUG - Datos del botón para auto-duplicar:');
            console.log('- fechaTeorica:', fechaTeorica);
            console.log('- periodicidad:', periodicidad);
            console.log('- prov:', prov);
            console.log('- tipo:', tipo);
            console.log('- asesorExternoNombre:', asesorExternoNombre);
            console.log('- asesorExternoEmpresa:', asesorExternoEmpresa);
            console.log('- procede:', procede);
            
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
            
            // Guardar datos originales para la funcionalidad de auto-duplicar
            console.log('=== ASIGNANDO DATOS ORIGINALES ===');
            console.log('fechaTeorica:', fechaTeorica);
            console.log('periodicidad:', periodicidad);
            console.log('prov:', prov);
            console.log('tipo:', tipo);
            console.log('asesorExternoNombre:', asesorExternoNombre);
            console.log('asesorExternoEmpresa:', asesorExternoEmpresa);
            
            $modal.find('.fecha_teorica_original_input').val(fechaTeorica);
            $modal.find('.periodicidad_original_input').val(periodicidad);
            $modal.find('.proveedor_id_original_input').val(prov);
            $modal.find('.tipo_analitica_original_input').val(tipo);
            $modal.find('.asesor_externo_nombre_original_input').val(asesorExternoNombre);
            $modal.find('.asesor_externo_empresa_original_input').val(asesorExternoEmpresa);
            
            console.log('Verificando asignación:');
            console.log('- fecha_teorica_original_input:', $modal.find('.fecha_teorica_original_input').val());
            console.log('- periodicidad_original_input:', $modal.find('.periodicidad_original_input').val());
            console.log('- proveedor_id_original_input:', $modal.find('.proveedor_id_original_input').val());
            console.log('- tipo_analitica_original_input:', $modal.find('.tipo_analitica_original_input').val());
            console.log('=== FIN ASIGNACIÓN ===');
            
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

            
            // Si es modo edición, cargar datos existentes
            if (esEdicion) {
                cargarDatosExistentes($modal, tipo, tienda);
            } else {
                // Limpiar formulario para modo agregar
                // NO limpiar el _token CSRF ni el id_registro ni num_tienda ni el campo de modo ni los datos originales
                $modal.find('input, select, textarea').not('.num_tienda_input, .modo_edicion_input, .id_registro_input, .analitica_id_input, .fecha_teorica_original_input, .periodicidad_original_input, .proveedor_id_original_input, .tipo_analitica_original_input, .asesor_externo_nombre_original_input, .asesor_externo_empresa_original_input, input[name="_token"]').val('');
                // En modo "agregar" ocultar el select de "procede" y los checkboxes "No procede"
                $modal.find('select[name="procede"]').closest('.form-group').hide();
                // ocultar las columnas de form-check que contienen los checkboxes de "No procede"
                $modal.find('.form-check').each(function() {
                    if ($(this).find('input.proveedor_no_procede, input.periodicidad_no_procede').length) {
                        $(this).hide();
                    }
                });
                // Restaurar selects visibles desde los datos originales (si vienen en data attributes)
                if (prov) {
                    $modal.find('select.proveedor_select').val(prov);
                }
                if (periodicidad) {
                    $modal.find('select.periodicidad_select').val(periodicidad);
                }
            }
            
            // Setear el campo procede desde los data attributes del botón (tanto para edición como agregar)
            if (procede !== '') {
                $modal.find('select[name="procede"]').val(procede);
            }

            // Si estamos en modo agregar, asegurarnos de que los campos ocultos no impidan el envío
            if (!esEdicion) {
                // eliminar valores y atributos required (si existieran)
                $modal.find('select[name="procede"]').val('');
                $modal.find('input.proveedor_no_procede, input.periodicidad_no_procede').prop('checked', false);
                $modal.find('select.proveedor_select, select.periodicidad_select').prop('required', false);
            } else {
                // En modo edición mostrar los campos por si estaban ocultos previamente
                $modal.find('select[name="procede"]').closest('.form-group').show();
                $modal.find('.form-check').show();
            }
            
            $modal.modal({
                show: true,
                backdrop: true
            });

            // Antes de enviar el formulario en modo agregar, garantizar que venga el campo 'procede'
            $modal.find('form').off('submit.ensure_procede').on('submit.ensure_procede', function(ev) {
                // Si estamos en modo agregar, calcular procede: si algún no_procede está marcado, procede=0, else 1
                if (!esEdicion) {
                    var provNo = $modal.find('input.proveedor_no_procede').is(':checked');
                    var perNo = $modal.find('input.periodicidad_no_procede').is(':checked');
                    var procedeVal = (provNo || perNo) ? '0' : '1';
                    // colocar/actualizar un hidden input para procede
                    var $hidden = $modal.find('input[name="procede_hidden"]');
                    if ($hidden.length === 0) {
                        $(this).append('<input type="hidden" name="procede" value="' + procedeVal + '" class="procede_hidden_input"/>');
                    } else {
                        $hidden.val(procedeVal);
                    }
                }
                // dejar que el submit continúe
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
                        
                        // Manejar específicamente el campo estado_analitica
                        if (data.estado_analitica) {
                            $modal.find('.estado_analitica_input').val(data.estado_analitica);
                        }
                        
                        // Manejar específicamente el campo procede
                        if (data.procede !== undefined && data.procede !== null) {
                            $modal.find('select[name="procede"]').val(data.procede.toString());
                        }
                        
                        // Guardar ID para el update
                        if (data.id) {
                            $modal.find('.id_registro_input').val(data.id);
                        }
                        // Si la respuesta incluye analitica_id, guardarlo en el campo oculto
                        if (data.analitica_id) {
                            $modal.find('.analitica_id_input').val(data.analitica_id);
                        }
                        
                        // Cargar también los datos originales de la analítica para la funcionalidad de auto-duplicar
                        if (data.analitica) {
                            $modal.find('.fecha_teorica_original_input').val(data.analitica.fecha_real_analitica || '');
                            $modal.find('.periodicidad_original_input').val(data.analitica.periodicidad || '');
                            $modal.find('.proveedor_id_original_input').val(data.analitica.proveedor_id || '');
                            $modal.find('.tipo_analitica_original_input').val(data.analitica.tipo_analitica || '');
                            $modal.find('.asesor_externo_nombre_original_input').val(data.analitica.asesor_externo_nombre || '');
                            $modal.find('.asesor_externo_empresa_original_input').val(data.analitica.asesor_externo_empresa || '');
                            
                            // También cargar el campo procede desde la analítica asociada
                            if (data.analitica.procede !== undefined && data.analitica.procede !== null) {
                                $modal.find('select[name="procede"]').val(data.analitica.procede.toString());
                            }
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

        // Manejar cambio de estado de analítica
        $(document).on('change', '.estado_analitica_input', function() {
            var estado = $(this).val();
            var $modal = $(this).closest('.modal');
            var $form = $(this).closest('form');
            
            console.log('=== CAMBIO DE ESTADO ===');
            console.log('Estado seleccionado:', estado);
            console.log('Modal:', $modal.attr('id'));
            console.log('Formulario encontrado:', $form.length);
            
            // Si se marca como realizada, se puede considerar automáticamente establecer la fecha
            if (estado === 'realizada') {
                console.log('Analítica marcada como realizada');
                console.log('Verificando datos para auto-duplicar:');
                console.log('- fecha_teorica_original:', $form.find('.fecha_teorica_original_input').val());
                console.log('- periodicidad_original:', $form.find('.periodicidad_original_input').val());
                console.log('- proveedor_id_original:', $form.find('.proveedor_id_original_input').val());
                console.log('- tipo_analitica_original:', $form.find('.tipo_analitica_original_input').val());
                
                // Intentar agregar los campos ocultos en este momento para asegurar que se envíen
                try {
                    var datosOriginalesCambio = {
                        tienda: $form.find('.num_tienda_input').val(),
                        tipo: $form.find('.tipo_analitica_original_input').val(),
                        fechaTeorica: $form.find('.fecha_teorica_original_input').val(),
                        periodicidad: $form.find('.periodicidad_original_input').val(),
                        proveedorId: $form.find('.proveedor_id_original_input').val(),
                        asesorExternoNombre: $form.find('.asesor_externo_nombre_original_input').val(),
                        asesorExternoEmpresa: $form.find('.asesor_externo_empresa_original_input').val()
                    };

                    if (datosOriginalesCambio.tienda && datosOriginalesCambio.tipo && datosOriginalesCambio.fechaTeorica && datosOriginalesCambio.periodicidad) {
                        var siguienteFechaCambio = calcularSiguienteFecha(datosOriginalesCambio.fechaTeorica, datosOriginalesCambio.periodicidad);
                        if (siguienteFechaCambio) {
                            if ($form.find('input[name="crear_siguiente"]').length === 0) {
                                $form.append('<input type="hidden" name="crear_siguiente" value="1">');
                                $form.append('<input type="hidden" name="siguiente_fecha_teorica" value="' + siguienteFechaCambio + '">');
                                $form.append('<input type="hidden" name="siguiente_tipo" value="' + datosOriginalesCambio.tipo + '">');
                                $form.append('<input type="hidden" name="siguiente_proveedor_id" value="' + datosOriginalesCambio.proveedorId + '">');
                                $form.append('<input type="hidden" name="siguiente_periodicidad" value="' + datosOriginalesCambio.periodicidad + '">');
                                $form.append('<input type="hidden" name="siguiente_asesor_externo_nombre" value="' + datosOriginalesCambio.asesorExternoNombre + '">');
                                $form.append('<input type="hidden" name="siguiente_asesor_externo_empresa" value="' + datosOriginalesCambio.asesorExternoEmpresa + '">');
                                console.log('Campos ocultos para crear siguiente agregados en cambio de estado. siguienteFecha:', siguienteFechaCambio);
                            } else {
                                console.log('Campos para crear siguiente ya existen (en cambio de estado)');
                            }
                        } else {
                            console.log('No se pudo calcular siguiente fecha en cambio de estado (fuera de rango)');
                        }
                    } else {
                        console.log('Datos insuficientes en cambio de estado para crear siguiente analítica:', datosOriginalesCambio);
                    }
                } catch (err) {
                    console.error('Error al intentar agregar campos ocultos en cambio de estado:', err);
                }
            } else if (estado === 'pendiente') {
                console.log('Analítica marcada como pendiente');
            } else {
                console.log('Analítica marcada como sin iniciar');
            }
            console.log('=== FIN CAMBIO ESTADO ===');
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

        // --- Duplicar analítica ---
        // Modal DOM (append to body) -------------------------------------------------
    var duplicarModalHtml = '\n<div class="modal fade" id="modalDuplicarAnalitica" tabindex="-1" role="dialog" aria-labelledby="modalDuplicarAnaliticaLabel">\n  <div class="modal-dialog" role="document">\n    <div class="modal-content">\n      <form id="formDuplicarAnalitica" method="POST" action="{{ route("evaluacion_analisis.guardar_analitica") }}">\n        @csrf\n        <input type="hidden" name="modo_edicion" value="duplicar">\n        <input type="hidden" name="id_registro" id="dup_id_registro">\n        <div class="modal-header">\n          <h5 class="modal-title" id="modalDuplicarAnaliticaLabel">Duplicar Analítica</h5>\n          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n        </div>\n        <div class="modal-body">\n          <div class="form-group">\n            <label>Origen: </label> <div id="dup_origen_info"></div>\n          </div>\n          <div class="form-row">\n            <div class="form-group col-md-6">\n              <label>Periodicidad origen</label>\n              <input type="text" id="dup_periodicidad" class="form-control" readonly>\n            </div>\n            <div class="form-group col-md-6">\n              <label>Tipo analítica origen</label>\n              <input type="text" id="dup_tipo_analitica" class="form-control" readonly>\n            </div>\n          </div>\n          <div class="form-row">\n            <div class="form-group col-md-6">\n              <label>Fecha real (opcional)</label>\n              <input type="date" name="fecha_real_analitica" id="dup_fecha_real_analitica" class="form-control">\n            </div>\n          </div>\n          <div class="form-row">\n            <div class="form-group col-md-6">\n              <label>Seleccionar Tienda destino</label>\n              <select name="num_tienda" id="dup_num_tienda" class="form-control">\n                <option value="">-- Seleccionar tienda --</option>\n                @foreach($tiendas as $t)\n                  <option value="{{ $t->num_tienda }}">{{ $t->num_tienda }} - {{ $t->nombre_tienda }}</option>\n                @endforeach\n              </select>\n            </div>\n            <div class="form-group col-md-6">\n              <label>Seleccionar Proveedor destino</label>\n              <select name="proveedor_id" id="dup_proveedor_id" class="form-control">\n                <option value="">-- Seleccionar proveedor --</option>\n                @foreach($proveedores as $p)\n                  <option value="{{ $p->id_proveedor }}">{{ $p->nombre_proveedor }}</option>\n                @endforeach\n              </select>\n            </div>\n          </div>\n          <p class="text-muted small">Se duplicarán los campos del formulario de analítica origen; podrá editar antes de guardar.</p>\n        </div>\n        <div class="modal-footer">\n          <button type="submit" class="btn btn-primary">Guardar Clon</button>\n          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>\n        </div>\n      </form>\n    </div>\n  </div>\n</div>';
        $('body').append(duplicarModalHtml);

        $(document).on('click', '.btn-duplicar-analitica', function(e){
            e.preventDefault();
            var id = $(this).data('analitica-id');
            if(!id) return alert('ID de analítica no encontrado');
            // Limpiar modal
            $('#dup_id_registro').val('');
            $('#dup_origen_info').text('Cargando...');
            $('#dup_num_tienda').val('');
            $('#dup_proveedor_id').val('');
            $('#modalDuplicarAnalitica').modal('show');

            // Cargar datos del origen via obtener-datos
            $.get('{{ route("evaluacion_analisis.obtener_datos") }}', { id: id }).done(function(resp){
                if(!resp.success) {
                    $('#dup_origen_info').text('No se pudo cargar la analítica origen');
                    return;
                }
                var anal = resp.analitica || resp.data || {};
                $('#dup_id_registro').val(anal.id || id);
                var info = (anal.tipo_analitica || '') + ' — ' + (anal.fecha_real_analitica || '') + ' — ' + (anal.periodicidad || '');
                $('#dup_origen_info').text(info);
                // rellenar campos adicionales
                $('#dup_periodicidad').val(anal.periodicidad || '');
                $('#dup_tipo_analitica').val(anal.tipo_analitica || '');
                if (anal.fecha_real_analitica) {
                    // normalizar a YYYY-MM-DD si viene en otro formato
                    var d = anal.fecha_real_analitica.split(' ')[0];
                    $('#dup_fecha_real_analitica').val(d);
                } else {
                    $('#dup_fecha_real_analitica').val('');
                }

                // Si la analítica origen está realizada, bloquear duplicado
                if (anal.realizada) {
                    $('#dup_origen_info').append(' <span class="text-danger">(Realizada - no duplicable)</span>');
                    $('#formDuplicarAnalitica button[type=submit]').prop('disabled', true);
                } else {
                    $('#formDuplicarAnalitica button[type=submit]').prop('disabled', false);
                }

                // Preselect proveedor if present
                if(anal.proveedor_id) $('#dup_proveedor_id').val(anal.proveedor_id);
            }).fail(function(){
                $('#dup_origen_info').text('Error al cargar origen');
            });
        });

        // Al enviar el formulario de duplicado, hacemos un POST al mismo endpoint de guardar_analitica
        // con modo 'duplicar' y el id_registro del origen. El backend actual guarda con los campos enviados.
        $(document).on('submit', '#formDuplicarAnalitica', function(e){
            // opcional: validar campos
            var tienda = $('#dup_num_tienda').val();
            if(!tienda) { alert('Seleccione una tienda destino'); e.preventDefault(); return; }
            // dejar que el form se envíe normalmente (POST) y el controlador guardará la nueva analítica
        });

        // Función para calcular la siguiente fecha según periodicidad
        function calcularSiguienteFecha(fechaTeorica, periodicidad) {
            if (!fechaTeorica || !periodicidad) return null;
            
            // Parsear la fecha teórica
            var fecha = new Date(fechaTeorica);
            if (isNaN(fecha.getTime())) {
                // Intentar parsear si viene en formato dd/mm/yyyy
                var partes = fechaTeorica.split(/[-\/]/);
                if (partes.length === 3) {
                    // Asumir formato yyyy-mm-dd o dd/mm/yyyy
                    if (partes[0].length === 4) {
                        fecha = new Date(partes[0], partes[1] - 1, partes[2]);
                    } else {
                        fecha = new Date(partes[2], partes[1] - 1, partes[0]);
                    }
                }
                if (isNaN(fecha.getTime())) return null;
            }
            
            // Calcular cuando vence según periodicidad
            var fechaVencimiento = new Date(fecha);
            switch(periodicidad.toLowerCase().trim()) {
                case '1 mes':
                    fechaVencimiento.setMonth(fechaVencimiento.getMonth() + 1);
                    break;
                case '3 meses':
                    fechaVencimiento.setMonth(fechaVencimiento.getMonth() + 3);
                    break;
                case '6 meses':
                    fechaVencimiento.setMonth(fechaVencimiento.getMonth() + 6);
                    break;
                case 'anual':
                    fechaVencimiento.setFullYear(fechaVencimiento.getFullYear() + 1);
                    break;
                default:
                    console.log('Periodicidad no reconocida:', periodicidad);
                    return null;
            }
            
            // Obtener fecha actual
            var hoy = new Date();
            hoy.setHours(0, 0, 0, 0); // Normalizar a medianoche
            fechaVencimiento.setHours(23, 59, 59, 999); // Hasta el final del día de vencimiento
            
            // Verificar si está dentro del rango (no vencido)
            if (hoy <= fechaVencimiento) {
                // La fecha de la nueva analítica será mañana
                var manana = new Date();
                manana.setDate(manana.getDate() + 1);
                return manana.toISOString().split('T')[0]; // Formato YYYY-MM-DD
            } else {
                console.log('Fuera de rango - Hoy:', hoy.toDateString(), 'Vencimiento:', fechaVencimiento.toDateString());
                return null;
            }
        }

        // Debug del botón submit
        $(document).on('click', '#modal_resultados_agua button[type="submit"], #modal_tendencias_superficie button[type="submit"], #modal_tendencias_micro button[type="submit"]', function(e) {
            console.log('🟡 BOTÓN SUBMIT CLICKEADO 🟡');
            var $form = $(this).closest('form');
            var estado = $form.find('.estado_analitica_input').val();
            console.log('Estado actual:', estado);
            console.log('Formulario válido:', $form[0].checkValidity());
        });

        // Handler para interceptar el envío de formularios cuando se marca como "realizada"
        $(document).on('submit', '#modal_resultados_agua form, #modal_tendencias_superficie form, #modal_tendencias_micro form', function(e) {
            console.log('🔥 EVENTO SUBMIT DETECTADO 🔥');
            
            var $form = $(this);
            var estadoAnalitica = $form.find('.estado_analitica_input').val();
            
            console.log('=== DEBUG SUBMIT FORM ===');
            console.log('Formulario enviado con estado:', estadoAnalitica);
            console.log('Selector del estado:', '.estado_analitica_input');
            console.log('Elemento del estado encontrado:', $form.find('.estado_analitica_input').length);
            console.log('Valor del estado:', $form.find('.estado_analitica_input').val());
            
            // Si se marca como realizada, capturar datos para crear la siguiente
            if (estadoAnalitica === 'realizada') {
                console.log('DEBUG: Formulario detectado como realizada, capturando datos...');
                
                // Verificar que los campos ocultos existen
                console.log('Campos ocultos disponibles:');
                console.log('- fecha_teorica_original_input:', $form.find('.fecha_teorica_original_input').length, 'valor:', $form.find('.fecha_teorica_original_input').val());
                console.log('- periodicidad_original_input:', $form.find('.periodicidad_original_input').length, 'valor:', $form.find('.periodicidad_original_input').val());
                console.log('- proveedor_id_original_input:', $form.find('.proveedor_id_original_input').length, 'valor:', $form.find('.proveedor_id_original_input').val());
                console.log('- tipo_analitica_original_input:', $form.find('.tipo_analitica_original_input').length, 'valor:', $form.find('.tipo_analitica_original_input').val());
                
                var datosOriginales = {
                    tienda: $form.find('.num_tienda_input').val(),
                    tipo: $form.find('.tipo_analitica_original_input').val(),
                    fechaTeorica: $form.find('.fecha_teorica_original_input').val(),
                    periodicidad: $form.find('.periodicidad_original_input').val(),
                    proveedorId: $form.find('.proveedor_id_original_input').val(),
                    asesorExternoNombre: $form.find('.asesor_externo_nombre_original_input').val(),
                    asesorExternoEmpresa: $form.find('.asesor_externo_empresa_original_input').val()
                };
                
                console.log('Datos originales capturados:', datosOriginales);
                
                // Verificar que tenemos los datos necesarios
                if (datosOriginales.tienda && datosOriginales.tipo && datosOriginales.fechaTeorica && datosOriginales.periodicidad) {
                    console.log('Datos suficientes para crear siguiente analítica - agregando datos al formulario');
                    
                    // Agregar datos de la siguiente analítica al formulario como campos ocultos
                    var siguienteFecha = calcularSiguienteFecha(datosOriginales.fechaTeorica, datosOriginales.periodicidad);
                    if (siguienteFecha) {
                        // Verificar que no existen ya estos campos para evitar duplicados
                        if ($form.find('input[name="crear_siguiente"]').length === 0) {
                            // Agregar campos ocultos para crear la siguiente analítica
                            $form.append('<input type="hidden" name="crear_siguiente" value="1">');
                            $form.append('<input type="hidden" name="siguiente_fecha_teorica" value="' + siguienteFecha + '">');
                            $form.append('<input type="hidden" name="siguiente_tipo" value="' + datosOriginales.tipo + '">');
                            $form.append('<input type="hidden" name="siguiente_proveedor_id" value="' + datosOriginales.proveedorId + '">');
                            $form.append('<input type="hidden" name="siguiente_periodicidad" value="' + datosOriginales.periodicidad + '">');
                            $form.append('<input type="hidden" name="siguiente_asesor_externo_nombre" value="' + datosOriginales.asesorExternoNombre + '">');
                            $form.append('<input type="hidden" name="siguiente_asesor_externo_empresa" value="' + datosOriginales.asesorExternoEmpresa + '">');
                            
                            console.log('✅ Siguiente fecha calculada:', siguienteFecha);
                            console.log('✅ Campos ocultos agregados para crear siguiente analítica');
                            console.log('✅ Dejando que el formulario se envíe normalmente...');
                        } else {
                            console.log('Los campos para crear siguiente ya existen, no se duplican');
                        }
                    } else {
                        console.log('No se pudo calcular la siguiente fecha (fuera de rango de periodicidad)');
                    }
                } else {
                    console.log('Datos insuficientes para crear siguiente analítica:', datosOriginales);
                }
            } else {
                console.log('Estado no es "realizada", no se creará siguiente analítica');
            }
            
            console.log('=== FIN DEBUG SUBMIT ===');
            console.log('🚀 Enviando formulario al servidor...');
        });

        // Manejar checkboxes "No procede" para proveedor
        $(document).on('change', '.proveedor_no_procede', function() {
            var $checkbox = $(this);
            var $select = $checkbox.closest('.form-group').find('.proveedor_select');
            
            if ($checkbox.is(':checked')) {
                $select.prop('disabled', true).val('');
            } else {
                $select.prop('disabled', false);
            }
        });

        // Manejar checkboxes "No procede" para periodicidad
        $(document).on('change', '.periodicidad_no_procede', function() {
            var $checkbox = $(this);
            var $select = $checkbox.closest('.form-group').find('.periodicidad_select');
            
            if ($checkbox.is(':checked')) {
                $select.prop('disabled', true).val('').prop('required', false);
            } else {
                $select.prop('disabled', false).prop('required', true);
            }
        });
    </script>
@endsection
