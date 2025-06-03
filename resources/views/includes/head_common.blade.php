<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Language" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="{{ URL::asset('' . DIR_IMG . '/favicon.ico') }}">
<meta name="viewport"
    content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />

<!-- Disable tap highlight on IE -->
<meta name="msapplication-tap-highlight" content="no">

<link href="{{ URL::asset('' . DIR_CSS . '/main.css') }}?v={{ config('app.version') }}" rel="stylesheet">
<link href="{{ URL::asset('' . DIR_CSS . '/custom.css') }}?v={{ config('app.version') }}" rel="stylesheet">



<!-- JQuery -->
<script type="text/javascript"
    src="{{ URL::asset('' . DIR_JS . '/jquery-3.4.1.min.js') }}?v={{ config('app.version') }}"></script>
<!-- JQuery ui -->
<script type="text/javascript" src="{{ URL::asset('' . DIR_JS . '/jquery-ui.min.js') }}?v={{ config('app.version') }}">
</script>
<!-- Metismenu -->
<script type="text/javascript" src="{{ URL::asset('' . DIR_JS . '/metismenu.js') }}?v={{ config('app.version') }}">
</script>


<!-- Bootstrap -->
<script type="text/javascript" src="{{ URL::asset('' . DIR_JS . '/popper.min.js') }}?v={{ config('app.version') }}">
</script>
<script type="text/javascript"
    src="{{ URL::asset('' . DIR_JS . '/bootstrap.min.js') }}?v={{ config('app.version') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">




<!-- DateRangePicker http://www.daterangepicker.com/#examples -->
<link href="{{ URL::asset('' . DIR_CSS . '/daterangepicker.css') }}?v={{ config('app.version') }}" rel="stylesheet">
<script type="text/javascript" src="{{ URL::asset('' . DIR_JS . '/moment.min.js') }}?v={{ config('app.version') }}">
</script>
<script type="text/javascript"
    src="{{ URL::asset('' . DIR_JS . '/daterangepicker.min.js') }}?v={{ config('app.version') }}"></script>


<!-- DatePicker  https://github.com/fengyuanchen/datepicker -->
<link href="{{ URL::asset('' . DIR_CSS . '/datepicker.min.css') }}?v={{ config('app.version') }}" rel="stylesheet">
<script type="text/javascript"
    src="{{ URL::asset('' . DIR_JS . '/datepicker.min.js') }}?v={{ config('app.version') }}"></script>
<script type="text/javascript"
    src="{{ URL::asset('' . DIR_JS . '/datepicker.es.min.js') }}?v={{ config('app.version') }}"></script>


<!-- JQ confirm -->
<link href="{{ URL::asset('' . DIR_CSS . '/jquery-confirm.min.css') }}?v={{ config('app.version') }}"
    rel="stylesheet">
<script type="text/javascript"
    src="{{ URL::asset('' . DIR_JS . '/jquery-confirm.min.js') }}?v={{ config('app.version') }}"></script>
<script type="text/javascript"
    src="{{ URL::asset('' . DIR_JS . '/jquery.validacion-formulario-bootstrap-3.5.js') }}?v={{ config('app.version') }}">
</script>
{{-- fontawesome --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Datatables -->
<link href="{{ URL::asset('' . DIR_CSS . '/dataTables.bootstrap4.min.css') }}?v={{ config('app.version') }}"
    rel="stylesheet">
<script type="text/javascript"
    src="{{ URL::asset('' . DIR_JS . '/jquery.dataTables.min.js') }}?v={{ config('app.version') }}"></script>
<script type="text/javascript"
    src="{{ URL::asset('' . DIR_JS . '/dataTables.bootstrap4.min.js') }}?v={{ config('app.version') }}"></script>
<script>
    var http_web_root = '{{ URL::asset('') }}';
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
