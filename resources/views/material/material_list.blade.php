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
                    Lista de Materiales del proveedor {{ $proveedor->nombre_proveedor }}
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <input type="hidden" value="0" name="tab_orders" id="tab_orders">

            <a class="m-2 btn btn-primary" href="#" data-toggle="modal" data-target="#createMaterial">
                <i class="metismenu-icon fa fa-user mr-2"></i></i>Crear Material
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
        <table id="shippingReferenceTbl"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">Codigo Material</th>
                    <th class="text-center">Jerarquia</th>
                    <th class="text-center">Descripcion Material</th>
                    <th class="text-center">Editar</th>
                    <th class="text-center">Eliminar</th>
                </tr>


            </thead>
            <tbody>
                @foreach ($materiales as $material)
                    <tr>
                        <td class="text-center">{{ $material->codigo }}</td>
                        <td class="text-center">{{ $material->jerarquia }}</td>
                        <td class="text-center">{{ $material->descripcion }} </td>
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

        @endsection
        <!-- Modal Crear material -->
        <div class="modal fade" id="createMaterial" tabindex="-1" role="dialog"
            aria-labelledby="createMaterialLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('materiales.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title" id="createMaterialLabel">Crear Material</h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <input type="hidden" name="id_proveedor" value="{{ $proveedor->id_proveedor }}">
                        <div class="modal-body">
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
                        <h4 class="modal-title" id="userModalLabel">Editar Usuario</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Aquí se cargarán los datos del usuario con AJAX -->
                        <form id="editUserForm" method="POST" action="{{ route('materiales.update') }}">
                            @csrf

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
                src="{{ URL::asset('' . DIR_JS . '/main_app/material_list.js') }}?v={{ config('app.version') }}"></script>
        @endsection
