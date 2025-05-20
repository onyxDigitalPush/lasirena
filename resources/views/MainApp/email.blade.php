@extends('layouts.app')

@section('app_name', config('app.name'))


@section('custom_head')
    <script type="text/javascript"
        src="{{ URL::asset('' . DIR_JS . '/main_app/email.js') }}?v={{ config('app.version') }}">
    </script>
@endsection


@section('title_content')

    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="fas fa-envelope icon-gradient bg-secondary"></i>
            </div>
            <div>Email
                <div class="page-title-subheading">
                    Visualizar datos del email
                </div>
            </div>
        </div>
        <div class="page-title-actions text-white">
            <input type="hidden" value="0" name="tab_orders" id="tab_orders">
            <a class="m-2 btn btn-primary" href="{{ route('email.index', $obj_email->project_id) }}"><i
                    class="fa fa-list mr-2"></i>Volver al Histórico de emails</a>
        </div>
    </div>

@endsection


@section('main_content')
    <div class="col-12 bg-white">
        <div class='mt-4 mb-4'></div>
        <div class="mb-4">
            <h3><strong>Productos</strong></h3>

        </div>
        {!! Form::open(['route' => ['email.send_redemption_mail'], 'method' => 'POST', 'id' => 'form_send_redemption_mail']) !!}
        @csrf
        <div class="row" id="send_redemption_data">
            <div class="position-relative form-group text-left col-sm-6 col-md-6 col-lg-4 col-xl-3">
                <label for="filterSearch" class="">Con copia a (separado por ; )</label>
                <input value="{{ $obj_project->ccs }}" name="ccs" id="ccs" type="text" class="form-control"
                    data-validacion="obligatorio email_multiple">
            </div>
        </div>
        <input type="hidden" name="str_sales" id="str_sales">
        <input type="hidden" value="{{ $obj_email->project_id }}" name="project_id">
        <input type="hidden" value="{{ $obj_email->email_id }}" name="email_id">
        </form>
        @php
            $have_redemption = 'false';
        @endphp
        <div class="row">
            <div class="col-sm-12 col-md-12 col-xl-12">
                <div class="table-responsive">
                    <table id="emailsTbl" style="width: 100%;"
                        class="table table-hover table-striped table-bordered dataTable dtr-inline border-secondary"
                        role="grid">
                        <thead>
                            <tr>
                                <th class="text-center">Orden</th>
                                <th class="text-center">Cód Proveedor</th>
                                <th class="text-center">Familia</th>
                                <th class="text-center">Nombre Proveedor</th>
                                <th class="text-center">Cód Producto</th>
                                <th class="text-center">Descripción Producto</th>
                                <th class="text-center">Dto</th>
                                <th class="text-center">Fecha Inicio</th>
                                <th class="text-center">Fecha Final</th>
                                <th class="text-center">Inicio Sap</th>
                                <th class="text-center">Fin Sap</th>
                                <th class="text-center">Ventas</th>
                                <th class="text-center">Enviar redención</th>
                            </tr>


                        </thead>
                        <tbody>
                            @foreach ($array_product as $product)
                                <tr class="text-center">
                                    <td>{{ $product->product_id }}</td>
                                    <td>{{ $product->provider_cod }}</td>
                                    <td>{{ $product->family }}</td>
                                    <td>{{ $product->provider_name }}</td>
                                    <td>{{ $product->product_cod }}</td>
                                    <td>{{ $product->product_description }}</td>
                                    <td>{{ number_format($product->dto, 2) }} %</td>
                                    <td>{{ $product->start_date->format('d/m/Y') }}</td>
                                    <td>{{ $product->end_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if (isset($product->start_sap) && $product->start_sap != '')
                                            {{ $product->start_sap->format('d/m/Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($product->end_sap) && $product->end_sap != '')
                                            {{ $product->end_sap->format('d/m/Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($product->redemption)
                                            <input type="number" id="sales_{{ $product->product_id }}"
                                                @if ($product->redempted == 1) readonly="true" value="{{ $product->sales }}"@else value="0" @endif
                                                class="sales_input form-control">
                                        @endif

                                    </td>
                                    <td>
                                        @if ($product->redemption && $product->redempted != 1)
                                            @php
                                                $have_redemption = 'true';
                                            @endphp
                                            <div class="radio_options">
                                                <label>

                                                    <input id="{{ $product->product_id }}" name="status"
                                                        class="redemption_check" type="checkbox" value="0">
                                                    <span></span>
                                                </label>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-12 text-right pb-4 pt-4 ">
                @if ($have_redemption)
                    <button type="button" id="confirm_button" disabled="" class="mt-4 btn btn-success"><i
                            class="fas fa-save pr-2"></i>
                        Enviar Redención de Emails</button>
                @endif
            </div>
        </div>
    </div>
    <script>
        var have_redemption = {{ $have_redemption }};
    </script>
@endsection

@section('custom_footer')

@endsection
