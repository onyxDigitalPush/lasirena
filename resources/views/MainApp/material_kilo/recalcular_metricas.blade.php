@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-sync-alt"></i> Recalcular Métricas de Proveedores
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> ¿Qué hace esta herramienta?</h5>
                        <p class="mb-2">
                            Esta herramienta recalcula todas las métricas de proveedores (RG1, RL1, DEV1, ROK1, RET1) 
                            basándose en los datos actuales de incidencias y devoluciones.
                        </p>
                        <ul class="mb-0">
                            <li>Procesa todos los períodos (año-mes) con datos registrados</li>
                            <li>Cuenta las incidencias y devoluciones por clasificación</li>
                            <li>Actualiza la tabla de métricas para todos los proveedores</li>
                            <li>Los resultados se muestran en la vista "Total Kg por Proveedor"</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Importante</h5>
                        <ul class="mb-0">
                            <li><strong>Este proceso puede tardar varios minutos</strong> dependiendo de la cantidad de datos</li>
                            <li>Se recomienda ejecutar en horarios de baja actividad</li>
                            <li>El proceso borrará y recalculará TODAS las métricas existentes</li>
                            <li>No cierre ni recargue esta página durante el proceso</li>
                        </ul>
                    </div>

                    <div class="text-center mb-4">
                        <button id="btnRecalcular" class="btn btn-primary btn-lg">
                            <i class="fas fa-play"></i> Iniciar Recálculo de Métricas
                        </button>
                    </div>

                    <!-- Progress Section -->
                    <div id="progressSection" style="display: none;">
                        <div class="card border-primary">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-cog fa-spin"></i> Procesando...</h5>
                            </div>
                            <div class="card-body">
                                <div class="progress" style="height: 30px;">
                                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                         role="progressbar" style="width: 0%">
                                        <span id="progressText">Iniciando...</span>
                                    </div>
                                </div>
                                <p id="progressInfo" class="text-center mt-3 text-muted">
                                    Por favor espere...
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Results Section -->
                    <div id="resultsSection" style="display: none;">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-check-circle"></i> Recálculo Completado</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Total Períodos</h6>
                                                <h3 id="totalPeriodos" class="text-primary">0</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Procesados</h6>
                                                <h3 id="procesados" class="text-success">0</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Errores</h6>
                                                <h3 id="errores" class="text-danger">0</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Tiempo</h6>
                                                <h3 id="tiempoEjecucion" class="text-info">0s</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="erroresDetalle" style="display: none;" class="mt-3">
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle"></i> Errores Encontrados:</h6>
                                        <ul id="listaErrores" class="mb-0"></ul>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <a href="{{ route('material_kilo.total_kg_proveedor') }}" class="btn btn-success btn-lg">
                                        <i class="fas fa-table"></i> Ver Resultados en "Total Kg por Proveedor"
                                    </a>
                                    <button id="btnVolverRecalcular" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-redo"></i> Ejecutar Nuevamente
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Section -->
                    <div id="errorSection" style="display: none;">
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-times-circle"></i> Error en el Recálculo</h5>
                            <p id="errorMessage"></p>
                            <button id="btnReintentar" class="btn btn-danger mt-2">
                                <i class="fas fa-redo"></i> Reintentar
                            </button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('material_kilo.total_kg_proveedor') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Total Kg por Proveedor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    
    function iniciarRecalculo() {
        // Ocultar resultados previos
        $('#resultsSection').hide();
        $('#errorSection').hide();
        $('#btnRecalcular').prop('disabled', true);
        
        // Mostrar progreso
        $('#progressSection').show();
        $('#progressBar').css('width', '50%');
        $('#progressText').text('Procesando...');
        $('#progressInfo').text('Recalculando métricas de todos los proveedores...');
        
        // Ejecutar AJAX
        $.ajax({
            url: '{{ route("material_kilo.ejecutar_recalculo_metricas") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#progressSection').hide();
                
                if (response.success) {
                    // Mostrar resultados
                    $('#totalPeriodos').text(response.resultados.total_periodos);
                    $('#procesados').text(response.resultados.procesados);
                    $('#errores').text(response.resultados.errores);
                    $('#tiempoEjecucion').text(response.resultados.tiempo_ejecucion + 's');
                    
                    // Mostrar detalles de errores si existen
                    if (response.resultados.errores > 0 && response.resultados.detalles_errores) {
                        $('#listaErrores').empty();
                        response.resultados.detalles_errores.forEach(function(error) {
                            $('#listaErrores').append('<li>' + error + '</li>');
                        });
                        $('#erroresDetalle').show();
                    }
                    
                    $('#resultsSection').show();
                } else {
                    // Mostrar error
                    $('#errorMessage').text(response.message);
                    $('#errorSection').show();
                }
                
                $('#btnRecalcular').prop('disabled', false);
            },
            error: function(xhr, status, error) {
                $('#progressSection').hide();
                $('#errorMessage').text('Error de conexión: ' + error + '. Por favor, revise los logs del servidor.');
                $('#errorSection').show();
                $('#btnRecalcular').prop('disabled', false);
            },
            timeout: 300000 // 5 minutos de timeout
        });
    }
    
    // Eventos
    $('#btnRecalcular').click(function() {
        if (confirm('¿Está seguro de que desea recalcular TODAS las métricas de proveedores?\n\nEsto puede tardar varios minutos.')) {
            iniciarRecalculo();
        }
    });
    
    $('#btnReintentar, #btnVolverRecalcular').click(function() {
        $('#resultsSection').hide();
        $('#errorSection').hide();
        $('#erroresDetalle').hide();
        $('#btnRecalcular').prop('disabled', false);
    });
});
</script>
@endpush
@endsection
