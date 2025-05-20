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
                <div class="row w-100 ml-0 pt-5">
                    <div class="col-sm-12 col-lg-4">
                    </div>
                    <div class="col-sm-12 col-lg-4 pt-3">
                        <div class="pt-4 card bg-white mt-5">
                            <form class="main_content pb-3 w-100" id="loginForm" method="POST" autocomplete="off"
                                action="{{ route('login') }}">
                                @csrf
                                <div class="card-header-title font-size-lg font-weight-normal float-left d-block w-100">
                                    <p class="card-title w-100 text-center font-size-lg text-primary">Acceso al
                                        backoffice de La Sirena</p>
                                    <p class="description w-100 text-center">Porfavor facilite las credenciales para
                                        poder acceder.</p>
                                </div>
                                <div class="card-body">
                                    <div>
                                        <label class="card-title">E-mail</label>
                                        <input class="form-control" type="text" name="email"
                                            data-validacion="obligatorio email" autocomplete="off">
                                    </div>
                                    <div class="mt-4 mb-2">
                                        <label class="card-title">Contraseña</label>
                                        <input class="form-control" type="password" name="password"
                                            data-validacion="obligatorio" autocomplete="off">
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12 offset-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember"
                                                    id="remember" {{ old('remember') ? 'checked' : '' }}>

                                                <label class="form-check-label" for="remember">
                                                    Mantenerme conectado
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6 text-left">
                                            {{-- @if (Route::has('password.request'))
                                                <a class="ml-2 mt-4 btn btn-link"
                                                    href="{{ route('password.request') }}">
                                                    Has olvidado la contraseña?
                                                </a>
                                            @endif --}}
                                        </div>
                                        <div class="col-6 text-right">
                                            <a class="ml-1 mt-4 btn btn-primary" title="Acceder" id="send_form"
                                                href="javascript:void(0);">ACCEDER</a>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-4">
                    </div>
                </div>
            </div>
        </main>
    </div>
    @include('includes.footer')
    <script>
        var form_login = $("#loginForm");
        form_login.validacion();

        $("#send_form").click(function(e) {

            e.preventDefault();
            if (!form_login.valida()) {
                return false;
            } else {
                $("#loginForm").submit();
            }
        });
    </script>
</body>

</html>
