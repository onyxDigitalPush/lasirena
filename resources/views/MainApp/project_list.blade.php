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
            <div>Proyectos
                <div class="page-title-subheading">
                    Proyectos que se han generado
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <input type="hidden" value="0" name="tab_orders" id="tab_orders">

            <a class="m-2 btn btn-primary" href="{{ route('upload_excel.index') }}">
                <i class="fa fa-plus mr-2"></i>Crear Proyecto</a>
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
                    <th class="text-center">Orden</th>
                    <th class="text-center">Nombre</th>
                    <th class="text-center">Asunto</th>
                    <th class="text-center">Enviados</th>
                    <th class="text-center">Leídos</th>
                    <th class="text-center">Fecha Creación</th>
                    <th class="text-center">Documento</th>
                    <th></th>
                </tr>


            </thead>
            <tbody>
                @foreach ($array_project as $project)
                    @php
                        $open_count_distinct = 0;
                        $array_opens_counted = [];
                        foreach ($project->opens as $open) {
                            if (!in_array($open->newsletter_reference, $array_opens_counted)) {
                                $open_count_distinct += 1;
                                array_push($array_opens_counted, $open->newsletter_reference);
                            }
                        }
                        //$emails_count = $project->emails->count();
                        $emails_count = $project->emails_count();
                        $sended_percentage = $project->email_impact->count() == 0 ? 0 : ($project->email_impact->count() * 100) / $emails_count;
                        $color_sended_bar = $sended_percentage <= 25 ? 'danger' : ($sended_percentage <= 50 ? 'warning' : ($sended_percentage <= 75 ? 'info' : 'success'));
                        
                        $read_percentage = $open_count_distinct == 0 ? 0 : ($open_count_distinct * 100) / $emails_count;
                        $color_read_bar = $read_percentage <= 25 ? 'danger' : ($read_percentage <= 50 ? 'warning' : ($read_percentage <= 75 ? 'info' : 'success'));
                        
                    @endphp

                    <tr>
                        <td class="text-center">{{ $project->project_id }}</td>
                        <td class="text-center">{{ $project->project_name }}</td>
                        <td class="text-center">{{ $project->subject }}</td>
                        <td class="text-center">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left pr-2">
                                            <div class="widget-numbers fsize-1 text-{{ $color_sended_bar }}">
                                                {{ number_format($sended_percentage, 0) }}%
                                            </div>
                                        </div>
                                        <div class="widget-content-right w-100">
                                            <div class="progress-bar-xs progress">
                                                <div class="progress-bar bg-{{ $color_sended_bar }}" role="progressbar"
                                                    aria-valuenow="{{ $sended_percentage }}" aria-valuemin="0"
                                                    aria-valuemax="100" style="width: {{ $sended_percentage }}%;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- {{ $project->email_impact->count() }} / {{ $emails_count }} --}}
                        </td>
                        <td class="text-center">
                            <div class="widget-content p-0">
                                <div class="widget-content-outer">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left pr-2">
                                            <div class="widget-numbers fsize-1 text-{{ $color_read_bar }}">
                                                {{ number_format($read_percentage, 0) }}%
                                            </div>
                                        </div>
                                        <div class="widget-content-right w-100">
                                            <div class="progress-bar-xs progress">
                                                <div class="progress-bar bg-{{ $color_read_bar }}" role="progressbar"
                                                    aria-valuenow="{{ $read_percentage }}" aria-valuemin="0"
                                                    aria-valuemax="100" style="width: {{ $read_percentage }}%;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- {{ $open_count_distinct }} / {{ $emails_count }} --}}
                        </td>

                        <td class="text-center">
                            @if (isset($project->created_at) && $project->created_at != '')
                                {{ $project->created_at->format('d/m/Y') ?? '' }}
                            @endif
                        </td>

                        <td class="text-center"> <a href="{{ asset('storage/excels/' . $project->document_url) }}" download
                                class="btn btn-success" href="" title="Descargar excel">
                                <i class="fas fa-file-download"></i> </td>
                        <td class="text-center"><a class="btn btn-primary"
                                href="{{ route('email.index', $project->project_id) }}"
                                title="Ver emails que contiene proyecto"><i class="fas fa-edit"></i></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('custom_footer')

    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/project_list.js') }}?v={{ config('app.version') }}"></script>
@endsection
