@extends('layouts.app')
<!-- Modal Agregar Analítica  -->
<div class="modal fade" id="modalAgregarAnalitica" tabindex="-1" role="dialog"
    aria-labelledby="modalAgregarAnaliticaLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formAgregarAnalitica" method="POST" action="{{ route('evaluacion_analisis.guardar_analitica') }}">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modalAgregarAnaliticaLabel">Agregar Analítica a <span
                            id="nombreTiendaModal"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="num_tienda" id="tienda_id_modal">
                    <div class="form-group">
                        <label for="asesor_externo_nombre">Asesor Externo - Nombre</label>
                        <input type="text" class="form-control" name="asesor_externo_nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="asesor_externo_empresa">Asesor Externo - Empresa</label>
                        <input type="text" class="form-control" name="asesor_externo_empresa" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_real_analitica">Fecha Real de la Analítica</label>
                        <input type="date" class="form-control" name="fecha_real_analitica" required>
                    </div>
                    <div class="form-group">
                        <label for="periodicidad">Periodicidad Temporal</label>
                        <select class="form-control" name="periodicidad" required>
                            <option value="1 mes">1 mes</option>
                            <option value="3 meses">3 meses</option>
                            <option value="6 meses">6 meses</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="proveedor_id">Proveedor Relacionado</label>
                        <select class="form-control" name="proveedor_id">
                            <option value="">-- Seleccionar proveedor --</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_proveedor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo_analitica">Tipo Analítica</label>
                        <select class="form-control" name="tipo_analitica" required>
                            <option value="Resultados agua">Resultados agua</option>
                            <option value="Tendencias superficie">Tendencias superficie</option>
                            <option value="Tendencias micro">Tendencias micro</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar Analítica</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@section('title_content')
    {{-- Espacio para el mensaje de success del backend --}}

    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-flask icon-gradient bg-secondary"></i>
            </div>
            <div>Historial de Evaluaciones Analíticas
                <div class="page-title-subheading">
                    Listado de tiendas y analíticas
                </div>
            </div>
        </div>
    </div>
    @if (method_exists($tiendas, 'links'))
        <div class="d-flex justify-content-center">{{ $tiendas->links() }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@endsection
<br><br>
@section('main_content')
    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        <table class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Num Tienda</th>
                    <th class="text-center">Nombre Tienda</th>
                    <th class="text-center">Dirección</th>
                    <th class="text-center">Responsable</th>
                    <th class="text-center">Agregar Analítica</th>
                    <th class="text-center">Estado Analítica</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tiendas as $tienda)
                    <tr>
                        <td class="text-center">{{ $tienda->num_tienda }}</td>
                        <td class="text-center">{{ $tienda->nombre_tienda }}</td>
                        <td class="text-center">{{ $tienda->direccion_tienda }}</td>
                        <td class="text-center">{{ $tienda->responsable }}</td>
                        <td class="text-center">
                            <a class="m-2 btn btn-primary btn-agregar-analitica" href="#" data-toggle="modal"
                                data-target="#modalAgregarAnalitica" data-id="{{ $tienda->num_tienda }}"
                                data-nombre="{{ $tienda->nombre_tienda }}">
                                <i class="metismenu-icon fa fa-plus mr-2"></i>Agregar Analítica
                            </a>
                        </td>
                        <td class="text-center"
                            @if($tienda->analitica_vencida ?? false) style="background: #ffcccc; color: #a94442; font-weight: bold;" @endif>
                            @if($tienda->fecha_ultima_analitica)
                                Última: {{ $tienda->fecha_ultima_analitica }}<br>
                                Periodicidad: {{ $tienda->periodicidad_ultima_analitica }}<br>
                                @if($tienda->analitica_vencida)
                                    Falta analítica
                                @else
                                    Al día
                                @endif
                            @else
                                Sin analíticas registradas<br>
                                <span style="color: #a94442;">Falta analítica</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- Paginación si usas ->paginate() en el controlador --}}

    </div>
@endsection
<!-- jQuery y Bootstrap JS solo para esta vista -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Verifica si jQuery está cargado
    console.log('jQuery version:', typeof $);
    if (typeof $ === 'undefined') {
        console.warn('jQuery NO está cargado. El script de la modal no funcionará.');
    } else {
        // Delegación de eventos para asegurar que funcione aunque la tabla se recargue
        $(document).on('click', '.btn-agregar-analitica', function(e) {
            e.preventDefault();
            console.log('Click en btn-agregar-analitica');
            var tiendaId = $(this).attr('data-id');
            var tiendaNombre = $(this).attr('data-nombre');
            $('#tienda_id_modal').val(tiendaId);
            $('#nombreTiendaModal').text(tiendaNombre);
            $('#modalAgregarAnalitica').modal('show');
        });
    }
</script>
