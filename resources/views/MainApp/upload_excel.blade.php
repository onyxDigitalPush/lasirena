@extends('layouts.app')

@section('app_name', config('app.name'))


@section('custom_head')
    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/xlsx.full.min.js') }}?v={{ config('app.version') }}">
    </script>
    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/upload_excel.js') }}?v={{ config('app.version') }}">
    </script>

    <meta name="viewport" content="width=device-width, initial-scale=1">
@endsection


@section('title_content')
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="fas fa-file-excel icon-gradient bg-secondary"></i>
            </div>
            <div>Crear Proyecto
                <div class="page-title-subheading">
                    Carga el listado de proveedores para enviar el email
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

    <div class="col-12  bg-white">
        <div class='mt-4 mb-4'></div>
        {!! Form::open(['route' => ['upload_excel.store'], 'method' => 'POST', 'files' => true, 'id' => 'form_upload_excel']) !!}
        @csrf
        <div class="row">

            <div class="position-relative form-group text-left col-sm-6 col-md-6 col-lg-4 col-xl-2">
                <label for="project_name" class="">Nombre proyecto</label>
                <input value="" name="project_name" id="project_name" type="text" class="form-control"
                    data-validacion="obligatorio" autocomplete="off">
            </div>
            <div class="position-relative form-group text-left col-sm-6 col-md-6 col-lg-4 col-xl-2">
                <label for="filterSearch" class="">Asunto</label>
                <input value="" name="subject" id="subject" type="text" class="form-control" data-validacion="obligatorio"
                    autocomplete="off">
            </div>
            <div class="position-relative form-group text-left col-sm-6 col-md-6 col-lg-4 col-xl-3">
                <label for="filterSearch" class="">Con copia a (separado por ; )</label>
                <input value="{{ $user_email }}" name="ccs" id="ccs" type="text" class="form-control" autocomplete="off"
                    data-validacion="obligatorio email_multiple">
            </div>
            <div class="position-relative form-group text-left col-sm-6 col-md-6 col-lg-4 col-xl-3">
                <label for="filterSearch" class="">Responder a</label>
                <input value="{{ $user_email }}" name="reply_to" id="ccs" type="text" data-validacion="obligatorio email"
                    autocomplete="off" class="form-control">
            </div>

            <input type="hidden" name="str_projects" id="str_projects" value="">
        </div>


        <div class="row mt-2">
            <div class="col-sm-6 col-md-5 col-lg-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupFileAddon01">
                            <i class="fas fa-file-excel"></i>
                        </span>
                    </div>
                    <div class="custom-file ">
                        <input type="file" class="custom-file-input" id="excelFile" name="excelFile"
                            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                            aria-describedby="inputGroupFileAddon01" data-validacion="obligatorio" autocomplete="off">
                        <label class="custom-file-label " for="excelFile" data-browse="Examinar">
                            Elija un archivo de mails </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-7 mt-3 mt-sm-0">
                <button id="excelParse" type="button" class="m-1 btn  btn-success upload_excel_rates">
                    <i class="fas fa-spinner fa-spin d-none"></i>
                    <i class="fa fa-file-upload mr-2"></i>
                    Importar excel </button>
                <div id="spinner" style="display: none;" class="m-1 spinner-border text-secondary"></div>
            </div>

        </div>

        </form>
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="table-responsive">
                <table id="projectExcelTbl" style="width: 100%;"
                    class="table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
                    role="grid">
                    <thead>
                        <tr role="row">
                            <th class="text-center">Familia</th>
                            <th class="text-center">Cod Prov</th>
                            <th class="text-center">Nombre Proveedor</th>
                            <th class="text-center">Prod</th>
                            <th class="text-center">Descripción producto</th>
                            <th class="text-center">Dto</th>
                            <th class="text-center">Fecha Inicio</th>
                            <th class="text-center">Fecha Final</th>
                            <th class="text-center">Inicio Sap</th>
                            <th class="text-center">Fin Sap</th>
                            <th class="text-center">Previsiones</th>
                            <th class="text-center">Idioma</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Redención</th>
                            <th class="text-center">Enviar Mail</th>
                            <th class="text-center"> Previsiones (desde el envío de la comunicación hasta finalizar la oferta) </th>
                        </tr>
                    </thead>
                    <tbody id="excel_table_body">

                    </tbody>
                    <div class="row d-none" id="rowError">
                        <div class="col-12 mt-2">
                            <div class="card">
                                <div class="card-body" style="background: #EEF1F4;">

                                </div>
                            </div>
                        </div>
                    </div>
                </table>

            </div>
        </div>
        <div class="col-12 text-right pb-4 pt-4 ">

            <button type="button" id="confirm_button" disabled class="mt-4 btn btn-success"><i class="fas fa-save pr-2"></i>
                Enviar Emails</button>

        </div>
    </div>
@endsection
