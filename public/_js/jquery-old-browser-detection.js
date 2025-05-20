//Needs <!--[if lt IE 9]><div id="nvg_ie_compat"></div><![endif]--> on html page for correct detection
window.onload = (function(){
    var nvgIeCompat = function () {
        var nvg_cname = "_nvgiecompat";
     
        if ( document.getElementById( 'nvg_ie_compat' ) !== null )
        {
            if ( !checkCookie() )
                window.location.href = 'old-browser.html';
        }

        function setCookie( cname, cvalue) {
            document.cookie = cname + "=" + cvalue + "; ";
        }

        function getCookie( cname ) {
            var name = cname + "=";
            var ca = document.cookie.split( ';' );
            for ( var i = 0; i < ca.length; i++ ) {
                var c = ca[i];
                while ( c.charAt(0) == ' ' )
                    c = c.substring( 1 );
                if ( c.indexOf( name ) == 0 )
                    return c.substring( name.length, c.length );
            }
            return "";
        }

        function checkCookie() {
            var cookie = getCookie( nvg_cname );
            if ( cookie != "" ) {
                return true;
            } else {
                setCookie( nvg_cname, 1);
                return false;
            }
        }
        
    };
    nvgIeCompat();
})();