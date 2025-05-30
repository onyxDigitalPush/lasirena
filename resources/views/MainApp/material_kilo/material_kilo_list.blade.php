
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


@endsection

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
        <table id="table_proveedores"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">Codigo Proveedor</th>
                    <th class="text-center">Nombre Proveedor</th>
                    <th class="text-center">Ver Articulos</th>
                    <th class="text-center">Editar</th>
                    <th class="text-center">Eliminar</th>
                </tr>
                <tr>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Código" /></th>
                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Nombre" /></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>


            <tbody>
                @foreach ($array_material_kilo as $material_kilo)
                    <tr>
                        <td class="text-center">{{ $material_kilo->id_proveedor }}</td>
                        <td class="text-center">{{ $material_kilo->nombre_proveedor }}</td>
                        <td class="text-center">
                            <a href="{{ url('material/' . (int) $material_kilo->id_proveedor . '/list') }}"
                                class="btn btn-primary">
                                <i class="metismenu-icon fa fa-eye"></i>
                            </a>

                        </td>
                        <td class="text-center">
                            <a href="#" class="btn btn-primary open-modal"
                                data-url="{{ url('proveedor/' . $material_kilo->id_proveedor . '/edit') }}">
                                <i class="metismenu-icon fa fa-pencil"></i>
                            </a>
                        </td>
                        <td class="text-center d-flex justify-content-center">
                            <form action="{{ route('usuarios.delete') }}" method="POST" style="display:inline-block;"
                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                @csrf
                                {{-- <input type="hidden" name="id" value="{{ $user->id }}"> --}}
                                <button type="submit" class="btn btn-danger mt-2">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endsection
    <!-- Modal Crear Usuario -->
    <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel">
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
                            <input type="number" class="form-control" id="id_proveedor" name="id_proveedor" required>
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
    <!-- Modal Edicion -->
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="userModalLabel">Editar Proveedor</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Aquí se cargarán los datos del usuario con AJAX -->
                    <form id="editUserForm" method="POST" action="{{ route('proveedores.update') }}">
                        @csrf
                        <div class="form-group">
                            <label for="codigo_proveedor">Codigo Proveedor</label>
                            <input type="number" class="form-control" id="codigo_proveedor_edit"
                                name="id_proveedor">
                        </div>
                        <input type="hidden" id="codigo_proveedor_old" name="codigo_proveedor_old">
                        <div class="form-group">
                            <label for="nombre_proveedor_edit">Nombre Proveedor</label>
                            <input type="text" class="form-control" id="nombre_proveedor_edit"
                                name="nombre_proveedor_edit">
                        </div>
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
            src="{{ URL::asset('' . DIR_JS . '/main_app/material_kilo_list.js') }}?v={{ config('app.version') }}"></script>
    @endsection



