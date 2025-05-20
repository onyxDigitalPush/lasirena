<!doctype html>
<html>

<head>
    <title>@yield('app_name', config('app.name'))</title>
    <meta name="description" content="Area Privada la Sirena">
    @include('includes.head_common')
    @yield('custom_head')
</head>

<body>
    <div class="app-container app-theme-white body-tabs-shadow fixed-header fixed-sidebar fixed-footer closed-sidebar">

        @include('includes.header')
        <div class="app-main">
            @include('includes.sidebar')
            <div class="app-main__outer">
                <div class="app-main__inner">
                    <!-- PAGE TITLE -->
                    <div class="app-page-title">
                        @yield('title_content')
                    </div>
                    <!-- CONTENT BODY -->
                    <div class="row">
                        @yield('main_content')
                    </div>
                    <!-- END CONTENT BODY -->

                    @include('includes.footer')
                    @yield('custom_footer')
                </div>
            </div>
        </div>
    </div>

</body>

</html>
