@extends('layouts.app')

@section('app_name', config('app.name'))


@section('custom_head')

@endsection

@section('title_content')

    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="fa fa-desktop icon-gradient bg-secondary"></i>
            </div>
            <div>Usuarios
                <div class="page-title-subheading">
                    Lista de Usuarios Plataforma
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <input type="hidden" value="0" name="tab_orders" id="tab_orders">

            <a class="m-2 btn btn-primary" href="{{ route('upload_excel.index') }}">
                <i class="fa fa-plus mr-2"></i>Crear Usuario</a>
        </div>
    </div>

@endsection



@section('main_content')
    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        <table id="shippingReferenceTbl"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">Nombre</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Acceso Usuario</th>
                    <th class="text-center">Editar</th>
                    <th class="text-center">Eliminar</th>
                </tr>


            </thead>
            <tbody>
                @foreach ($array_users as $user)
                    <tr>
                        <td class="text-center">{{ $user->name }}</td>
                        <td class="text-center">{{ $user->email }}</td>
                        <td class="text-center">
                            @if ($user->type_user == 1)
                                Administrador
                            @elseif ($user->type_user == 2)
                                Ofertas
                            @elseif ($user->type_user == 3)
                                Proyectos
                            @else
                                Usuario
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="#" class="btn btn-primary open-modal"
                                data-url="{{ url('usuario/' . $user->id . '/edit') }}">
                                Editar
                            </a>
                        </td>
                        {{-- <td class="text-center">
                            <a href="{{ route('usuarios.destroy', $user->id) }}" class="btn btn-danger"><i
                                    class="fa fa-trash"></i></a>
                        </td> --}}

                    </tr>
                @endforeach
            </tbody>


        @endsection
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
                        <form id="editUserForm">
                            <input type="hidden" name="id" id="userId">
                            <div class="form-group">
                                <label for="userName">Nombre</label>
                                <input type="text" class="form-control" id="userName" name="name">
                            </div>
                            <div class="form-group">
                                <label for="userEmail">Correo</label>
                                <input type="email" class="form-control" id="userEmail" name="email">
                            </div>
                            <div class="form-group">
                                <label for="userType">Tipo de Usuario</label>
                                <select class="form-control" id="userType" name="type_user">
                                    <option value="">Seleccionar</option>
                                    <option value="1">Administrador</option>
                                    <option value="2">Ofertas</option>
                                    <option value="3">Proyectos</option>
                                    <option value="4">Usuario</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>




        @section('custom_footer')

            <script type="text/javascript"
                src="{{ URL::asset('' . DIR_JS . '/main_app/user_list.js') }}?v={{ config('app.version') }}"></script>
        @endsection
