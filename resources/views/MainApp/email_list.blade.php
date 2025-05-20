@extends('layouts.app')

@section('app_name', config('app.name'))


@section('custom_head')

@endsection


@section('title_content')

    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="fas fa-envelope icon-gradient bg-secondary"></i>
            </div>
            <div>Emails enviados
                <div class="page-title-subheading">
                    Histórico de los emails que contiene el proyecto
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <input type="hidden" value="0" name="tab_orders" id="tab_orders">
            <a class="m-2 btn btn-primary" href="{{ route('project.index') }}"><i class="fa fa-list mr-2"></i>Volver a
                proyectos</a>

        </div>
    </div>

@endsection


@section('main_content')
    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        <table id="emailsTbl"
            class="mt-4 table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
            style="width:100%">
            <thead>
                <tr>
                    <th class="text-center">Orden</th>
                    <th>Email</th>
                    <th>Nombre</th>
                    <th class="text-center">Enviado</th>
                    <th class="text-center">Leído</th>
                    <th class="text-center">Redención</th>
                    <th class="text-center">Fecha Creación</th>
                    <th></th>
                </tr>


            </thead>
            <tbody>
                @foreach ($array_email as $email)
                    <tr role="row">
                        <td class="text-center">{{ $email->email_id }}</td>
                        <td>{{ $email->recipient }}</td>
                        <td>{{ $email->getProviderName() }}</td>
                        <td class="text-center">

                            @if (count($email->email_impact) > 0)
                                <span class="toggleUserData" data-toggle="tooltip" data-html="true" data-placement="top"
                                    title="" data-original-title=""><i
                                        class="green_sm_bo fa fa-check-circle"></i><span></span></span>
                            @else
                                <span class="toggleUserData" data-toggle="tooltip" data-html="true" data-placement="top"
                                    title=""><i class="red_sm_bo fa fa-times-circle"></i><span></span></span>
                            @endif
                        </td>
                        <td class="text-center">

                            @if (count($email->opens) > 0)
                                <span class="toggleUserData" data-toggle="tooltip" data-html="true" data-placement="top"
                                    title="" data-original-title=""><i
                                        class="green_sm_bo fa fa-check-circle"></i><span></span></span>
                            @else
                                <span class="toggleUserData" data-toggle="tooltip" data-html="true" data-placement="top"
                                    title=""><i class="red_sm_bo fa fa-times-circle"></i><span></span></span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if (!$email->check_redemption())
                                <span class="toggleUserData" data-toggle="tooltip" data-html="true" data-placement="top"
                                    title=""><i class="red_sm_bo fa fa-times-circle"></i><span></span></span>
                            @else
                                <span class="toggleUserData" data-toggle="tooltip" data-html="true" data-placement="top"
                                    title="" data-original-title=""><i
                                        class="green_sm_bo fa fa-check-circle"></i><span></span></span>
                            @endif
                        </td>
                        <td class="text-center">{{ $email->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <a class="btn btn-primary" href="{{ route('email.show', $email->email_id) }}"
                                title="Ver emails que contiene proyecto"><i class="fas fa-edit"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('custom_footer')

    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/email_list.js') }}?v={{ config('app.version') }}">
    </script>
@endsection
