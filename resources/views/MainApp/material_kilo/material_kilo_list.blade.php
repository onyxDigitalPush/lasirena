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
            
            <a class="m-2 btn btn-success" href="{{ route('material_kilo.total_kg_proveedor') }}">
                <i class="fa fa-bar-chart mr-2"></i>Total KG por Proveedor
            </a>
        </div>
    </div>

    @endsection



    @section('main_content')
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                {{ session('error') }}
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
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Código" /></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Proveedor" />
                        </th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Descripción" />
                        </th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Mes" />
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($array_material_kilo as $material_kilo)
                        <tr>
                            <td class="text-center">{{ $material_kilo->codigo_material }}</td>
                            <td class="text-center">{{ $material_kilo->nombre_proveedor }}</td>
                            <td class="text-center">{{ $material_kilo->nombre_material }}</td>
                            <td class="text-center">{{ $material_kilo->ctd_emdev }}</td>
                            <td class="text-center">{{ $material_kilo->umb }}</td>
                            <td class="text-center">{{ $material_kilo->valor_emdev }}</td>
                            <td class="text-center">{{ $material_kilo->umb }}</td>
                            <td class="text-center">{{ $material_kilo->mes }}</td>
                            <td class="text-center">{{ $material_kilo->factor_conversion }}</td>
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
        <!-- Modal Crear Usuario -->
        <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog"
            aria-labelledby="createUserModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('proveedores.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title" id="createUserModalLabel">Crear Proveedor</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label for="codigo_proveedor">Codigo Proveedor</label>
                                <input type="number" class="form-control" id="id_proveedor" name="id_proveedor"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="nombre_proveedor">Nombre Proveedor</label>
                                <input type="text" class="form-control" id="nombre_proveedor" name="nombre_proveedor"
                                    required>
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
        @section('custom_footer')

            <script type="text/javascript"
                src="{{ URL::asset('' . DIR_JS . '/main_app/material_kilo_list.js') }}?v={{ config('app.version') }}"></script>
        @endsection
