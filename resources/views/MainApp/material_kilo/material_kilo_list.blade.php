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
    
    /* Indicador de búsqueda activa */
    .filter-input.searching {
        border-color: #ffc107;
        background-color: #fff3cd;
    }
    
    .filter-input.has-value {
        border-color: #28a745;
        background-color: #d4edda;
    }
    
    /* Mejorar la apariencia del botón de limpiar filtros */
    #clearFilters {
        transition: all 0.3s ease;
    }
    
    #clearFilters:hover {
        transform: translateY(-1px);
    }
    
    /* Indicador de carga */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Estilos para el card de búsqueda exacta */
    .card-header.bg-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    .search-exact-card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: box-shadow 0.3s ease;
    }
    
    .search-exact-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
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
                <a href="{{ route('material_kilo.index', array_merge(request()->except('orden'), ['orden' => 'total_kg_desc'])) }}" 
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
        <!-- Filtros de búsqueda exacta con backend -->
        <div class="card mb-3 search-exact-card">
            <div class="card-header bg-primary text-white">
                <i class="fa fa-search mr-2"></i>
                <strong>Búsqueda Exacta </strong>
            </div>
            <div class="card-body">
                <p class="mb-3 text-muted">
                    <i class="fa fa-info-circle mr-1"></i>
                    <small>Estos filtros buscan coincidencias <strong>exactas</strong>. Ingrese el código o ID completo que desea buscar.</small>
                </p>
                <form action="{{ route('material_kilo.index') }}" method="GET" class="form-inline">
                    <!-- Mantener parámetros de ordenamiento y filtros existentes -->
                    @if(request('orden'))
                        <input type="hidden" name="orden" value="{{ request('orden') }}">
                    @endif
                    @if(request('filtro'))
                        <input type="hidden" name="filtro" value="{{ request('filtro') }}">
                    @endif
                    
                    <div class="form-group mr-3 mb-2">
                        <label for="search_codigo_material" class="mr-2">Código Material:</label>
                        <input type="text" 
                               class="form-control" 
                               id="search_codigo_material" 
                               name="search_codigo_material" 
                               placeholder="Ej: 123456"
                               value="{{ request('search_codigo_material') }}"
                               style="width: 150px;">
                    </div>
                    
                    <div class="form-group mr-3 mb-2">
                        <label for="search_proveedor_id" class="mr-2">ID Proveedor:</label>
                        <input type="text" 
                               class="form-control" 
                               id="search_proveedor_id" 
                               name="search_proveedor_id" 
                               placeholder="Ej: 1001"
                               value="{{ request('search_proveedor_id') }}"
                               style="width: 150px;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary mr-2 mb-2">
                        <i class="fa fa-search mr-1"></i>Buscar
                    </button>
                    
                    <a href="{{ route('material_kilo.index', array_merge(request()->except(['search_codigo_material', 'search_proveedor_id']), [])) }}" 
                       class="btn btn-secondary mb-2">
                        <i class="fa fa-times mr-1"></i>Limpiar Búsqueda
                    </a>
                </form>
                
                @if(request('search_codigo_material') || request('search_proveedor_id'))
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fa fa-info-circle mr-1"></i>
                            Mostrando resultados exactos para:
                            @if(request('search_codigo_material'))
                                <strong>Código Material: {{ request('search_codigo_material') }}</strong>
                            @endif
                            @if(request('search_codigo_material') && request('search_proveedor_id'))
                                |
                            @endif
                            @if(request('search_proveedor_id'))
                                <strong>ID Proveedor: {{ request('search_proveedor_id') }}</strong>
                            @endif
                        </small>
                    </div>
                @endif
            </div>
        </div>
    
        <!-- Info de búsqueda activa -->
        @if(request()->hasAny(['codigo_material', 'proveedor_id', 'nombre_proveedor', 'nombre_material', 'mes']))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fa fa-filter mr-2"></i>
                <strong>Filtros activos:</strong>
                @if(request('codigo_material'))
                    <span class="badge badge-primary mr-1">Código: {{ request('codigo_material') }}</span>
                @endif
                @if(request('proveedor_id'))
                    <span class="badge badge-primary mr-1">ID Proveedor: {{ request('proveedor_id') }}</span>
                @endif
                @if(request('nombre_proveedor'))
                    <span class="badge badge-primary mr-1">Proveedor: {{ request('nombre_proveedor') }}</span>
                @endif
                @if(request('nombre_material'))
                    <span class="badge badge-primary mr-1">Material: {{ request('nombre_material') }}</span>
                @endif
                @if(request('mes'))
                    <span class="badge badge-primary mr-1">Mes: {{ request('mes') }}</span>
                @endif
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        
        
        <div class="col-12 bg-white">
            <div class='mt-4 mb-4'></div>
            <table id="table_material_kilo"
                class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
                style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">Codigo Material</th>
                        <th class="text-center">Descripcion</th>
                        <th class="text-center">ID Proveedor</th>
                        <th class="text-center">Proveedor</th>
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
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="Buscar Descripción" /></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="ID Proveedor" /></th>
                        <th><input type="text" class="form-control form-control-sm filter-input" placeholder="Buscar Proveedor" /></th>
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
                    @forelse ($array_material_kilo as $material_kilo)   
                             
                    <tr class="material-row" data-id="{{ $material_kilo->id }}" style="cursor: pointer;">
                        <td class="text-center">{{ $material_kilo->codigo_material }}</td>
                        <td class="text-center">{{ $material_kilo->nombre_material }}</td>
                        <td class="text-center">{{ $material_kilo['proveedor_id'] }}</td>
                        <td class="text-center">{{ $material_kilo->nombre_proveedor }}</td>
                        <td class="text-center">{{ $material_kilo->ctd_emdev }}</td>
                        <td class="text-center">{{ $material_kilo->umb }}</td>
                        <td class="text-center">{{ number_format($material_kilo->valor_emdev, 2, ',', '.') }}</td>
                        <td class="text-center">{{ $material_kilo->umb }}</td>
                        <td class="text-center">{{ $material_kilo->mes }}</td>
                        <td class="text-center">
                            @if($material_kilo->factor_conversion !== null && $material_kilo->factor_conversion > 0)
                                <span class="badge badge-success">{{ number_format($material_kilo->factor_conversion, 2, ',', '.') }}</span>
                            @elseif($material_kilo->factor_conversion == 0)
                                <span class="badge badge-danger">{{ number_format($material_kilo->factor_conversion, 2, ',', '.') }}</span>
                                @else
                                    <span class="badge badge-warning">Sin Factor</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong class="text-primary">{{ number_format($material_kilo->total_kg, 2, ',', '.') }} KG</strong>
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
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4">
                                <div class="alert alert-warning mb-0">
                                    <i class="fa fa-search fa-2x mb-2"></i>
                                    <h5>No se encontraron resultados</h5>
                                    <p class="mb-0">
                                        @if(request()->hasAny(['codigo_material', 'proveedor_id', 'nombre_proveedor', 'nombre_material', 'mes']))
                                            No hay materiales que coincidan con los filtros aplicados.
                                            <br><a href="{{ route('material_kilo.index') }}" class="btn btn-primary mt-2">
                                                <i class="fa fa-refresh mr-1"></i>Ver todos los registros
                                            </a>
                                        @else
                                            No hay materiales registrados en el sistema.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-4">
                {{ $array_material_kilo->links() }}
            </div>

        @endsection
        <!-- Modal Edicion de Material -->
        <div class="modal fade" id="editMaterialModal" tabindex="-1" role="dialog" aria-labelledby="editMaterialModalLabel">
            <div class="modal-dialog modal-lg" role="document">
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
                                <input type="number" step="0.00001" class="form-control" id="factor_conversion" name="factor_conversion" placeholder="Ingrese el factor de conversión" required>
                            </div>

                            <hr>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="aplicar_rango" name="aplicar_rango" value="1">
                                    <label class="custom-control-label" for="aplicar_rango">
                                        <strong>Aplicar factor a múltiples registros (rango de fechas)</strong>
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Si no marca esta opción, solo se actualizará el registro actual.
                                </small>
                            </div>

                            <div id="rango_fechas_container" style="display: none;">
                                <h5>Rango de Fechas para Aplicar Factor</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mes_inicio">Mes Inicio</label>
                                        <select class="form-control" id="mes_inicio" name="mes_inicio">
                                            <option value="">Seleccione mes</option>
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="anio_inicio">Año Inicio</label>
                                        <input type="number" class="form-control" id="anio_inicio" name="anio_inicio" min="2020" max="2030" placeholder="2024">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mes_fin">Mes Fin</label>
                                        <select class="form-control" id="mes_fin" name="mes_fin">
                                            <option value="">Seleccione mes</option>
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="anio_fin">Año Fin</label>
                                        <input type="number" class="form-control" id="anio_fin" name="anio_fin" min="2020" max="2030" placeholder="2024">
                                    </div>
                                </div>
                            </div>
                            </div>
                            <hr>

                            <div class="form-group">
                                <label for="ctd_emdev">Cantidad EM-DEV (registro actual)</label>
                                <input type="number" step="0.01" class="form-control" id="ctd_emdev" name="ctd_emdev" readonly>
                            </div>

                            <div class="form-group">
                                <label for="valor_emdev">Valor EM-DEV (registro actual)</label>
                                <input type="number" step="0.01" class="form-control" id="valor_emdev" name="valor_emdev" readonly>
                            </div>

                            <div class="form-group">
                                <label for="mes">Mes (registro actual)</label>
                                <input type="text" class="form-control" id="mes" name="mes" readonly>
                            </div>

                            <div class="form-group">
                                <label for="total_kg">Total KG (registro actual)</label>
                                <input type="text" class="form-control" id="total_kg" name="total_kg" readonly>
                            </div>

                            <input type="hidden" id="material_kilo_id" name="material_kilo_id">
                            <input type="hidden" id="codigo_material_hidden" name="codigo_material_hidden">
                            
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" id="btn_guardar_material">
                                    <i class="fa fa-save mr-1"></i><span id="btn_text">Guardar Cambios</span>
                                </button>
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
