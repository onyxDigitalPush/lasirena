<style>
    /* Estilos para separadores de sección en sidebar
   - Altura basada en padding (devuelto al comportamiento previo)
   - Barra sólida desplazada a la derecha para cubrir el área de iconos
   - Color controlado por la variable CSS --sep-color (establecida inline)
 */
    .sidebar-section-separator {
        position: relative;
        display: block;

        padding: 3px 0;
        /* vuelve a la altura basada en padding */
        min-height: 18px;
        /* mantiene altura suficiente para iconos */
        border-radius: 0 4px 4px 0;
        transition: all 0.12s ease;
        background-color: transparent;
    }


    /* Barra sólida desplazada hacia la derecha (cubre área de iconos)
   Usa la variable --sep-color para el color, definida inline en cada separador
*/
    .sidebar-section-separator::before {
        content: '';
        position: absolute;
        top: 6px;
        bottom: 6px;
        width: 35px;
        background: var(--sep-color, #007bff);
        border-radius: 0 4px 4px 0;
        /* bordes ligeramente más redondeados para mayor grosor */
    }

    /* Iconos coloreados por sección */
    .icon-admin {
        color: #007bff !important;
    }

    .icon-proyectos {
        color: #28a745 !important;
    }

    .icon-proveedores {
        color: #fd7e14 !important;
    }

    .icon-analisis {
        color: #6f42c1 !important;
    }
</style>

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

                @php
                    // Normalizar tipos: puede venir como JSON string, array o value único
                    $__user_types = Auth::user()->type_user_multi ?? (Auth::user()->type_user ?? []);
                    if (!is_array($__user_types)) {
                        $tmp = json_decode($__user_types, true);
                        $__user_types = is_array($tmp) ? $tmp : [$__user_types];
                    }
                    $__types_str = array_map('strval', $__user_types);
                @endphp

                <li class="app-sidebar__heading">Menú</li>

                {{-- ============================================ --}}
                {{-- 🔵 ADMINISTRACIÓN GENERAL - Solo Admin (1)  --}}
                {{-- ============================================ --}}
                @if (in_array('1', $__types_str))
                    <!-- Separador visual azul -->
                    <li class="sidebar-section-separator" style="--sep-color: #007bff;"></li>

                    <li class="active">
                        <a href="{{ route('usuarios.index') }}" title="Control Usuarios" class="active">
                            <i class="metismenu-icon fa fa-user icon-admin"></i>
                        </a>
                    </li>

                    <!-- Separador final azul -->
                    <li class="sidebar-section-separator" style="--sep-color: #007bff;"></li>
                @endif

                {{-- ============================================ --}}
                {{-- 🟢 GESTIÓN DE PROYECTOS - Admin(1) y Gestor(3) --}}
                {{-- ============================================ --}}
                @if (in_array('1', $__types_str) || in_array('3', $__types_str))
                    <!-- Separador visual verde -->
                    <li class="sidebar-section-separator" style="--sep-color: #28a745;"></li>

                    <li class="active">
                        <a href="{{ route('project.index') }}" title="Ver proyectos" class="active">
                            <i class="metismenu-icon fa fa-home icon-proyectos"></i>
                        </a>
                    </li>

                    <li class="active">
                        <a href="{{ route('upload_excel.index') }}" title="Crear Proyecto" class="active">
                            <i class="metismenu-icon fas fa-plus icon-proyectos"></i>
                        </a>
                    </li>

                    <!-- Separador final verde -->
                    <li class="sidebar-section-separator" style="--sep-color: #28a745;"></li>
                @endif

                {{-- ============================================ --}}
                {{-- 🟠 GESTIÓN DE PROVEEDORES - Admin(1) y Compras(2) --}}
                {{-- ============================================ --}}
                @if (in_array('1', $__types_str) || in_array('2', $__types_str))
                    <!-- Separador visual naranja -->
                    <li class="sidebar-section-separator" style="--sep-color: #fd7e14;"></li>

                    <li class="active">
                        <a href="{{ route('proveedores.index') }}" title="Control Proveedores" class="active">
                            <i class="metismenu-icon fa fa-users icon-proveedores"></i>
                        </a>
                    </li>

                    <li class="active">
                        <a href="{{ route('material_kilo.index') }}" title="Lista Material Kilos" class="active">
                            <i class="metismenu-icon fa fa-truck icon-proveedores"></i>
                        </a>
                    </li>

                    <li class="active">
                        <a href="{{ route('material_kilo.total_kg_proveedor') }}" title="Total KG por Proveedor"
                            class="active">
                            <i class="metismenu-icon fa fa-bar-chart icon-proveedores"></i>
                        </a>
                    </li>

                    <li class="active">
                        <a href="{{ route('material_kilo.evaluacion_continua_proveedores') }}"
                            title="Evaluación Continua Proveedores" class="active">
                            <i class="metismenu-icon fa fa-line-chart icon-proveedores"></i>
                        </a>
                    </li>

                    <li class="active">
                        <a href="{{ route('material_kilo.historial_incidencias_devoluciones') }}"
                            title="Historial Incidencias y Devoluciones" class="active">
                            <i class="metismenu-icon fa fa-exclamation-triangle icon-proveedores"></i>
                        </a>
                    </li>



                    <!-- Separador final naranja -->
                    <li class="sidebar-section-separator" style="--sep-color: #fd7e14;"></li>
                @endif

                {{-- ============================================ --}}
                {{-- 🟣 ANÁLISIS DE TIENDAS - Admin(1) y Análisis(5) --}}
                {{-- ============================================ --}}
                @if (in_array('1', $__types_str) || in_array('5', $__types_str))
                    <!-- Separador visual morado -->
                    <li class="sidebar-section-separator" style="--sep-color: #6f42c1;"></li>

                    <li class="active">
                        <a href="{{ route('tiendas.index') }}" title="Control Tiendas" class="active">
                            <i class="metismenu-icon fa fa-building icon-analisis"></i>
                        </a>
                    </li>
                    <li class="active">
                        <a href="{{ route('evaluacion_analisis.historial_evaluaciones') }}"
                            title="Historial evaluaciones" class="active">
                            <i class="metismenu-icon fa fa-undo icon-proveedores"></i>
                        </a>
                    </li>

                    <li class="active">
                        <a href="{{ route('evaluacion_analisis.list') }}" title="Listado Evaluaciones" class="active">
                            <i class="metismenu-icon fa fa-flask icon-analisis"></i>
                        </a>
                    </li>

                    <li class="active">
                        <a href="{{ route('evaluacion_analisis.gestion') }}" title="Gestión de Análisis"
                            class="active">
                            <i class="metismenu-icon fa fa-chart-bar icon-analisis"></i>
                        </a>
                    </li>

                    <!-- Separador final morado -->
                    <li class="sidebar-section-separator" style="--sep-color: #6f42c1;"></li>
                @endif
            </ul>
        </div>
    </div>
</div>
