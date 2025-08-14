@extends('layouts.app')

<!-- Modals para cada tipo (2 columnas) -->
@foreach (['Resultados agua' => 'modal_resultados_agua', 'Tendencias superficie' => 'modal_tendencias_superficie', 'Tendencias micro' => 'modal_tendencias_micro'] as $tipo => $modalId)
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog"
        aria-labelledby="{{ $modalId }}Label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('evaluacion_analisis.guardar_analitica') }}">
                    @csrf
                    <input type="hidden" name="num_tienda" class="num_tienda_input">
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
                    @else
                        {{-- Modal por defecto para otros tipos (Tendencias micro u otros) --}}
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
        <table class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Num Tienda</th>
                    <th class="text-center">Nombre Tienda</th>
                    <th class="text-center">Tipo Analítica</th>
                    <th class="text-center">Fecha Real</th>
                    <th class="text-center">Periodicidad</th>
                    <th class="text-center">Proveedor</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($analiticas as $a)
                    <tr>
                        <td class="text-center">{{ $a->num_tienda }}</td>
                        <td class="text-center">{{ $a->tienda_nombre ?? (optional($a->tienda)->nombre_tienda ?? '-') }}</td>
                        <td class="text-center">{{ $a->tipo_analitica }}</td>
                        <td class="text-center">{{ $a->fecha_real_analitica }}</td>
                        <td class="text-center">{{ $a->periodicidad }}</td>
                        <td class="text-center">{{ optional($a->proveedor)->nombre_proveedor ?? '-' }}</td>
                        <td class="text-center">
                            <a href="#" class="btn btn-sm btn-primary btn-agregar-analitica-eval"
                                data-tipo="{{ $a->tipo_analitica }}" data-tienda="{{ $a->num_tienda }}"
                                data-nombre="{{ $a->tienda_nombre ?? (optional($a->tienda)->nombre_tienda ?? '') }}"
                                data-prov="{{ $a->proveedor_id }}">
                                <i class="fa fa-plus mr-1"></i>Agregar Analítica
                            </a>
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
    <script>
    var tendenciasGuardarUrl = "{{ route('evaluacion_analisis.tendencias_superficie.guardar') }}";
    var tiendaMap = {!! \App\Models\Tienda::pluck('id','num_tienda')->toJson() !!};
        // Evitar duplicar jQuery/Bootstrap: usar los cargados en includes/head_common.blade.php
        $(document).on('click', '.btn-agregar-analitica-eval', function(e) {
            e.preventDefault();
            var tipo = $(this).data('tipo');
            var tienda = $(this).data('tienda');
            var nombre = $(this).data('nombre');
            var prov = $(this).data('prov');
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

            $modal.find('.num_tienda_input').val(tienda);
            if (prov) $modal.find('.proveedor_select').val(prov);
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
        });

        // Limpieza al cerrar modales
        $(document).on('hidden.bs.modal', '.modal', function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
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
    </script>
@endsection
