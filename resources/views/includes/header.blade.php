
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
                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    class="p-0 btn">
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
                                                            <div class="widget-subheading opacity-8">
                                                                Administrador
                                                            </div>
                                                        </div>
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
                            <div class="widget-subheading">
                                Administrador
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
