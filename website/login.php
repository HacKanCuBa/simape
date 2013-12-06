<?php

/*****************************************************************************
 *  SiMaPe
 *  Sistema Integrado de Manejo de Personal
 *  Copyright (C) <2013>  <Ivan Ariel Barrera Oro>
 *  ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *****************************************************************************/

/*
 * ToDo:
 *  - Manejo de errores
 *  - limpiar html
 *  - opcional loguearse con email, legajo, dni o algun otro campo unico
 *   (si algo paso con el nombre de usuario)
 */
if (!defined('CONFIG')) { require_once 'loadconfig.php'; }

// Iniciar o continuar sesion
session_do();

// Cerrar cualquier sesion que estuviera abierta
session_unset_sessionkey();

// Instanciar un token del formulario
$form_token = form_token_get_new();

// Sanitizar primero...
$login_user = username_format(post_get_frmText());
$login_password = post_get_frmPwd('Login');
$form_token_post = post_get_frmtkn();
//$form_token_session = session_get_frmtkn();
//$fingerprint_token_session = session_get_fingerprinttkn();
// = err_get_errt();

 /*// DBG
echo "<br /><br /><br /><br /><br /><br /><br /> "
        . "Usr: " . post_get_frmText(). "<br />"
        . "Pwd: " . post_get_frmPwd() . "<br />"
        . "ErrT: " . err_get_errt() . "<br />"
        . "TknP: ". post_get_frmtkn() . "<br />"
        . "TknS: " . session_get_frmtkn() . "<br />"
        . "TknF: $form_token<br />"
        . "FingS: " . session_get_fingerprinttkn() . "<br />"
        . "FingC: " . fingerprint_token_get() . "<br />\n"
        . "SessK: " . session_get_sessionkey() . "<br />"
        . "SessName: " . session_name() . "<br \>"
        . "SessStatus: " . session_status();
//*/

// Validar datos antes de continuar
if ((fingerprint_token_validate() === FALSE) || 
        (form_token_validate($form_token_post) === FALSE)) {
    // Fingerprint o form token invalido
    // Hay que testear con === FALSE, porque en esta página
    // puede que aún no estén creados y sean NULL, lo que no 
    // implica un error de autenticacion
    
    // Destruir la sesion actual
    session_terminate();
    // Crear una nueva
    session_do();
    session_set_errt($err_authfail);
    //$redirect = LOC_AUTHFAILED;
}
elseif (!empty(post_get_frmBtn()) 
        && (form_token_validate($form_token_post) === TRUE)
        && (fingerprint_token_validate() === TRUE)) {
    // Verificar User y Pass
    // Hago la verificacion siempre que el usuario haya ingresado algun dato,
    // aún si los mismos fueran intento de injection (para la validacion
    // uso los datos sanitizados).
    // De esta forma, prevengo timing oracle.
    
    $password_db = db_auto_get_password($login_user);
    
    if(password_validate($login_password, $password_db)) {
        // Login OK
        $sessionkey = sessionkey_get_new($login_user);
        session_set_sessionkey($sessionkey);
        session_set_username($login_user);;
        $redirect = LOC_NAV;
        $params = "pagetkn=" . page_token_get_new();
    }
    else {
        // Enviar mensaje user pass incorrecto       
        session_set_errt($err_authfail);
    }
}

// Crear tokens si no existen
if (empty(session_get_fingerprinttkn())) {
    // Almacenar token de identificacion del usario
    session_set_fingerprinttkn(fingerprint_token_get());
}

session_set_frmtkn(form_token_get_formtkn($form_token));

if (!empty($redirect)) {
    page_goto($redirect, $params);
    exit();
}

/* // DBG
echo "<br /><br /><br /><br /><br /><br /><br /> "
        . "Usr: " . post_get_loginusr() . "<br />"
        . "Pwd: " . post_get_loginpwd() . "<br />"
        . "ErrT: " . err_get_errt() . "<br />"
        . "TknP: ". post_get_frmtkn() . "<br />"
        . "TknS: " . session_get_frmtkn() . "<br />"
        . "TknF: $form_token<br />"
        . "FingS: " . session_get_fingerprinttkn() . "<br />"
        . "FingC: " . fingerprint_token_get() . "<br />\n"
        . "SessK: " . session_get_sessionkey() . "<br />"
        . "SessName: " . session_name() . "<br \>"
        . "SessStatus: " . session_status();
//*/
?>

<?php echo page_get_head('SiMaPe - Iniciar Sesi&oacute;n'); ?>
<?php echo "\n\t<style type='text/css'>\n\t.data { margin-left: auto; }"
            . "\n\t</style>"; ?>
<?php echo page_get_body(); ?>
<?php echo page_get_header(); ?>
<?php echo page_get_header_close(); ?>
<?php echo page_get_main(); ?>

    <div style="text-align: center;"> 
        <h2 style="text-align: center;">Sistema Integrado de Manejo de Personal</h2>
        <form style="text-align: center; margin: 0 auto; width: 100%;" 
              name="loginform" id="loginform" method="post">
            
            <!-- <fieldset> --> 
            <address>Por favor, identif&iacute;quese para continuar</address><br /> 
            <?php 
                  if (!empty(err_get_errt())) 
                  {
                      echo("<address class='fadeout' style='color:red; text-align: center;'>" . err_get_errt() . "</address>\n"); 
                      err_unset_errt();
                  }
                  else
                  {

                      echo("<br />\n");
                  }
            ?>
            <table style=" text-align: left; margin: auto; with: auto;" >
              <tbody>
                <tr>
                  <td style="text-align: left;"><br />
                    <span style="font-weight: bold;">Nombre de usuario:</span>
                  </td>
                  <td style="text-align: left;"><br />
                    <input name="frm_txt" 
                           title="Ingrese el nombre de usuario"
                           maxlength="<?php echo constant('USRNAME_MAXLEN'); ?>" 
                           type="text" autofocus required /> 
                  </td>
                </tr>
                <tr>
                  <td style="text-align: left;"><br />
                    <span style="font-weight: bold;">Contrase&ntilde;a:</span>
                  </td>
                  <td style="text-align: left;"><br />
                    <input name="frm_pwdLogin" 
                           title="Ingrese la constrase&ntilde;a"
                           type="password" 
                           maxlength="<?php echo constant('PWD_MAXLEN'); ?>" 
                           required />
                  </td>
                </tr>
                <tr>
                  <td colspan="2" style="text-align: center;"><br />
                      <input name="frm_btn" value="Iniciar sesi&oacute;n" 
                             type="submit" /> 
                      <input value="Cancelar"
                             title="Volver a la p&aacute;gina inicial" 
                             type="button" 
                             onClick="window.location = '<?php echo constant('WEB_ROOT'); ?>'" />
                  </td>
                </tr>
              </tbody>
            </table>
            <input type="hidden" name="form_token" 
                   value="<?php echo form_token_get_randtkn($form_token); ?>" />
           <!-- </fieldset> -->
       </form>
    </div>
<?php echo page_get_main_close(); ?>
<?php echo page_get_footer(); ?>