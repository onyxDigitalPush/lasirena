@extends('layouts.app')

@section('app_name', config('app.name'))


@section('custom_head')
<style>
    .material-row:hover {
        background-color: #f8f9fa !important;
        cursor: pointer;
    }
    .material-row:hover td {
        background-color: #f8f9fa !important;
    }
    
    /* Estilos para los filtros */
    .filter-input {
        border: 1px solid #ddd;
        border-radius: 4px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .filter-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .filter-input:hover {
        border-color: #adb5bd;
    }
    
    /* Mejorar la apariencia del botón de limpiar filtros */
    #clearFilters {
        transition: all 0.3s ease;
    }
    
    #clearFilters:hover {
        transform: translateY(-1px);
    }
</style>
@endsection

@section('title_content')

    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-truck icon-gradient bg-secondary"></i>
            </div>
            <div>Entradas Productos
                <div class="page-title-subheading">
                    Lista de Las entradas de Productos
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <!-- Filtros de ordenamiento -->
            <div class="btn-group mr-2" role="group">
                <a href="{{ route('material_kilo.index') }}" 
                   class="btn btn-secondary {{ !request('orden') ? 'active' : '' }}">
                    <i class="fa fa-list mr-1"></i>Por Defecto
                </a>
                <a href="{{ route('material_kilo.index', ['orden' => 'total_kg_desc']) }}" 
                   class="btn btn-info {{ request('orden') == 'total_kg_desc' ? 'active' : '' }}">
                    <i class="fa fa-sort-amount-desc mr-1"></i>Total KG Mayor
                </a>
                <a href="{{ route('material_kilo.index', ['orden' => 'total_kg_asc']) }}" 
                   class="btn btn-warning {{ request('orden') == 'total_kg_asc' ? 'active' : '' }}">
                    <i class="fa fa-sort-amount-asc mr-1"></i>Total KG Menor
                </a>
            </div>

            <!-- Filtros de Factor de Conversión por Orden -->
            <div class="btn-group mr-2" role="group">
                <a href="{{ route('material_kilo.index', array_merge(request()->except('filtro'), ['orden' => 'factor_desc'])) }}" 
                   class="btn btn-success {{ request('orden') == 'factor_desc' ? 'active' : '' }}">
                    <i class="fa fa-sort-numeric-desc mr-1"></i>Factor Mayor
                </a>
                <a href="{{ route('material_kilo.index', array_merge(request()->except('filtro'), ['orden' => 'factor_asc'])) }}" 
                   class="btn btn-primary {{ request('orden') == 'factor_asc' ? 'active' : '' }}">
                    <i class="fa fa-sort-numeric-asc mr-1"></i>Factor Menor
                </a>
            </div>

            <!-- Filtros de Factor de Conversión -->
            <div class="btn-group mr-2" role="group">
                <a href="{{ route('material_kilo.index', array_merge(request()->except('orden'), ['filtro' => 'factor_cero'])) }}" 
                   class="btn btn-danger {{ request('filtro') == 'factor_cero' ? 'active' : '' }}">
                    Factor en 0
                </a>
            </div>
            
            <a class="m-2 btn btn-success" href="{{ route('material_kilo.total_kg_proveedor') }}">
                <i class="fa fa-bar-chart mr-2"></i>Total KG por Proveedor
            </a>
            
            <!-- Botón para limpiar filtros -->
            <button class="btn btn-outline-secondary" id="clearFilters" title="Limpiar todos los filtros">
                <i class="fa fa-eraser mr-1"></i>Limpiar Filtros
            </button>
        </div>
    </div>

    @endsection

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
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

    @section('main_content')
        <!-- Debug temporal - remover después -->
        <div class="alert alert-info">
            <strong>Debug:</strong> 
            Orden actual: {{ request('orden') ?? 'ninguno' }} | 
            Filtro actual: {{ request('filtro') ?? 'ninguno' }}
        </div>
        
        <div class="col-12 bg-white">
            <div class='mt-4 mb-4'></div>
            <table id="table_material_kilo"
                class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
                style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">Codigo Material</th>
                        <th class="text-center">Proveedor </th>
                        <th class="text-center">Descripcion </th>
                        <th class="text-center">Ctd. EM-DEV</th>
                        <th class="text-center">UMB</th>
                        <th class="text-center">Valor EM-DEV</th>
                        <th class="text-center">UMB</th>
                        <th class="text-center">MES</th>
                        <th class="text-center">Factor Conversion</th>
                        <th class="text-center">Total KG</th>
                        <th class="text-center">Eliminar</th>
                    </tr>
                    <tr>
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="Buscar Código" /></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="Buscar Proveedor" /></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="Buscar Descripción" /></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="Mes" /></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($array_material_kilo as $material_kilo)
                        <tr class="material-row" data-id="{{ $material_kilo->id }}" style="cursor: pointer;">
                            <td class="text-center">{{ $material_kilo->codigo_material }}</td>
                            <td class="text-center">{{ $material_kilo->nombre_proveedor }}</td>
                            <td class="text-center">{{ $material_kilo->nombre_material }}</td>
                            <td class="text-center">{{ $material_kilo->ctd_emdev }}</td>
                            <td class="text-center">{{ $material_kilo->umb }}</td>
                            <td class="text-center">{{ $material_kilo->valor_emdev }}</td>
                            <td class="text-center">{{ $material_kilo->umb }}</td>
                            <td class="text-center">{{ $material_kilo->mes }}</td>
                            <td class="text-center">
                                @if($material_kilo->factor_conversion !== null && $material_kilo->factor_conversion > 0)
                                    <span class="badge badge-success">{{ number_format($material_kilo->factor_conversion, 2) }}</span>
                                @elseif($material_kilo->factor_conversion == 0)
                                    <span class="badge badge-danger">{{ number_format($material_kilo->factor_conversion, 2) }}</span>
                                @else
                                    <span class="badge badge-warning">Sin Factor</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong class="text-primary">{{ number_format($material_kilo->total_kg, 2) }} KG</strong>
                            </td>
                            <td class="text-center d-flex justify-content-center">
                                <form action="{{ route('material_kilo.delete') }}" method="POST"
                                    style="display:inline-block;"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                    @csrf
                                    <input type="hidden" name="id_material_kilo" value="{{ $material_kilo->id }}">
                                    <button type="submit" class="btn btn-danger mt-2">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-4">
                {{ $array_material_kilo->links() }}
            </div>

        @endsection
        <!-- Modal Edicion de Material -->
        <div class="modal fade" id="editMaterialModal" tabindex="-1" role="dialog" aria-labelledby="editMaterialModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="editMaterialModalLabel">Editar Material</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="editMaterialForm" method="POST" action="{{ route('material_kilo.update_material') }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="codigo_material">Código Material</label>
                                <input type="text" class="form-control" id="codigo_material" name="codigo_material" readonly>
                            </div>

                            <div class="form-group">
                                <label for="nombre_material">Descripción</label>
                                <input type="text" class="form-control" id="nombre_material" name="nombre_material" readonly>
                            </div>

                            <div class="form-group">
                                <label for="nombre_proveedor">Proveedor</label>
                                <input type="text" class="form-control" id="nombre_proveedor" name="nombre_proveedor" readonly>
                            </div>

                            <div class="form-group">
                                <label for="factor_conversion">Factor Conversión</label>
                                <input type="number" step="0.00001" class="form-control" id="factor_conversion" name="factor_conversion" placeholder="Ingrese el factor de conversión">
                            </div>

                            <div class="form-group">
                                <label for="ctd_emdev">Cantidad EM-DEV</label>
                                <input type="number" step="0.01" class="form-control" id="ctd_emdev" name="ctd_emdev" readonly>
                            </div>

                            <div class="form-group">
                                <label for="valor_emdev">Valor EM-DEV</label>
                                <input type="number" step="0.01" class="form-control" id="valor_emdev" name="valor_emdev" readonly>
                            </div>

                            <div class="form-group">
                                <label for="mes">Mes</label>
                                <input type="text" class="form-control" id="mes" name="mes" readonly>
                            </div>

                            <div class="form-group">
                                <label for="total_kg">Total KG</label>
                                <input type="text" class="form-control" id="total_kg" name="total_kg" readonly>
                            </div>

                            <input type="hidden" id="material_kilo_id" name="material_kilo_id">
                            
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @section('custom_footer')
            <script>
                // URLs para AJAX generadas por Laravel
                window.materialKiloEditUrl = "{{ url('/material_kilo') }}" + "/:id/edit";
                window.materialKiloUpdateUrl = "{{ url('/material_kilo/update-material') }}";
                window.baseUrl = "{{ url('/') }}";
                
                // Debug - mostrar URLs generadas
                console.log('Edit URL template:', window.materialKiloEditUrl);
                console.log('Update URL:', window.materialKiloUpdateUrl);
                console.log('Base URL:', window.baseUrl);
            </script>
            <script type="text/javascript"
                src="{{ URL::asset('' . DIR_JS . '/main_app/material_kilo_list.js') }}?v={{ config('app.version') }}"></script>
        @endsection
