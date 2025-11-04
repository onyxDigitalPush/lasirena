@extends('layouts.app')

@section('app_name', config('app.name'))


@section('custom_head')

@endsection

@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="metismenu-icon fa fa-truck icon-gradient bg-secondary"></i>
            </div>
            <div>Materiales
                <div class="page-title-subheading">
                    Lista General de Materiales
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <input type="hidden" value="0" name="tab_orders" id="tab_orders">

            <!-- Filtros -->
            <div class="btn-group mr-2" role="group">
                <a href="{{ route('materiales.index') }}" 
                   class="btn btn-secondary {{ !request('filtro') ? 'active' : '' }}">
                    Todos
                </a>
                <a href="{{ route('materiales.index', ['filtro' => 'con_factor']) }}" 
                   class="btn btn-info {{ request('filtro') == 'con_factor' ? 'active' : '' }}">
                    Con Factor
                </a>
                <a href="{{ route('materiales.index', ['filtro' => 'sin_factor']) }}" 
                   class="btn btn-warning {{ request('filtro') == 'sin_factor' ? 'active' : '' }}">
                    Sin Factor
                </a>
                <a href="{{ route('materiales.index', ['filtro' => 'factor_cero']) }}" 
                   class="btn btn-danger {{ request('filtro') == 'factor_cero' ? 'active' : '' }}">
                    Factor en 0
                </a>
            </div>

            <a class="m-2 btn btn-primary" href="#" data-toggle="modal" data-target="#createMaterial">
                <i class="metismenu-icon fa fa-plus mr-2"></i>Crear Material
            </a>
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
    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        <table id="table_materiales_global"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">Codigo Material</th>
                    <th class="text-center">Descripcion Material</th>
                    <th class="text-center">Codigo Proveedor</th>
                    <th class="text-center">Nombre Proveedor</th>
                    <th class="text-center">Factor Conversion</th>
                    <th class="text-center">Editar</th>
                    <th class="text-center">Eliminar</th>
                </tr>
                <tr>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Código Material" /></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Descripción" /></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Código Proveedor" /></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Proveedor" /></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($materiales as $material)
                    <tr>
                        <td class="text-center">{{ $material->codigo }}</td>
                        <td class="text-center">{{ $material->descripcion }} </td>
                        <td class="text-center">{{ $material->proveedor_id }}</td>
                        <td class="text-center">{{ $material->nombre_proveedor }}</td>
                        <td class="text-center">
                            @if($material->factor_conversion !== null && $material->factor_conversion > 0)
                                <span class="badge badge-success">{{ number_format($material->factor_conversion, 5) }}</span>
                            @elseif($material->factor_conversion == 0)
                                <span class="badge badge-danger">{{ number_format($material->factor_conversion, 2) }}</span>
                            @else
                                <span class="badge badge-warning">Sin Factor</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="#" class="btn btn-primary open-modal"
                                data-url="{{ url('material/' . $material->id . '/edit') }}">
                                <i class="metismenu-icon fa fa-pencil"></i>
                            </a>
                        </td>
                        <td class="text-center d-flex justify-content-center">
                            <form action="{{ route('materiales.delete') }}" method="POST" style="display:inline-block;"
                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este material?');">
                                @csrf
                                <input type="hidden" name="id_material" value="{{ $material->id }}">
                                <button type="submit" class="btn btn-danger mt-2">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-4 mb-4">
            {{ $materiales->links() }}
        </div>
    </div>
@endsection

<!-- Modal Crear material -->
<div class="modal fade" id="createMaterial" tabindex="-1" role="dialog"
    aria-labelledby="createMaterialLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('materiales.store.global') }}">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="createMaterialLabel">Crear Material</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="create_proveedor">Proveedor</label>
                        <select class="form-control" id="create_proveedor" name="id_proveedor" required>
                            <option value="">Seleccionar proveedor...</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id_proveedor }}">
                                    {{ $proveedor->id_proveedor }} - {{ $proveedor->nombre_proveedor }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_codigo">Codigo Material</label>
                        <input type="text" class="form-control" id="create_codigo" name="codigo" required>
                    </div>

                    <div class="form-group">
                        <label for="create_descripcion">Descripcion</label>
                        <input type="text" class="form-control" id="create_descripcion" name="descripcion"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="create_jerarquia">Jerarquia</label>
                        <input type="text" class="form-control" id="create_jerarquia" name="jerarquia"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="create_factor_conversion">Factor Conversion</label>
                        <input type="number" step="0.00001" class="form-control" id="create_factor_conversion" name="factor_conversion" placeholder="Ingrese el factor de conversión">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Crear</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edicion -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="userModalLabel">Editar Material</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Aquí se cargarán los datos del usuario con AJAX -->
                <form id="editUserForm" method="POST" action="{{ route('materiales.update') }}">
                    @csrf

                    <div class="form-group">
                        <label for="proveedor_nombre_edit">Proveedor</label>
                        <input type="text" class="form-control" id="proveedor_nombre_edit" readonly>
                    </div>

                    <div class="form-group">
                        <label for="codigo">Codigo Material</label>
                        <input type="text" class="form-control" id="codigo" name="codigo">
                    </div>
                    <div class="form-group">
                        <label for="jerarquia">Jerarquia</label>
                        <input type="text" class="form-control" id="jerarquia" name="jerarquia">
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripcion</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion">
                    </div>
                    <div class="form-group">
                        <label for="factor_conversion">Factor Conversion</label>
                        <input type="number" step="0.00001" class="form-control" id="factor_conversion" name="factor_conversion" placeholder="Ingrese el factor de conversión">
                    </div>
                    <input type="hidden" name="proveedor_id" id="proveedor_id" >
                    <input type="hidden" id="id" name="id">
                    <button type="submit" class="btn btn-primary">Guardar
                        Cambios</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('custom_footer')
    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/materiales_global_list.js') }}?v={{ config('app.version') }}"></script>
@endsection
