<?php

/**
 * Controller
 */
/**
 * @filename: notifications.cntrl.php
 * Location: _app/_controllers
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 * 	20181212 RBM Created
 */
require_once 'common.cntrl.inc.php';

switch ($_GET['cod'])
{

    case '001':
        $alert = 'Lo sentimos, has alcanzado el límite <br> de conexiones permitidas';
        $alert2 = 'Tu acceso quedará bloqueado de forma temporal por motivos de seguridad. <br> Por favor, vuelve a intentarlo en otro momento.';
        break;
    case '002':
        $alert = 'Lo sentimos, tu sesión de <br> usuario ha expirado';
        $alert2 = 'Por favor, vuelve a intentarlo de nuevo.';
        break;
    case '003':
        $alert = 'Tu cuenta ha sido bloqueado por motivos de seguridad';
        $alert2 = 'Por favor, para resolver la incidencia ponte en contacto con nosotros';
        break;
    case '004':
        $alert = 'Disculpa las molestias estamos actualizando la plataforma en breve estaremos operativos';
        $alert2 = 'Por favor, vuelve a intentarlo de nuevo en unos minutos.';
        break;
    case '005':
        $alert = 'Estamos resolviendo una incidencia técnica.';
        $alert2 = 'Por favor, vuelve en otro momento. <br><br> Disculpa las molestias.';
        break;
    case '006':
        $alert = 'Se ha detectado un acceso desde otro dispositivo.';
        $alert2 = 'Por motivos de seguridad esta sesión se ha finalizado.';
        break;
    case '007':
        $alert = 'Si estás registrado, recibirás en el correo electrónico indicado un email para activar tu cuenta.';
        $alert2 = 'Recuerda revisar la carpeta de correo no deseado por si el email va a parar allí.';
        break;
    case '008':
        $alert = 'Tu cuenta ha sido bloqueada por motivos de seguridad';
        $alert2 = 'Por favor, para resolver la incidencia ponte en contacto con nosotros';
        break;

    /**
      ########## LOS ERRORES PROPIOS DEBEN PONERSE CON 10X o 20X, etc nunca usar el 00X
     */
    case '100':
        $alert = 'Error guardar en el histórico';
        $alert2 = 'No hay referencia, subject, html o no se ha podido guardar';
        break;
    case '101':
        $alert = 'Error al recuperar el e-mail';
        $alert2 = 'No se ha podido localizar el e-mail o ha fallado la descarga';
        break;
    case '102':
        $alert = 'Error al insertar los destinatarios';
        $alert2 = 'No se han podido insertar los destinatarios';
        break;
    case '200':
        $alert = 'Se ha producido un error';
        $alert2 = 'No se ha podido realizar la baja correctamente.<br> Por favor, vuelve a intentarlo en otro momento.';
        break;
    case '201':
        $alert = 'Se ha producido un error';
        $alert2 = 'No se ha podido realizar la baja correctamente.<br> Por favor, vuelve a intentarlo en otro momento.';
        break;
    case '202':
        $alert = 'Se ha producido un error';
        $alert2 = 'No se ha recibido listado de direcciones.';
        break;
    case '300':
        $alert = 'Se ha producido un error';
        $alert2 = 'No se ha podido registrar correctamente.<br> Por favor, vuelve a intentarlo en otro momento.';
        break;
    case '203':
        $alert = 'Gracias por facilitarnos la información.';
        $alert2 = 'Ya nos confirmaste tu inscripción en el Programa con anterioridad. Si tienes alguna duda puedes ponerte en contacto con nosotros a través de <a href="https://www.nestle.es/profile/ContactaWeb?wid=763" title="Contactar">este formulario</a>.';
        break;
    case '204':
        $alert = 'Se ha producido un error';
        $alert2 = 'Los datos recibidos no son correctos. Si tienes alguna duda puedes ponerte en contacto con nosotros a través de <a href="https://www.nestle.es/profile/ContactaWeb?wid=763" title="Contactar">este formulario</a>.';
        break;
    default:
        throw new ErrorException('Se ha producido un error inesperado Notif.' . (int) $_GET['cod'], 1);
        exit();
        break;
}


include VIEWS_DIR . '/notifications.view.php';
exit();
?>