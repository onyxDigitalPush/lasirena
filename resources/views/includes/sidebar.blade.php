<div class="app-sidebar sidebar-shadow">
    <div class="app-header__logo">
        <div class="logo-src"></div>
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">

                <li class="app-sidebar__heading">Menú</li>
                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '3')
                    <li class="active">
                        <a href="{{ route('project.index') }}" title="Ver proyectos" class="active">
                            <i class="metismenu-icon fa fa-home"></i>
                            {{-- <i class="metismenu-icon pe-7s-home"></i> --}}
                            Proyectos
                        </a>
                    </li>
                @endif
                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '3')
                    <li class="active">
                        <a href="{{ route('upload_excel.index') }}" title="Crear Proyecto" class="active">

                            {{-- <i class="metismenu-icon pe-7s-plus"></i> --}}
                            <i class="metismenu-icon fas fa-plus"></i>
                            Crear Proyecto
                        </a>
                    </li>
                @endif
                @if (Auth::user()->type_user == '1')
                    <li class="active">
                        <a href="{{ route('usuarios.index') }}" title="Control Usuarios" class="active">
                            <i class="metismenu-icon fa fa-user"></i>
                        </a>
                    </li>
                @endif
                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '2')
                    <li class="active">
                        <a href="{{ route('proveedores.index') }}" title="Control Proveedores" class="active">
                            <i class="metismenu-icon fa fa-users"></i>
                            Proveedores
                        </a>
                    </li>
                @endif
                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '2')
                    <li class="active">
                        <a href="{{ route('tiendas.index') }}" title="Control Tiendas" class="active">
                            <i class="metismenu-icon fa fa-building"></i>
                            Tiendas
                        </a>
                    </li>
                    <li class="active">
                        <a href="{{ route('material_kilo.index') }}" title="Lista Material Kilos" class="active">
                            <i class="metismenu-icon fa fa-truck"></i>
                            Material Kilos
                        </a>
                    </li>
                @endif
                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '2')
                    <li class="active">
                        <a href="{{ route('material_kilo.total_kg_proveedor') }}" title="Total KG por Proveedor" class="active">
                            <i class="metismenu-icon fa fa-bar-chart"></i>
                            Total KG por Proveedor
                        </a>
                    </li>
                @endif
                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '2')
                    <li class="active">
                        <a href="{{ route('material_kilo.evaluacion_continua_proveedores') }}" title="Evaluación Continua Proveedores" class="active">
                            <i class="metismenu-icon fa fa-line-chart"></i>
                            Evaluación Continua
                        </a>
                    </li>
                @endif
                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '2')
                    <li class="active">
                        <a href="{{ route('material_kilo.historial_incidencias_devoluciones') }}" title="Historial Incidencias y Devoluciones" class="active">
                            <i class="metismenu-icon fa fa-exclamation-triangle"></i>
                            Historial Incidencias
                        </a>
                    </li>
                @endif
                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '2')
                    <li class="active">
                        <a href="{{ route('evaluacion_analisis.historial_evaluaciones') }}" title="Historial evaluaciones" class="active">
                            <i class="metismenu-icon fa fa-undo"></i>
                            Evaluación de análisis externos producto
                        </a>
                    </li>
                @endif

                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '2')
                    <li class="active">
                        <a href="{{ route('evaluacion_analisis.list') }}" title="Listado Evaluaciones" class="active">
                            <i class="metismenu-icon fa fa-flask"></i>
                            Listado Evaluaciones
                        </a>
                    </li>
                @endif

                @if (Auth::user()->type_user == '1' || Auth::user()->type_user == '2')
                    <li class="active">
                        <a href="{{ route('evaluacion_analisis.gestion') }}" title="Gestión de Análisis" class="active">
                            <i class="metismenu-icon fa fa-chart-bar"></i>
                            Gestión de Análisis
                        </a>
                    </li>
                @endif

            </ul>
        </div>
    </div>
</div>
