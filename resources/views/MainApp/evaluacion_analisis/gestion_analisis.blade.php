@extends('layouts.app')

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-chart-bar icon-gradient bg-warning"></i>
            </div>
            <div>Gestión de Análisis
                <div class="page-title-subheading">Gestión completa con estado de vencimiento y edición</div>
            </div>
        </div>
    </div>
@endsection
@php
    $tipoDisplay = [
        'Resultados agua' => 'Analitica agua',
        'Tendencias superficie' => 'Analitica de superficie',
        'Tendencias micro' => 'Analitica de microbiologia'
    ];
@endphp
{{-- Modal de Confirmación para Eliminar --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar este registro de <strong id="tipoEliminar"></strong>?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="{{ route('evaluacion_analisis.eliminar') }}" id="formEliminar">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id" id="idEliminar">
                    <input type="hidden" name="tipo" id="tipoEliminarInput">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Edición (se generará dinámicamente con JavaScript) --}}
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar <span id="tipoEditar"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="contenidoModalEditar">
                <!-- Se cargará dinámicamente -->
            </div>
        </div>
    </div>
</div>
@section('main_content')
    <div class="col-12 bg-white p-3">
        <div class="mb-4"></div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div id="flash-success" class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
        @endif

        @if (session('error'))
            <div id="flash-error" class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
        @endif

        {{-- Formulario de filtros --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fa fa-filter mr-2"></i>Filtros de búsqueda
                    <button class="btn btn-sm btn-outline-secondary float-right" type="button" data-toggle="collapse"
                        data-target="#filtrosCollapse" aria-expanded="false">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                </h6>
            </div>
            <div class="collapse @if (request()->hasAny(['num_tienda', 'nombre_tienda', 'tipo_analitica'])) show @endif" id="filtrosCollapse">
                <div class="card-body">
                    <form method="GET" action="{{ route('evaluacion_analisis.gestion') }}">
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
                                        <option value="Resultados agua" {{ request('tipo_analitica') == 'Resultados agua' ? 'selected' : '' }}>{{ $tipoDisplay['Resultados agua'] }}</option>
                                        <option value="Tendencias superficie" {{ request('tipo_analitica') == 'Tendencias superficie' ? 'selected' : '' }}>{{ $tipoDisplay['Tendencias superficie'] }}</option>
                                        <option value="Tendencias micro" {{ request('tipo_analitica') == 'Tendencias micro' ? 'selected' : '' }}>{{ $tipoDisplay['Tendencias micro'] }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha inicio</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                                        value="{{ request('fecha_inicio') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha fin</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                                        value="{{ request('fecha_fin') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search mr-1"></i>Buscar
                                </button>
                                <a href="{{ route('evaluacion_analisis.gestion') }}" class="btn btn-secondary ml-2">
                                    <i class="fa fa-times mr-1"></i>Limpiar filtros
                                </a>
                                @if (request()->hasAny(['num_tienda', 'nombre_tienda', 'tipo_analitica']))
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

        {{-- Leyenda de colores --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-12">
                        <small class="text-muted">
                            <strong>Estado de vencimiento:</strong>
                            <span class="badge badge-success ml-2">Vigente</span>
                            <span class="badge badge-warning ml-1">Próximo a vencer (≤7 días)</span>
                            <span class="badge badge-danger ml-1">Vencido</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla principal --}}
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Num Tienda</th>
                        <th class="text-center">Nombre Tienda</th>
                        <th class="text-center">Tipo Analítica</th>
                        <th class="text-center">Fecha Análisis</th>
                        <th class="text-center">Fecha Teorica</th>
                        <th class="text-center">Periodicidad</th>
                        <th class="text-center">Fecha Límite</th>
                        <th class="text-center">Días Restantes</th>
                        <th class="text-center">Archivos</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resultados as $resultado)
                            @php
                            // Determinar si existe fecha de realización (resultado ya cargado)
                            // Usamos la bandera `realizada` que calcula el controlador. No usamos
                            // `fecha_real_analitica` como prueba de que está realizada porque
                            // esa fecha es la fecha del análisis programado y puede existir
                            // aunque no se haya cargado un resultado final.
                            $fechaRealizacion = $resultado->fecha_realizacion ?? null;
                            $esRealizada = (!empty($resultado->realizada) || !empty($fechaRealizacion));
                            // Mantener lógica de vencido/advertencia cuando NO está realizada
                            $estadoClase = 'success';
                            $estadoTexto = 'Vigente';
                            if (!$esRealizada) {
                                if ($resultado->vencido) {
                                    $estadoClase = 'danger';
                                    $estadoTexto = 'Vencido';
                                } elseif ($resultado->dias_restantes !== null && $resultado->dias_restantes <= 7) {
                                    $estadoClase = 'warning';
                                    $estadoTexto = 'Próximo a vencer';
                                }
                            }

                            // Calcular procede: si proveedor o periodicidad está marcada como 'no procede'
                            $procedeCalculado = (($resultado->proveedor_no_procede ?? 0) || ($resultado->periodicidad_no_procede ?? 0)) ? 0 : 1;
                        @endphp
                            <tr class="{{ $esRealizada ? 'table-success' : ($estadoClase === 'danger' ? 'table-danger' : ($estadoClase === 'warning' ? 'table-warning' : '')) }}">
                                <td class="text-center">
                                    @if($esRealizada)
                                        <span class="badge badge-success"><i class="fa fa-check mr-1"></i>Realizada el {{ \Carbon\Carbon::parse($fechaRealizacion)->format('d/m/Y') }}</span>
                                    @else
                                        <span class="badge badge-{{ $estadoClase }}">{{ $estadoTexto }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $resultado->num_tienda }}
                                </td>
                                <td class="text-center">
                                    {{ $resultado->tienda_nombre ?? '-' }}
                                </td>
                            <td class="text-center">{{ $tipoDisplay[$resultado->tipo_analitica] ?? $resultado->tipo_analitica }}</td>
                            <td class="text-center">{{ $resultado->fecha_real_analitica }}</td>
                            <td class="text-center">
                                @if($esRealizada)
                                    {{ \Carbon\Carbon::parse($fechaRealizacion)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($procedeCalculado === 1)
                                    {{ $resultado->periodicidad }}
                                @else
                                    <span class="badge badge-secondary">No procede</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $resultado->fecha_limite }}</td>
                            <td class="text-center">
                                @if ($resultado->dias_restantes !== null)
                                    @if ($resultado->dias_restantes < 0)
                                        <span class="text-danger">{{ abs($resultado->dias_restantes) }} días
                                            vencido</span>
                                    @else
                                        {{ $resultado->dias_restantes }} días
                                    @endif
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Columna de archivos --}}
                            <td class="text-center" style="min-width: 150px;">
                                @if($resultado->analitica && $resultado->analitica->hasArchivos())
                                    @php $archivos = $resultado->analitica->getArchivosArray(); @endphp
                                    <div class="text-left">
                                        @foreach($archivos as $archivo)
                                            @if(is_array($archivo) && isset($archivo['nombre']) && isset($archivo['nombre_original']))
                                                <div class="d-flex justify-content-between align-items-center mb-1 p-1 bg-light rounded archivo-item">
                                                    <small class="text-truncate-custom" title="{{ $archivo['nombre_original'] }}">
                                                        <i class="fas fa-file text-primary"></i> {{ Str::limit($archivo['nombre_original'], 10) }}
                                                    </small>
                                                    <a href="{{ route('evaluacion_analisis.descargar_archivo', ['analiticaId' => $resultado->analitica->id, 'nombreArchivo' => $archivo['nombre']]) }}" 
                                                       class="btn btn-xs btn-outline-primary" 
                                                       title="Descargar {{ $archivo['nombre_original'] }}" 
                                                       target="_blank">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <small class="text-muted">Sin archivos</small>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary btn-editar-analisis"
                                        data-id="{{ $resultado->id }}" data-tipo="{{ $resultado->tabla_origen }}"
                                        data-tipo-texto="{{ $resultado->tipo_analitica }}">
                                        <i class="fa fa-edit mr-1"></i>Editar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-analisis"
                                        data-id="{{ $resultado->id }}" data-tipo="{{ $resultado->tabla_origen }}"
                                        data-tipo-texto="{{ $resultado->tipo_analitica }}">
                                        <i class="fa fa-trash mr-1"></i>Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $resultados->links() }}
        </div>
    </div>
@endsection

@section('custom_footer')
    <script>
        // Auto-dismiss flash messages
        $(function() {
            var $flashSuccess = $('#flash-success');
            var $flashError = $('#flash-error');
            if ($flashSuccess.length) {
                setTimeout(function() {
                    $flashSuccess.alert('close');
                }, 4000);
            }
            if ($flashError.length) {
                setTimeout(function() {
                    $flashError.alert('close');
                }, 4000);
            }
        });

        // Botón eliminar
        $(document).on('click', '.btn-eliminar-analisis', function() {
            var id = $(this).data('id');
            var tipo = $(this).data('tipo');
            var tipoTexto = $(this).data('tipo-texto');

            $('#idEliminar').val(id);
            $('#tipoEliminarInput').val(tipo);
            $('#tipoEliminar').text(tipoTexto);
            $('#modalEliminar').modal('show');
        });

        // Botón editar
        $(document).on('click', '.btn-editar-analisis', function() {
            var id = $(this).data('id');
            var tipo = $(this).data('tipo');
            var tipoTexto = $(this).data('tipo-texto');

            $('#tipoEditar').text(tipoTexto);

            // Cargar datos vía AJAX
            $.get("{{ route('evaluacion_analisis.obtener_datos') }}", {
                id: id,
                tipo: tipo
            }).done(function(response) {
                if (response && response.success) {
                    // tolerar distintas claves: datos, data, analitica
                    var payload = response.datos || response.data || response.analitica;
                    if (!payload) {
                        alert('No se encontraron datos para editar');
                        return;
                    }
                    
                    // Guardar analitica_id para manejo de archivos
                    window.analiticaIdActual = payload.analitica_id || payload.id;
                    
                    generarFormularioEdicion(payload, tipo);
                    $('#modalEditar').modal('show');
                    
                    // Cargar archivos existentes si los hay
                    if (payload.archivos) {
                        var archivos = payload.archivos;
                        // Si archivos es un string JSON, convertirlo a array
                        if (typeof archivos === 'string') {
                            try {
                                archivos = JSON.parse(archivos);
                            } catch (e) {
                                console.error('Error al parsear archivos JSON:', e);
                                archivos = [];
                            }
                        }
                        if (archivos && archivos.length > 0) {
                            mostrarArchivosExistentesGestion(archivos);
                        }
                    }
                } else {
                    alert('Error al cargar los datos');
                }
            }).fail(function() {
                alert('Error al cargar los datos');
            });
        });

        // Función para mostrar archivos existentes en gestión
        function mostrarArchivosExistentesGestion(archivos) {
            var container = $('#lista_archivos_existentes_gestion');
            container.empty();
            
            if (archivos && archivos.length > 0) {
                var html = '<div class="mt-2"><strong>Archivos existentes:</strong><ul class="list-group mt-1">';
                archivos.forEach(function(archivo, index) {
                    if (typeof archivo === 'object' && archivo.nombre_original && archivo.nombre) {
                        html += '<li class="list-group-item d-flex justify-content-between align-items-center py-2">';
                        html += '<div class="d-flex align-items-center">';
                        html += '<i class="fas fa-file text-primary mr-2"></i>';
                        html += '<div>';
                        html += '<strong>' + archivo.nombre_original + '</strong><br>';
                        html += '<small class="text-muted">' + (archivo.tamano ? formatFileSize(archivo.tamano) : '') + '</small>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div>';
                        html += '<a href="/evaluacion_analisis/descargar_archivo/' + window.analiticaIdActual + '/' + archivo.nombre + '" class="btn btn-sm btn-outline-primary mr-1" target="_blank" title="Descargar"><i class="fas fa-download"></i></a>';
                        html += '<button type="button" class="btn btn-sm btn-danger" onclick="eliminarArchivoExistenteGestion(\'' + archivo.nombre + '\', ' + index + ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
                        html += '</div>';
                        html += '</li>';
                    }
                });
                html += '</ul></div>';
                container.html(html);
            }
        }

        // Función para eliminar archivo existente en gestión
        function eliminarArchivoExistenteGestion(nombreArchivo, index) {
            if (confirm('¿Estás seguro de que quieres eliminar este archivo?')) {
                if (!window.analiticaIdActual) {
                    alert('Error: ID de analítica no disponible');
                    return;
                }

                $.ajax({
                    url: '/evaluacion_analisis/eliminar_archivo',
                    method: 'DELETE',
                    data: {
                        analiticaId: window.analiticaIdActual,
                        nombreArchivo: nombreArchivo,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Recargar archivos
                            location.reload();
                        } else {
                            alert('Error al eliminar el archivo: ' + (response.message || 'Error desconocido'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al eliminar archivo:', error);
                        alert('Error al eliminar el archivo');
                    }
                });
            }
        }

        // Función para formatear tamaño de archivo
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Función para generar sección de archivos
        function generarSeccionArchivos() {
            var html = '<hr><h6><i class="fas fa-paperclip mr-2"></i>Archivos</h6>';
            html += '<div class="form-group">';
            html += '<label>Subir archivos (PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF - Máx. 10MB)</label>';
            html += '<input type="file" name="archivos[]" class="form-control-file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">';
            html += '<small class="text-muted">Puedes seleccionar múltiples archivos</small>';
            html += '</div>';
            html += '<div id="lista_archivos_existentes_gestion"></div>';
            return html;
        }

        function generarFormularioEdicion(datos, tipo) {
            if (!datos || typeof datos !== 'object') {
                console.error('generarFormularioEdicion: datos inválidos', datos);
                alert('Error: datos de edición inválidos. Reintente.');
                return '';
            }

            var html = '<form method="POST" action="{{ route('evaluacion_analisis.actualizar') }}" enctype="multipart/form-data">';
            html += '@csrf';
            html += '<input type="hidden" name="id" value="' + (datos.id || '') + '">';
            html += '<input type="hidden" name="tipo" value="' + tipo + '">';

            // Generar campos según el tipo
            switch (tipo) {
                case 'analitica':
                    html += generarCamposAnalitica(datos);
                    break;
                case 'superficie':
                    html += generarCamposSuperficie(datos);
                    break;
                case 'micro':
                    html += generarCamposMicro(datos);
                    break;
            }

            html += '<div class="modal-footer">';
            html += '<button type="submit" class="btn btn-success">Actualizar</button>';
            html += '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>';
            html += '</div>';
            html += '</form>';

            $('#contenidoModalEditar').html(html);
        }

        function generarCamposAnalitica(datos) {
            var html = '<div class="container-fluid"><div class="row">';
            html += '<div class="col-md-6">';
            html += '<div class="form-group"><label>Fecha de muestra</label>';
            html += '<input type="date" name="fecha_muestra" class="form-control" value="' + (datos.fecha_muestra || '') +
                '"></div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Año</label>';
            html += '<input type="text" name="anio" class="form-control" value="' + (datos.anio || '') +
            '" readonly></div>';
            html += '<div class="form-group col-md-6"><label>Mes</label>';
            html += '<input type="text" name="mes" class="form-control" value="' + (datos.mes || '') + '" readonly></div>';
            html += '</div>';
            html += '<div class="form-group"><label>Número de muestras</label>';
            html += '<input type="number" name="numero_muestras" class="form-control" value="' + (datos.numero_muestras ||
                '') + '"></div>';
            html += '<div class="form-group"><label>Precio reducido agua</label>';
            html += '<input type="text" name="precio_reducido_agua" class="form-control" value="' + (datos
                .precio_reducido_agua || '') + '"></div>';
            html += '<div class="form-group"><label>Código</label>';
            html += '<input type="text" name="codigo" class="form-control" value="' + (datos.codigo || '') + '"></div>';
            html += '<h6 class="mt-3">Resultados (Correcto / Falso)</h6>';
            html += '</div>';
            html += '<div class="col-md-6">';
            html += '<div class="form-group"><label>Protinco</label>';
            html += '<input type="text" name="protinco" class="form-control" value="' + (datos.protinco || '') + '"></div>';
            html += '<div class="form-group"><label>Asesor</label>';
            html += '<input type="text" name="asesor" class="form-control" value="' + (datos.asesor || '') + '"></div>';
            html += '<div class="form-group"><label>Tipo referencia</label>';
            html += '<input type="text" name="tipo_referencia" class="form-control" value="' + (datos.tipo_referencia ||
                '') + '"></div>';
            html += '<div class="form-group"><label>E.O</label>';
            html += '<input type="text" name="eo" class="form-control" value="' + (datos.eo || '') + '"></div>';
            html += '<div class="form-group"><label>Descripción centro</label>';
            html += '<input type="text" name="descripcion_centro" class="form-control" value="' + (datos
                .descripcion_centro || '') + '"></div>';
            html += '<hr>';
            html += '</div>';
            html += '</div>';

            // Generar campos de resultados
            html += '<div class="row mt-3">';
            var resultFields = ['calificacion', 'estaraca', 'avia', 'mix', 'plan', 'cal', 'condicion', 'clara'];
            for (var i = 0; i < resultFields.length; i++) {
                var field = resultFields[i];
                html += '<div class="col-md-6">';
                html += '<div class="form-group mb-3">';
                html += '<label class="d-block font-weight-bold">' + field.charAt(0).toUpperCase() + field.slice(1) +
                    '</label>';
                html += '<div class="row align-items-end">';
                html += '<div class="col-md-6">';
                html += '<label class="small mb-1">Valor</label>';
                html += '<input type="text" name="' + field + '_valor" class="form-control form-control-sm" value="' + (
                    datos[field + '_valor'] || '') + '">';
                html += '</div>';
                html += '<div class="col-md-6">';
                html += '<label class="small mb-1">Resultado</label>';
                html += '<select name="' + field + '_resultado" class="form-control form-control-sm">';
                html += '<option value="">-- Seleccionar --</option>';
                var selectedCorrecto = (datos[field + '_resultado'] == 'correcto') ? ' selected' : '';
                var selectedFalso = (datos[field + '_resultado'] == 'falso') ? ' selected' : '';
                html += '<option value="correcto"' + selectedCorrecto + '>Correcto</option>';
                html += '<option value="falso"' + selectedFalso + '>Falso</option>';
                html += '</select>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            }
            html += '</div>';
            html += '</div>';
            
            // Agregar sección de archivos
            html += generarSeccionArchivos();
            
            return html;
        }

        function generarCamposSuperficie(datos) {
            var html = '<div class="container-fluid"><div class="row">';
            html += '<div class="col-md-12">';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Fecha muestras</label>';
            html += '<input type="date" name="fecha_muestra" class="form-control" value="' + (datos.fecha_muestra || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"><label>Año</label>';
            html += '<input type="text" name="anio" class="form-control" value="' + (datos.anio || '') +
            '" readonly></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Mes</label>';
            html += '<input type="text" name="mes" class="form-control" value="' + (datos.mes || '') + '" readonly></div>';
            html += '<div class="form-group col-md-6"><label>Semana</label>';
            html += '<input type="text" name="semana" class="form-control" value="' + (datos.semana || '') +
                '" readonly></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Código Centro</label>';
            html += '<input type="text" name="codigo_centro" class="form-control" value="' + (datos.codigo_centro || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"><label>Descripción Centro</label>';
            html += '<input type="text" name="descripcion_centro" class="form-control" value="' + (datos
                .descripcion_centro || '') + '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Provincia</label>';
            html += '<input type="text" name="provincia" class="form-control" value="' + (datos.provincia || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"><label>Número de muestras</label>';
            html += '<input type="number" name="numero_muestras" class="form-control" value="' + (datos.numero_muestras ||
                '') + '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Número factura</label>';
            html += '<input type="text" name="numero_factura" class="form-control" value="' + (datos.numero_factura || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"><label>Código referencia</label>';
            html += '<input type="text" name="codigo_referencia" class="form-control" value="' + (datos.codigo_referencia ||
                '') + '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Referencias</label>';
            html += '<input type="text" name="referencias" class="form-control" value="' + (datos.referencias || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"></div>'; // columna vacía para balance
            html += '</div>';

            html += '<hr><h6 class="mb-2">Resultados microbiológicos</h6>';

            // Aerobios mesófilos
            html += '<div class="form-row align-items-end">';
            html += '<div class="form-group col-md-6"><label>Aerobios mesófilos a 30 C (valor)</label>';
            html += '<input type="text" name="aerobios_mesofilos_30c_valor" class="form-control" value="' + (datos
                .aerobios_mesofilos_30c_valor || '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Resultado</label>';
            html += '<select name="aerobios_mesofilos_30c_result" class="form-control">';
            html += '<option value="">-- Seleccionar --</option>';
            var selectedCorrect1 = (datos.aerobios_mesofilos_30c_result == 'correcto') ? ' selected' : '';
            var selectedIncorrect1 = (datos.aerobios_mesofilos_30c_result == 'incorrecto') ? ' selected' : '';
            html += '<option value="correcto"' + selectedCorrect1 + '>Correcto</option>';
            html += '<option value="incorrecto"' + selectedIncorrect1 + '>Incorrecto</option>';
            html += '</select></div>';
            html += '</div>';

            // Enterobacterias
            html += '<div class="form-row align-items-end">';
            html += '<div class="form-group col-md-6"><label>Enterobacterias (valor)</label>';
            html += '<input type="text" name="enterobacterias_valor" class="form-control" value="' + (datos
                .enterobacterias_valor || '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Resultado</label>';
            html += '<select name="enterobacterias_result" class="form-control">';
            html += '<option value="">-- Seleccionar --</option>';
            var selectedCorrect2 = (datos.enterobacterias_result == 'correcto') ? ' selected' : '';
            var selectedIncorrect2 = (datos.enterobacterias_result == 'incorrecto') ? ' selected' : '';
            html += '<option value="correcto"' + selectedCorrect2 + '>Correcto</option>';
            html += '<option value="incorrecto"' + selectedIncorrect2 + '>Incorrecto</option>';
            html += '</select></div>';
            html += '</div>';

            // Listeria monocytogenes
            html += '<div class="form-row align-items-end">';
            html += '<div class="form-group col-md-6"><label>Listeria monocytogenes (valor)</label>';
            html += '<input type="text" name="listeria_monocytogenes_valor" class="form-control" value="' + (datos
                .listeria_monocytogenes_valor || '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Resultado</label>';
            html += '<select name="listeria_monocytogenes_result" class="form-control">';
            html += '<option value="">-- Seleccionar --</option>';
            var selectedCorrect3 = (datos.listeria_monocytogenes_result == 'correcto') ? ' selected' : '';
            var selectedIncorrect3 = (datos.listeria_monocytogenes_result == 'incorrecto') ? ' selected' : '';
            html += '<option value="correcto"' + selectedCorrect3 + '>Correcto</option>';
            html += '<option value="incorrecto"' + selectedIncorrect3 + '>Incorrecto</option>';
            html += '</select></div>';
            html += '</div>';

            html += '<hr>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Acción Correctiva</label>';
            html += '<input type="text" name="accion_correctiva" class="form-control" value="' + (datos.accion_correctiva ||
                '') + '"></div>';
            html += '<div class="form-group col-md-3"><label>Repetición N1</label>';
            html += '<input type="text" name="repeticion_n1" class="form-control" value="' + (datos.repeticion_n1 || '') +
                '"></div>';
            html += '<div class="form-group col-md-3"><label>Repetición N2</label>';
            html += '<input type="text" name="repeticion_n2" class="form-control" value="' + (datos.repeticion_n2 || '') +
                '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Proveedor ID</label>';
            html += '<input type="number" name="proveedor_id" class="form-control" value="' + (datos.proveedor_id || '') +
                '"></div>';
            html += '</div>';

            // Agregar sección de archivos
            html += generarSeccionArchivos();

            html += '</div>';
            html += '</div></div>';
            return html;
        }

        function generarCamposMicro(datos) {
            var html = '<div class="container-fluid"><div class="row">';
            html += '<div class="col-md-12">';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Fecha toma muestras</label>';
            html += '<input type="date" name="fecha_toma_muestras" class="form-control" value="' + (datos
                .fecha_toma_muestras || '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Año</label>';
            html += '<input type="text" name="anio" class="form-control" value="' + (datos.anio || '') +
            '" readonly></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Mes</label>';
            html += '<input type="text" name="mes" class="form-control" value="' + (datos.mes || '') + '" readonly></div>';
            html += '<div class="form-group col-md-6"><label>Semana</label>';
            html += '<input type="text" name="semana" class="form-control" value="' + (datos.semana || '') +
                '" readonly></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Código</label>';
            html += '<input type="text" name="codigo" class="form-control" value="' + (datos.codigo || '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Nombre</label>';
            html += '<input type="text" name="nombre" class="form-control" value="' + (datos.nombre || '') + '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Provincia</label>';
            html += '<input type="text" name="provincia" class="form-control" value="' + (datos.provincia || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"><label>Número de muestra</label>';
            html += '<input type="number" name="numero_muestra" class="form-control" value="' + (datos.numero_muestra ||
                '') + '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Número factura</label>';
            html += '<input type="text" name="numero_factura" class="form-control" value="' + (datos.numero_factura || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"><label>Código producto</label>';
            html += '<input type="text" name="codigo_producto" class="form-control" value="' + (datos.codigo_producto ||
                '') + '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Nombre producto</label>';
            html += '<input type="text" name="nombre_producto" class="form-control" value="' + (datos.nombre_producto ||
                '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Código proveedor</label>';
            html += '<input type="text" name="codigo_proveedor" class="form-control" value="' + (datos.codigo_proveedor ||
                '') + '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Nombre proveedor</label>';
            html += '<input type="text" name="nombre_proveedor" class="form-control" value="' + (datos.nombre_proveedor ||
                '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>TE proveedor</label>';
            html += '<input type="text" name="te_proveedor" class="form-control" value="' + (datos.te_proveedor || '') +
                '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Lote</label>';
            html += '<input type="text" name="lote" class="form-control" value="' + (datos.lote || '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Tipo</label>';
            html += '<input type="text" name="tipo" class="form-control" value="' + (datos.tipo || '') + '"></div>';
            html += '</div>';
            html += '<div class="form-row">';
            html += '<div class="form-group col-md-6"><label>Referencia</label>';
            html += '<input type="text" name="referencia" class="form-control" value="' + (datos.referencia || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"></div>'; // columna vacía
            html += '</div>';

            html += '<hr><h6 class="mb-2">Resultados microbiológicos</h6>';

            // Aerobiotico
            html += '<div class="form-row align-items-end">';
            html += '<div class="form-group col-md-6"><label>Aerobiotico (valor)</label>';
            html += '<input type="text" name="aerobiotico_valor" class="form-control" value="' + (datos.aerobiotico_valor ||
                '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Resultado</label>';
            html += '<select name="aerobiotico_resultado" class="form-control">';
            html += '<option value="">-- Seleccionar --</option>';
            var selectedCorrect1 = (datos.aerobiotico_resultado == 'correcto') ? ' selected' : '';
            var selectedIncorrect1 = (datos.aerobiotico_resultado == 'incorrecto') ? ' selected' : '';
            html += '<option value="correcto"' + selectedCorrect1 + '>Correcto</option>';
            html += '<option value="incorrecto"' + selectedIncorrect1 + '>Incorrecto</option>';
            html += '</select></div>';
            html += '</div>';

            // Entero
            html += '<div class="form-row align-items-end">';
            html += '<div class="form-group col-md-6"><label>Entero (valor)</label>';
            html += '<input type="text" name="entero_valor" class="form-control" value="' + (datos.entero_valor || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"><label>Resultado</label>';
            html += '<select name="entero_resultado" class="form-control">';
            html += '<option value="">-- Seleccionar --</option>';
            var selectedCorrect2 = (datos.entero_resultado == 'correcto') ? ' selected' : '';
            var selectedIncorrect2 = (datos.entero_resultado == 'incorrecto') ? ' selected' : '';
            html += '<option value="correcto"' + selectedCorrect2 + '>Correcto</option>';
            html += '<option value="incorrecto"' + selectedIncorrect2 + '>Incorrecto</option>';
            html += '</select></div>';
            html += '</div>';

            // E.coli
            html += '<div class="form-row align-items-end">';
            html += '<div class="form-group col-md-6"><label>E.coli (valor)</label>';
            html += '<input type="text" name="ecoli_valor" class="form-control" value="' + (datos.ecoli_valor || '') +
                '"></div>';
            html += '<div class="form-group col-md-6"><label>Resultado</label>';
            html += '<select name="ecoli_resultado" class="form-control">';
            html += '<option value="">-- Seleccionar --</option>';
            var selectedCorrect3 = (datos.ecoli_resultado == 'correcto') ? ' selected' : '';
            var selectedIncorrect3 = (datos.ecoli_resultado == 'incorrecto') ? ' selected' : '';
            html += '<option value="correcto"' + selectedCorrect3 + '>Correcto</option>';
            html += '<option value="incorrecto"' + selectedIncorrect3 + '>Incorrecto</option>';
            html += '</select></div>';
            html += '</div>';

            // S
            html += '<div class="form-row align-items-end">';
            html += '<div class="form-group col-md-6"><label>S (valor)</label>';
            html += '<input type="text" name="s_valor" class="form-control" value="' + (datos.s_valor || '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Resultado</label>';
            html += '<select name="s_resultado" class="form-control">';
            html += '<option value="">-- Seleccionar --</option>';
            var selectedCorrect4 = (datos.s_resultado == 'correcto') ? ' selected' : '';
            var selectedIncorrect4 = (datos.s_resultado == 'incorrecto') ? ' selected' : '';
            html += '<option value="correcto"' + selectedCorrect4 + '>Correcto</option>';
            html += '<option value="incorrecto"' + selectedIncorrect4 + '>Incorrecto</option>';
            html += '</select></div>';
            html += '</div>';

            // Salmonella
            html += '<div class="form-row align-items-end">';
            html += '<div class="form-group col-md-6"><label>Salmonella (valor)</label>';
            html += '<input type="text" name="salmonella_valor" class="form-control" value="' + (datos.salmonella_valor ||
                '') + '"></div>';
            html += '<div class="form-group col-md-6"><label>Resultado</label>';
            html += '<select name="salmonella_resultado" class="form-control">';
            html += '<option value="">-- Seleccionar --</option>';
            var selectedCorrect5 = (datos.salmonella_resultado == 'correcto') ? ' selected' : '';
            var selectedIncorrect5 = (datos.salmonella_resultado == 'incorrecto') ? ' selected' : '';
            html += '<option value="correcto"' + selectedCorrect5 + '>Correcto</option>';
            html += '<option value="incorrecto"' + selectedIncorrect5 + '>Incorrecto</option>';
            html += '</select></div>';
            html += '</div>';

            // Agregar sección de archivos
            html += generarSeccionArchivos();

            html += '</div>';
            html += '</div></div>';
            return html;
        }
    </script>

    {{-- Estilos adicionales para archivos --}}
    <style>
        .btn-xs {
            padding: 0.125rem 0.25rem;
            font-size: 0.75rem;
            line-height: 1.2;
            border-radius: 0.15rem;
        }
        
        .archivo-item {
            transition: background-color 0.2s;
        }
        
        .archivo-item:hover {
            background-color: #f8f9fa !important;
        }
        
        .text-truncate-custom {
            max-width: 80px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }
    </style>
@endsection
