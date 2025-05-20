$( document ).ready( function () {
    setTimeout( checkSession, 30000 );
} );

function checkSession()
{
    $.ajax( {
        type: 'POST',
        async: true,
        data: 'check_session=1',
        dataType: 'json',
        success: function ( result ) {
            if ( result === 'OK' )
            {
                setTimeout( checkSession, 30000 );
            }
            else
            {
                window.location.replace( 'notificacion.html?cod=' + result );
            }
        }
    } );
}
