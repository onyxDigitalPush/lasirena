<!doctype html>


<head>
    <title>{{ config('app.name') }}</title>
    <meta name="description" content="">
    @include('includes.head_common')
</head>

<body>
    <div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header closed-sidebar h-100">
        <div class="app-header bg-white text-dark">
            <img src="{{ URL::asset('' . DIR_IMG . '/logo-header-la-sirena.svg') }}" class="logo">
        </div>
        <main>
            <div class="app-main">
                <div class="row w-100 ml-0">
                    <div class="col-sm-12 col-lg-4">
                    </div>
                    <div class="col-sm-12 col-lg-4">
                        <div class="pt-4 card bg-white mt-5">
                            <div class="main_content w-100">
                                <div class="card-header-title font-size-lg font-weight-normal float-left d-block w-100">
                                    <div class="session_container">
                                        <p class="card-title w-100 text-center font-size-lg text-danger"><i
                                                class="fas fa-exclamation-triangle fa-2x"></i></span></p>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="error_title w-100 text-center font-size-lg">@yield('title')</p>
                                    <p class="error_text w-100 text-center font-size-lg">@yield('text')</p>
                                    <div class="button_container text-center">
                                        <a class="btn btn-primary mt-3" title="Aceptar"
                                            href="{{ HTTPS_WEB_ROOT }}">ACEPTAR</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-4">
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
