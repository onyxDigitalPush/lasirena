function __afpcInicializaComponente()
{
    if (typeof __afpc_conf != "undefined")
    {
        if (typeof jQuery == 'undefined')
        {
            var jq_script = document.createElement('script');
            jq_script.type = 'text/javascript';
            jq_script.src = ruta_widget + "jquery-3.2.1.min.js";

            var head_html = document.getElementsByTagName('head')[0];
            var flag_script_cargado = false;

            jq_script.onload = jq_script.onreadystatechange = function ()
            {
                if (!flag_script_cargado && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete'))
                {
                    flag_script_cargado = true;
                    jq_script.onload = jq_script.onreadystatechange = null;
                    $(document).ready(function () {
                        __afpcInicializaAvisoCookies();
                    });
                }
            };

            head_html.appendChild(jq_script);
        }
        else
        {
            $(document).ready(function () {
                __afpcInicializaAvisoCookies();
            });
        }
    }
}

function __afpcInicializaAvisoCookies()
{
    var URL = "//" + window.location.host + window.location.pathname;

    if (URL != __afpc_conf.URL_politica_cookies)
    {
        $("head").append($("<link rel='stylesheet' type='text/css' href='" + ruta_widget + "afpc.css'>"));
        $("head").append($("<script type='text/javascript' src='" + ruta_widget + "jquery.cookie.js'></script>"));
        if (typeof CryptoJS == "undefined")
        {
            $("head").append($("<script type='text/javascript' src='" + ruta_widget + "sha1.js'></script>"));
        }

        var html_aviso = '<p id="__afpc" class="__afpc_main_container">Utilizamos cookies de terceros para mejorar tu accesibilidad, personalizar y analizar tu navegaci&oacute;n. Si contin&uacute;as navegando, consideramos que aceptas su uso. Puedes cambiar la configuraci&oacute;n u obtener m&aacute;s informaci&oacute;n en nuestra <a class="__afpc_policy_link" title="Pol&iacute;tica sobre Cookies" href="' + window.location.protocol + __afpc_conf.URL_politica_cookies + '">Pol&iacute;tica sobre Cookies</a>.</p>';

        if (!($.cookie("acepta_cookies_" + CryptoJS.SHA1(__afpc_conf.URL_website + 'cookies')) == CryptoJS.SHA1(__afpc_conf.URL_website)))
        {
            $("body").prepend(html_aviso);

            $(document).click(function (e) {
                if ($(e.target).closest("#__afpc").length > 0 || $(e.target).closest("#__afpc_pie").length > 0)
                {
                    //$.removeCookie('acepta_cookies');
                }
                else
                {
                    $.cookie('acepta_cookies_' + CryptoJS.SHA1(__afpc_conf.URL_website + 'cookies'), CryptoJS.SHA1(__afpc_conf.URL_website), {expires: 180, path: '/'});
                    $("#__afpc").remove();
                }
            });
        }
    }
}

var ruta_widget = window.location.protocol + "//" + window.location.host + "/_widgets/_aviso_cookies/";
__afpcInicializaComponente();