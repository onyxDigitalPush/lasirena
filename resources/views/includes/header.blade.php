<div class="app-header bg-white text-dark header-shadow">
    <div class="app-header__logo">
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
    <div class="app-header__content">
        <?php /* TODO: Not Used */ ?>
        <img src="{{ URL::asset('' . DIR_IMG . '/logo-header-la-sirena.svg') }}" class="logo">

        <?php /* */ ?>

        <div class="app-header-right">
            <?php /* TODO: Not Used */ ?>
            <div class="header-dots">

            </div>
            <?php /* */ ?>
            <div class="header-btn-lg pr-0">
                <div class="widget-content p-0">
                    <div class="widget-content-wrapper">
                        <div class="widget-content-left">
                            <div class="btn-group">
                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                    <img width="42" class="rounded-circle"
                                        src="{{ URL::asset('' . DIR_IMG . '/avatars/1.jpg') }}" alt="">
                                    <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                </a>
                                <div tabindex="-1" role="menu" aria-hidden="true"
                                    class="rm-pointers dropdown-menu-lg dropdown-menu dropdown-menu-right">
                                    <div class="dropdown-menu-header">
                                        <div class="dropdown-menu-header-inner bg-info">
                                            <div class="menu-header-image opacity-2"
                                                style="background-image: url('{{ URL::asset('' . DIR_IMG . '/dropdown-header/city3.jpg') }}');">
                                            </div>
                                            <div class="menu-header-content text-left">
                                                <div class="widget-content p-0">
                                                    <div class="widget-content-wrapper">
                                                        <div class="widget-content-left mr-3">
                                                            <img width="42" class="rounded-circle"
                                                                src="{{ URL::asset('' . DIR_IMG . '/avatars/1.jpg') }}"
                                                                alt="">
                                                        </div>
                                                        <div class="widget-content-left">
                                                            <div class="widget-heading">
                                                                {{ Auth::user()->name }}
                                                            </div>
                                                            @php
                                                                // Normalize type_user_multi which can be a JSON string, comma separated string, integer or an array
                                                                $typeRaw = Auth::user()->type_user_multi;
                                                                $types = [];
                                                                if (is_array($typeRaw)) {
                                                                    $types = $typeRaw;
                                                                } elseif (is_numeric($typeRaw)) {
                                                                    $types = [(int) $typeRaw];
                                                                } else {
                                                                    // could be JSON "["1","2"]" or comma separated "1,2" or strings with quotes
                                                                    // try json decode first
                                                                    $decoded = json_decode($typeRaw, true);
                                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                                        $types = $decoded;
                                                                    } else {
                                                                        // remove spaces and double quotes then split by comma
                                                                        $clean = str_replace('"', '', $typeRaw);
                                                                        $clean = str_replace('"', '', $clean);
                                                                        $clean = str_replace(' ', '', $clean);
                                                                        if ($clean === '') {
                                                                            $types = [];
                                                                        } else {
                                                                            $types = explode(',', $clean);
                                                                        }
                                                                    }
                                                                }

                                                                // normalize every item to int
                                                                $types = array_filter(array_map(function ($v) {
                                                                    if (is_numeric($v)) return (int) $v;
                                                                    // if it's a quoted string like '"1"' try to strip quotes
                                                                    $v = trim($v, "\"'");
                                                                    return is_numeric($v) ? (int) $v : null;
                                                                }, $types));

                                                                $roleNamesMap = [
                                                                    1 => 'Administrador',
                                                                    2 => 'Proveedores',
                                                                    3 => 'Proyectos',
                                                                    4 => 'Usuario',
                                                                    5 => 'Analisis Tiendas',
                                                                ];

                                                                $roleNames = array_values(array_map(function ($id) use ($roleNamesMap) {
                                                                    return $roleNamesMap[$id] ?? (string) $id;
                                                                }, $types));

                                                                $display = '';
                                                                $title = '';
                                                                if (count($roleNames) === 0) {
                                                                    $display = '—';
                                                                } elseif (count($roleNames) === 1) {
                                                                    $display = $roleNames[0];
                                                                } else {
                                                                    // show first two then +n
                                                                    $first = array_slice($roleNames, 0, 2);
                                                                    $remaining = count($roleNames) - count($first);
                                                                    $display = implode(', ', $first) . ($remaining > 0 ? ' +' . $remaining : '');
                                                                }
                                                                $title = implode(', ', $roleNames);
                                                            @endphp
                                                            <div class="widget-subheading opacity-8" title="{{ $title }}">
                                                                {{ $display }}
                                                            </div>

                                                        </div>

                                                        {{-- boton cambiar contraseña que abre modal --}}
                                                        <div class="widget-content-right mr-2" style="width: 60px">
                                                            <a href="#"
                                                                class="btn-pill btn-shadow btn-shine btn btn-focus"
                                                                data-toggle="modal" data-target="#changePasswordModal"
                                                                data-user-id="{{ Auth::user()->id }}">
                                                                Cambiar Contraseña
                                                            </a>
                                                        </div>
                                                        {{-- fin boton cambiar contraseña que abre modal --}}
                                                        <div class="widget-content-right mr-2">
                                                            <a href="{{ url('/logout') }}"
                                                                class="btn-pill btn-shadow btn-shine btn btn-focus">
                                                                Salir </a>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <div class="widget-content-left  ml-3 header-user-info">
                            <div class="widget-heading">
                                {{ Auth::user()->name }}
                            </div>
                            @php
                                // Reuse the same logic for the compact role display in header
                                $typeRaw = Auth::user()->type_user_multi;
                                $types = [];
                                if (is_array($typeRaw)) {
                                    $types = $typeRaw;
                                } elseif (is_numeric($typeRaw)) {
                                    $types = [(int) $typeRaw];
                                } else {
                                    $decoded = json_decode($typeRaw, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $types = $decoded;
                                    } else {
                                        $clean = str_replace('"', '', $typeRaw);
                                        $clean = str_replace(' ', '', $clean);
                                        if ($clean === '') {
                                            $types = [];
                                        } else {
                                            $types = explode(',', $clean);
                                        }
                                    }
                                }
                                $types = array_filter(array_map(function ($v) {
                                    if (is_numeric($v)) return (int) $v;
                                    $v = trim($v, "\"'");
                                    return is_numeric($v) ? (int) $v : null;
                                }, $types));
                                $roleNamesMap = [
                                    1 => 'Administrador',
                                    2 => 'Proveedores',
                                    3 => 'Proyectos',
                                    4 => 'Usuario',
                                    5 => 'Analisis Tiendas',
                                ];
                                $roleNames = array_values(array_map(function ($id) use ($roleNamesMap) {
                                    return $roleNamesMap[$id] ?? (string) $id;
                                }, $types));
                                if (count($roleNames) === 0) {
                                    $display = '—';
                                } elseif (count($roleNames) === 1) {
                                    $display = $roleNames[0];
                                } else {
                                    $first = array_slice($roleNames, 0, 2);
                                    $remaining = count($roleNames) - count($first);
                                    $display = implode(', ', $first) . ($remaining > 0 ? ' +' . $remaining : '');
                                }
                                $title = implode(', ', $roleNames);
                            @endphp
                            <div class="widget-subheading opacity-8" title="{{ $title }}">
                                {{ $display }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- cambio de contraseña --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordLabel">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('usuarios.cambiar_contrasena') }}">
            @csrf
            <input type="hidden" name="user_id" id="modalUserId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Contraseña</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password_confirmation">Confirmar Contraseña</label>
                        <input type="password" class="form-control" name="new_password_confirmation" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $('#changePasswordModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var userId = button.data('user-id');
        $('#modalUserId').val(userId);
    });
</script>
