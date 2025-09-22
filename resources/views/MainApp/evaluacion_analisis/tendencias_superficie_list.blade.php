@extends('layouts.app')

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-area-chart icon-gradient bg-secondary"></i>
            </div>
            <div>Analitica de superficie
                <div class="page-title-subheading">Listado y creación de analitica de superficie</div>
            </div>
        </div>
    </div>
@endsection

@section('main_content')
    <div class="col-12 bg-white p-3">
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h5>Analitica de superficie</h5>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modal_tendencias_superficie">Agregar Tendencia</button>
        </div>

        <table class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Tienda</th>
                    <th>Fecha Muestra</th>
                    <th>Proveedor</th>
                    <th>Aerobios 30C</th>
                    <th>Enterobacterias</th>
                    <th>Listeria</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tendencias as $t)
                    <tr>
                        <td>{{ $t->id }}</td>
                        <td>{{ optional($t->tienda)->nombre_tienda ?? '-' }}</td>
                        <td>{{ $t->fecha_muestra }}</td>
                        <td>{{ optional($t->proveedor)->nombre_proveedor ?? '-' }}</td>
                        <td>{{ $t->aerobios_mesofilos_30c_valor }} ({{ $t->aerobios_mesofilos_30c_result }})</td>
                        <td>{{ $t->enterobacterias_valor }} ({{ $t->enterobacterias_result }})</td>
                        <td>{{ $t->listeria_monocytogenes_valor }} ({{ $t->listeria_monocytogenes_result }})</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center">{{ $tendencias->links() }}</div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal_tendencias_superficie" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('evaluacion_analisis.tendencias_superficie.guardar') }}">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Agregar Tendencia Superficie</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tienda</label>
                                        <select name="tienda_id" class="form-control">
                                            <option value="">-- Seleccionar Tienda --</option>
                                            @foreach(\App\Models\Tienda::all() as $ti)
                                                <option value="{{ $ti->id }}">{{ $ti->nombre_tienda }} ({{ $ti->num_tienda }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Fecha muestras</label>
                                        <input type="date" name="fecha_muestra" class="form-control fecha_muestra_input">
                                        <small class="form-text text-muted">Seleccione la fecha de la muestra.</small>
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
                                    <div class="form-group">
                                        <label>Semana</label>
                                        <input type="text" name="semana" class="form-control semana_input" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Codigo Centro</label>
                                        <input type="text" name="codigo_centro" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Descripción Centro</label>
                                        <input type="text" name="descripcion_centro" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Provincia</label>
                                        <input type="text" name="provincia" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Número de muestras</label>
                                        <input type="number" name="numero_muestras" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Número factura</label>
                                        <input type="text" name="numero_factura" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Código referencia</label>
                                        <input type="text" name="codigo_referencia" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Referencias</label>
                                        <textarea name="referencias" class="form-control"></textarea>
                                    </div>

                                    <h6 class="mt-3">Resultados microbiológicos</h6>
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

                                    <div class="form-group">
                                        <label>Acción Correctiva</label>
                                        <textarea name="accion_correctiva" class="form-control"></textarea>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Repetición N1</label>
                                            <input type="text" name="repeticion_n1" class="form-control">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Repetición N2</label>
                                            <input type="text" name="repeticion_n2" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Proveedor</label>
                                        <select name="proveedor_id" class="form-control">
                                            <option value="">-- Seleccionar proveedor --</option>
                                            @foreach($proveedores as $prov)
                                                <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_proveedor }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('custom_footer')
    <script>
        // Al cambiar el campo fecha_muestra (date o texto), hacer split por espacio, 'T' o '|' y parsear la primera parte
        $(document).on('change', '.fecha_muestra_input', function() {
            var raw = $(this).val() || '';
            var first = raw.split(/\s+|T|\|/)[0];
            if (!first) return;
            var parts = first.split('-');
            if (parts.length >= 3) {
                var y = parseInt(parts[0], 10);
                var m = parseInt(parts[1], 10);
                var d = parseInt(parts[2], 10);
                if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                    $('.anio_input').val(y);
                    $('.mes_input').val(('0' + m).slice(-2));
                    var dateObj = new Date(y, m - 1, d);
                    var onejan = new Date(y,0,1);
                    var week = Math.ceil((((dateObj - onejan) / 86400000) + onejan.getDay()+1)/7);
                    $('.semana_input').val(week);
                }
            }
        });

        // Limpieza al abrir/cerrar modal
        $('#modal_tendencias_superficie').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });
    </script>
@endsection
