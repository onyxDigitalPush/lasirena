<!doctype html>
<html>

<head>
    <title>{{ config('app.name') }}</title>
    <meta name="description" content="Area Privada la Sirena">
    @include('includes.head_common')
</head>

<body>
    <div class="app-container app-theme-white body-tabs-shadow fixed-header fixed-sidebar fixed-footer ">

        @include('includes.header')
        <div class="app-main">
            @include('includes.sidebar')
        </div>
    </div>
    @include('includes.footer')
</body>

</html>
