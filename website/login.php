<?php

/*****************************************************************************
 *  Este archivo forma parte de SiMaPe
 *  Sistema Integrado de Manejo de Personal
 *  Copyright (C) <2013>  <Ivan Ariel Barrera Oro>
 *  ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 *  SiMaPe is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  SiMaPe is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *****************************************************************************/

/**
 * Realiza el login y la recuperación de la contraseña
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.36
 */

/*
 * ToDo:
 *  - loguearse con email, legajo, dni o algun otro campo unico
 *  - reestablecer contraseña
 *   
 */

require_once 'load.php';

/**
 * Nombre de indice de _SESSION donde se almacena la cuenta de reintentos de
 * inicio de sesión.
 */
const LOGIN_SESSINDEX_RETRY_COUNT = 'RETRY_COUNT';

/**
 * Valor que indica cantidad máxima de reintentos de login sin captcha.
 */
const LOGIN_RETRY_MAX = 2;

/**
 * Texto de error que se mostrará cuando el captcha no sea superado.
 */
const LOGIN_ERR_CAPTCHA = 'La operaci&oacute;n de verificaci&oacute;n no ha sido resuelta correctamente';

/**
 * Nombre de indice de _SESSION donde se almacena el valor del captcha.
 */
const LOGIN_SESSINDEX_CAPTCHA = 'CAPTCHA';

/**
 * Texto de error que se mostrará el token de restablecimiento de pwd sea incorrecto.
 */
const LOGIN_ERR_RESTORETKN = '&iexcl;El token de restablecimiento de contrase&ntilde;a no es v&aacute;lido!';

/**
 * Constantes que manejan lo que será mostrado al usuario.
 */
const LOGIN_DISPLAY_DEFAULT = 0;
const LOGIN_DISPLAY_PWDRESTORE_FORM = 1;
const LOGIN_DISPLAY_PWDRESTORE_ERROR = 2;
const LOGIN_DISPLAY_PWDRESTORE_OK = 3;
const LOGIN_DISPLAY_EMAIL_SENT = 4;
const LOGIN_DISPLAY_EMAIL_NOTSENT = 5;
const LOGIN_DISPLAY_EMAIL_NOTFOUND = 6;
const LOGIN_DISPLAY_NEWPWD_FORM = 7;
const LOGIN_DISPLAY_NEWPWD_OK = 8;
const LOGIN_DISPLAY_NEWPWD_ERROR = 9;
const LOGIN_DISPLAY_LOGGEDOUT = 10;

/**
 * Indica qué se mostrará al usuario en la página.
 * @var string
 */
$display = LOGIN_DISPLAY_DEFAULT;

// Si SSL está habilitado pero el usuario cargó la página de manera insegura,
// recarga por SSL.
force_connect(FORCE_CONNECT_SSL);

// Inicializaciones
// Iniciar o continuar sesion
$session = new Session;
$formToken = new FormToken;

// Recuperar el nombre de usuario
$session->useSystemPassword();
$username = trim(Sanitizar::glPOST('frm_txtLogin')) ?: 
                $session->retrieveEnc(SMP_SESSINDEX_USERNAME);

// TODO hay un error en esta funcion! si el nombre de usuario es valido, se cuelga todo y no se ve ningun error :S
// error en metodo rtreive_fromDB
$usuario = new Usuario($username);

if (!empty(Sanitizar::glPOST('frm_btnLogin'))) {
    //$start = time();
    // Pruebo captcha primero
    // si aun no hay captcha, ambos seran equivalentes (NULL y STRING NULL)
    if (Session::retrieve(LOGIN_SESSINDEX_CAPTCHA) == Sanitizar::glPOST('frm_txtCaptcha'))
    {
        // captcha OK
        $usuario->setPassword(Sanitizar::glPOST('frm_pwdLogin'), FALSE);
        
        $formToken->prepare_to_auth(Sanitizar::glPOST(SMP_SESSINDEX_FORM_TOKEN), 
                            $session->retrieve(SMP_SESSINDEX_FORM_RANDOMTOKEN), 
                            $session->retrieve(SMP_SESSINDEX_FORM_TIMESTAMP));
        // Ejecuto la autenticación de la contraseña aún si el form
        // token no valida, para evitar Timing Oracle.
        if($usuario->authenticatePassword() 
           && $formToken->authenticateToken()
        ) {
            // Login OK 
            if($usuario->sesionIniciar()) {
                // Sesion iniciada, ir a la pagina de inicio del usuario
                $nav = defined('SMP_HOME') ? SMP_HOME : 'usr/index.php';
            } else {
                // Falló
                Session::store(SMP_SESSINDEX_NOTIF_ERR, 'Ha ocurrido un error '
                        . 'inesperado.  Vuelva a iniciar sesi&oacute;n o contacte con '
                        . 'un administrador');
                $display = LOGIN_DISPLAY_DEFAULT;
            }
            
            // Limpieza
            Session::remove(LOGIN_SESSINDEX_CAPTCHA);
            Session::remove(LOGIN_SESSINDEX_RETRY_COUNT);
            Session::remove(SMP_SESSINDEX_FORM_RANDOMTOKEN);
            Session::remove(SMP_SESSINDEX_FORM_TIMESTAMP);
        } else {
            // Enviar mensaje user pass incorrecto       
            Session::store(SMP_SESSINDEX_NOTIF_ERR, SMP_ERR_AUTHFAIL);
        }  
    } else {
        // Enviar mensaje captcha incorrecto       
        Session::store(SMP_SESSINDEX_NOTIF_ERR, LOGIN_ERR_CAPTCHA);
    }
    $login_atempt = TRUE;
    $display = LOGIN_DISPLAY_DEFAULT;
    //$end = time();
} elseif (!empty(Sanitizar::glPOST('frm_btnCancelLogin'))) {
    // Volver a la pág inicial
    $nav = SMP_WEB_ROOT;
} elseif (!empty(Sanitizar::glPOST('frm_btnCancelRestore'))) {
    // Cargar Login normal
    $nav = SMP_LOGIN;
} elseif (!empty(Sanitizar::glPOST('frm_btnForget'))) {
    // Cargar form de restablecimiento de contraseña
    $display = LOGIN_DISPLAY_PWDRESTORE_FORM;
} elseif (!empty(Sanitizar::glPOST('frm_btnRestore'))) {
    $usuario->retrieve_fromDB_Empleado();
    if (empty($usuario->getEmail())) {
        $display =  LOGIN_DISPLAY_EMAIL_NOTFOUND;
    } else {   
        if($usuario->passwordRestore()) {
            $display = LOGIN_DISPLAY_EMAIL_SENT;
        } else {
            $display = LOGIN_DISPLAY_EMAIL_NOTSENT;
        }
    }   
} elseif (Sanitizar::glGET(SMP_NAV_ACTION) == SMP_RESTOREPWD) {       
    $usuario->setToken(Sanitizar::glGET('passRestoreToken'));
    if ($usuario->authenticatePasswordRestore()) {
        // Token válido
        // mostrar formulario
        $display = LOGIN_DISPLAY_NEWPWD_FORM;
        if (!empty(Sanitizar::glPOST('frm_btnNewPwd'))) {
            // guardar nueva contraseña      
            $formToken->prepare_to_auth(Sanitizar::glPOST(SMP_SESSINDEX_FORM_TOKEN), 
                            Session::retrieve(SMP_SESSINDEX_FORM_RANDOMTOKEN), 
                            Session::retrieve(SMP_SESSINDEX_FORM_TIMESTAMP));
            if ($formToken->authenticateToken()) {
                // Pruebo captcha primero
                // si aun no hay captcha, ambos seran equivalentes (NULL y STRING NULL)
                if (Session::retrieve(LOGIN_SESSINDEX_CAPTCHA) == Sanitizar::glPOST('frm_txtCaptcha'))
                {
                    // captcha OK
                    $newpasswd = array_map('trim', Sanitizar::glPOST('frm_pwdLogin'));
                    if ($newpasswd[0] === $newpasswd[1]) {
                        $usuario->retrieve_fromDB();
                        if ($usuario->setPassword($newpasswd[0], SMP_PASSWORD_REQUIRESTRONG)) {
                            $display = LOGIN_DISPLAY_NEWPWD_ERROR;
                            if ($usuario->encryptPassword()) {
                                if ($usuario->store_inDB()) {
                                    // nueva contraseña establecida
                                    // borrar tokens de restablecimiento
                                    $usuario->remove_fromDB_PwdRestore();     
                                    $display = LOGIN_DISPLAY_NEWPWD_OK;
                                }
                            }
                        } else {
                            Session::store(SMP_SESSINDEX_NOTIF_ERR, 
                                "La contrase&ntilde;a ingresada no cumple con los "
                                . "requisitos m&iacute;nimos de seguridad "
                                . "establecidos, por lo que no puede ser aceptada");
                        }
                    } else {
                        Session::store(SMP_SESSINDEX_NOTIF_ERR, 
                                "Las contrase&ntilde;as ingresadas no coinciden");
                    }
                } else {
                    // captcha err
                    Session::store(SMP_SESSINDEX_NOTIF_ERR, LOGIN_ERR_CAPTCHA);
                }
            }
        }
    } else {
        // Token inválido
        $display = LOGIN_DISPLAY_DEFAULT;
        Session::store(SMP_SESSINDEX_NOTIF_ERR, LOGIN_ERR_RESTORETKN);
    }
} elseif (Sanitizar::glGET(SMP_LOGOUT)) {
    $display = LOGIN_DISPLAY_LOGGEDOUT;
}

isset($nav) ? Page::nav($nav) : NULL;

if (isset($login_atempt)) {
    // Almacenar cant de reintentos de login
    Session::store(LOGIN_SESSINDEX_RETRY_COUNT, 
        Session::retrieve(LOGIN_SESSINDEX_RETRY_COUNT) + 1);
}

// Token de formulario
$formToken->generate();
Session::store(SMP_SESSINDEX_FORM_RANDOMTOKEN, $formToken->getRandomToken());
Session::store(SMP_SESSINDEX_FORM_TIMESTAMP, $formToken->getTimestamp());
// -- --
//
// Mostrar página
Page::printHead('SiMaPe | Iniciar Sesi&oacute;n', ['main', 'input', 'msg', 'img']);
Page::printBody();
Page::printHeader();
Page::printHeaderClose();
Page::printMain();
//var_dump($end - $start);
Page::_e("<h2 style='text-align: center;'>"
            . "Sistema Integrado de Manejo de Personal</h2>", 2);
Page::_e(Page::getForm(Page::FORM_TYPE_OPEN, 
                        'loginform',
                        'text-align:center;margin:0 auto;width:100%;'), 
        2);

switch ($display) {
    case LOGIN_DISPLAY_NEWPWD_ERROR:
        Page::_e("<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>", 3);
        Page::_e("<p>Ha ocurrido un error al tratar de restablecer su "
             . "contrase&ntilde;a: &iquest;escribi&oacute; correctamente su nueva "
             . "contrase&ntilde;a ambas veces? Si considera que as&iacute; lo "
             . "hizo, <i>contacte con un administrador</i> y expl&iacute;quele "
            . "lo sucedido.</p>", 3);
        Page::_e("<p>Para volver a intentarlo, revise su email y haga "
            . "click nuevamente en el v&iacute;nculo especificado, o bien en "
            . "el siguiente bot&oacute;n.</p>", 3);
        Page::_e("<p style='text-align:center;'>", 3);
        Page::_e(Page::getInput('button', 
                                NULL, 
                                'Volver', 
                                NULL, 
                                'btn_blue', 
                                NULL, 
                                NULL, 
                                "onClick='location.href=\"login.php\";'"), 4);
        Page::_e("</p>", 3);
        break;
    
    case LOGIN_DISPLAY_NEWPWD_OK:
        Page::_e("<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>", 3);
        Page::_e("<p>¡Se ha restablecido satisfactoriamente su contrase&ntilde;a!</p>", 3);
        Page::_e("<p style='text-align:center;'>", 3);
        Page::_e(Page::getInput('button', 
                                NULL, 
                                'Iniciar sesi&oacute;n', 
                                NULL, 
                                'btn_blue', 
                                NULL, 
                                NULL, 
                                "onClick='location.href=\"login.php\";'"), 4);
        Page::_e("</p>", 3);
        break;
    
    case LOGIN_DISPLAY_NEWPWD_FORM:
        Page::_e("<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>", 3);
        if (!empty(Session::retrieve(SMP_SESSINDEX_NOTIF_ERR))) {
            Page::_e("<p class='fadeout' "
                 . "style='color:red; text-align: center;' >" 
                 . Session::retrieve(SMP_SESSINDEX_NOTIF_ERR) . "</p>", 3);
            Session::remove(SMP_SESSINDEX_NOTIF_ERR);
        }
        Page::_e("Ingrese su nueva contrase&ntilde;a para continuar.  "
             . "<strong>No cierre esta p&aacute;gina:</strong> el proceso "
             . "de establecimiento de la nueva contrase&ntilde;a "
             . "puede demorar varios segundos.</p>", 3);
        Page::_e("La contrase&ntilde;a debe contener, al menos, una "
            . "letra may&uacute;scula, una min&uacute;scula y un "
            . "n&uacute;mero, y una logitud de entre " 
            . SMP_PWD_MINLEN . " y " . SMP_PWD_MAXLEN . " caracteres.</p>", 3);
        Page::_e("<table style='text-align: left; margin: auto; width: auto;' >", 3);
        Page::_e("<tbody>", 4);
        Page::_e("<tr>", 5);
        Page::_e("<td style='text-align: left;'>", 6);
        Page::_e("<p style='font-weight: bold;' >"
             . "Contrase&ntilde;a nueva:</p>", 7);
        Page::_e("</td>", 6);
        Page::_e("<td style='text-align: left;'>", 6);
        Page::_e(Page::getInput('password', 
                                'frm_pwdLogin[0]', 
                                NULL, 
                                NULL, 
                                'txt_resizable', 
                                NULL, 
                                NULL, 
                                "title='Ingrese nueva constrase&ntilde;a' "
                                    . "maxlength='" . SMP_PWD_MAXLEN . "'"), 
                7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("<tr>", 5);
        Page::_e("<td style='text-align: left;'>", 6);
        Page::_e("<span style='font-weight: bold;' >"
                 . "Verificar contrase&ntilde;a nueva:</span>", 7);
        Page::_e("</td>", 6);
        Page::_e("<td style='text-align: left;'>", 6);
        Page::_e(Page::getInput('password', 
                                'frm_pwdLogin[1]', 
                                NULL, 
                                NULL, 
                                'txt_resizable', 
                                NULL, 
                                NULL, 
                                "title='Repita su nueva constrase&ntilde;a' "
                                    . "maxlength='" . SMP_PWD_MAXLEN . "'")
                , 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        // mostrar captcha
        Page::_e("<tr>", 5);
        Page::_e("<td style='text-align:left;'>", 6);
        Page::_e("<span style='font-style:italic;' >"
                    . "&iquest;Cu&aacute;nto d&aacute; ", 7);
        $captcha = rand(30, 99);
        $captcha_string = $captcha . " + ";
        for ($i = 0; $i < 2; $i++) {
            $value = rand(1, 9);
            $captcha += $value;
            $captcha_string .= $value . " + ";
        }
        Session::store(LOGIN_SESSINDEX_CAPTCHA, $captcha);
        $captcha_string = substr($captcha_string, 0, 
                            strlen($captcha_string) - 3);
        Page::_e($captcha_string . "?</span>", 0, FALSE);
        Page::_e("</td>", 6);
        Page::_e("<td style='text-align:left;'>", 6);
        Page::_e(Page::getInput('number', 
                                'frm_txtCaptcha', 
                                NULL, 
                                NULL, 
                                NULL, 
                                NULL, 
                                NULL, 
                                "title='Ingrese el resultado de la "
                                    . "operaci&oacute;n' maxlength='3'"), 
                7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        // -- fin captcha
        Page::_e("<tr>", 5);
        Page::_e("<td colspan='2' style='text-align: center;' >", 6);
        Page::_e(Page::getInput('submit', 'frm_btnNewPwd', 'Cambiar contrase&ntilde;a', NULL, 'btn_blue'), 7);
        Page::_e(Page::getInput('submit', 'frm_btnCancelRestore', 'Cancelar', NULL, 'btn_red'), 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("</tbody>", 4);
        Page::_e("</table>", 3);
        break;
    
    case LOGIN_DISPLAY_EMAIL_SENT:
        Page::_e("<h3 style='text-align: center;'>Restablecimiento de "
                    . "contrase&ntilde;a</h3>", 3);
        Page::_e("<p>Se ha enviado un email a su casilla " 
                    . $usuario->getEmail(TRUE) . " con instrucciones para "
                    . "continuar.</p>", 3);
        Page::_e("<p>Puede cerrar esta p&aacute;gina.</p>", 3);
        break;
        
    case LOGIN_DISPLAY_EMAIL_NOTSENT:
        Page::_e("<h3 style='text-align: center;'>Restablecimiento de "
                    . "contrase&ntilde;a</h3>", 3);
        Page::_e("<p>No se ha podido enviar el email correctamente por "
                    . "razones desconocidas.</p>", 3);
        Page::_e("<p>Por favor, <em>contacte con un administrador</em>.</p>", 3);
        Page::_e("<p style='text-align:center;'>", 3);
        Page::_e(Page::getInput('submit', 'frm_btnCancelRestore', 'Cancelar', NULL, 'btn_red'), 4);
        Page::_e("</p>", 3);
        break;
    
    case LOGIN_DISPLAY_EMAIL_NOTFOUND:
        // no se encontro email para ese usuario, o no es un usuario valido
        Page::_e("<h3 style='text-align: center;'>Restablecimiento de "
                    . "contrase&ntilde;a</h3>", 3);
        Page::_e("<p>No se ha encontrado una direcci&oacute;n de email para el usuario "
                . "indicado, o bien no es un usuario v&aacute;lido de este "
                . "sistema.<br/>"
                . "Si considera que se trata de un error, por favor "
                . "<i>contacte con un administrador</i>.</p>", 3);
        Page::_e("<p style='text-align:center;'>", 3);
        Page::_e(Page::getInput('submit', 
                                'frm_btnCancelRestore', 
                                'Cancelar', 
                                NULL, 
                                'btn_red')
                 , 4);
        Page::_e("</p>", 3);
        break;
    
    case LOGIN_DISPLAY_PWDRESTORE_FORM:
        // Fromulario de restablecimiento de pwd
        Page::_e("<h3 style='text-align: center;'>Restablecimiento de "
                    . "contrase&ntilde;a</h3>", 3);
        Page::_e("<table style='text-align: left; margin: auto; border-collapse:separate; border-spacing:0 1em; max-width: 400px' >", 3);
        Page::_e("<tbody>", 4);
        Page::_e("<tr>", 5);
        Page::_e("<td style='text-align: left;'>", 6);
        Page::_e("<strong>Nombre de usuario:</strong>", 7);
        Page::_e("</td>", 6);
        Page::_e("<td style='text-align: left;'>", 6);
        Page::_e(Page::getInput('text', 
                                'frm_txtLogin', 
                                $username, 
                                NULL, 
                                'txt_resizable', 
                                NULL, 
                                NULL, 
                                "maxlength='" . SMP_USRNAME_MAXLEN 
                                    . "' autofocus required"), 
                7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("<tr>", 5);
        Page::_e("<td colspan='2' style='text-align: center;' >", 6);
        Page::_e("<em>Se enviar&aacute; un email a su direcci&oacute;n "
                    . "registrada en el sistema "
                    . "para continuar con el proceso de restablecimiento de "
                    . "contrase&ntilde;a</em>", 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("<tr>", 5);
        Page::_e("<td colspan='2' style='text-align: center;' >", 6);
        Page::_e(Page::getInput('submit', 'frm_btnRestore', "Reestablecer contrase&ntilde;a", NULL, 'btn_blue'), 7);
        Page::_e(Page::getInput('submit', 'frm_btnCancelRestore', "Cancelar", NULL, 'btn_red'), 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("</tbody>", 4);
        Page::_e("</table>", 3);
        break;
    
    case LOGIN_DISPLAY_LOGGEDOUT:
        Page::_e("<p>¡Hasta luego " . $username 
                    . "!  Su sesi&oacute;n ha finalizado "
                    . "satisfactoriamente.</p>", 3);
        // omito el break
    default:
        Page::_e("<em>Por favor, identif&iacute;quese para continuar</em>", 3);
        Page::_e("<br />", 3);
        Page::_e("<table style='text-align: left; margin: auto; width: auto; border-collapse:separate; border-spacing:0 1em;' >", 3);
        Page::_e("<tbody>", 4);
        if (!empty(Session::retrieve(SMP_SESSINDEX_NOTIF_ERR))) {
            Page::_e("<tr>", 5);
            Page::_e("<td colspan='2' style='text-align:center;'>", 6);
            Page::_e("<p class='fadeout' "
                 . "style='color:red; text-align: center;' >" 
                 . Session::retrieve(SMP_SESSINDEX_NOTIF_ERR) . "</p>", 7);
            Page::_e("</td>", 6);
            Page::_e("</tr>", 5);
            Session::remove(SMP_SESSINDEX_NOTIF_ERR);
        }

        $retry_count = Session::retrieve(LOGIN_SESSINDEX_RETRY_COUNT);
        if ($retry_count > LOGIN_RETRY_MAX) {
            // mostrar captcha
            Page::_e("<tr>", 5);
            Page::_e("<td style='text-align:left;'>", 6);
            Page::_e("<p style='font-style:italic;' >"
                        . "&iquest;Cu&aacute;nto d&aacute; ", 7);
            $captcha = rand(30, 99);
            $captcha_string = $captcha . " + ";
            for ($i = LOGIN_RETRY_MAX; $i < $retry_count; $i++) {
                $value = rand(1, 9);
                $captcha += $value;
                $captcha_string .= $value . " + ";
            }
            Session::store(LOGIN_SESSINDEX_CAPTCHA, $captcha);
            $captcha_string = substr($captcha_string, 0, 
                                strlen($captcha_string) - 3);
            echo $captcha_string . "?";
            echo "</p>";
            Page::_e("</td>", 6);
            Page::_e("<td style='text-align:left;'>", 6);
            Page::_e(Page::getInput('number', 
                                    'frm_txtCaptcha', 
                                    NULL, 
                                    NULL, 
                                    'txt_fixed', 
                                    NULL, 
                                    NULL, 
                                    'autofocus maxlength="3" title="Ingrese el resultado de la operaci&oacute;n"')
                    , 7);
            Page::_e("</td>", 6);
            Page::_e("</tr>", 5);
        }
        Page::_e("<tr>", 5);
        Page::_e("<td style='text-align: left;'>", 6);
        Page::_e("<strong>Nombre de usuario:</strong>", 7);
        Page::_e("</td>", 6);
        Page::_e("<td style='text-align: left;'>", 6);
        Page::_e(Page::getInput('text', 
                                'frm_txtLogin', 
                                $username, 
                                NULL, 
                                'txt_resizable', 
                                NULL, 
                                NULL, 
                                'autofocus maxlength="' . SMP_USRNAME_MAXLEN 
                                    . '" title="Ingrese el nombre de usuario"')
                , 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("<tr>", 5);
        Page::_e("<td style='text-align: left;' >", 6);
        Page::_e("<strong>Contrase&ntilde;a:</strong>", 7);
        Page::_e("</td>", 6);
        Page::_e("<td style='text-align: left;' >", 6);
        Page::_e(Page::getInput('password', 
                                'frm_pwdLogin', 
                                NULL, 
                                NULL, 
                                'txt_resizable', 
                                NULL, 
                                NULL, 
                                'maxlength="' . SMP_PWD_MAXLEN 
                                    . '" title="Ingrese la constrase&ntilde;a"')
                , 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("<tr>", 5);
        Page::_e("<td colspan='2' style='text-align: center;' >", 6);
        Page::_e(Page::getInput('submit', 
                                'frm_btnLogin', 
                                'Iniciar sesi&oacute;n', 
                                NULL, 
                                'btn_blue')
                , 7);
        Page::_e(Page::getInput('submit', 
                                'frm_btnForget', 
                                'Me olvid&eacute; la contrase&ntilde;a', 
                                NULL, 
                                'btn_green')
                , 7);
        Page::_e(Page::getInput('submit', 
                                'frm_btnCancelLogin', 
                                'Cancelar', 
                                NULL, 
                                'btn_red')
                , 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("</tbody>", 4);
        Page::_e("</table>", 3);
        break;
}
Page::_e("<hr />");
Page::_e("<p>Siempre <strong>verificar</strong> que aparezca el <strong>candado</strong> arriba a la derecha y que al darle click aparezca la leyenda <strong>Verificado por: SiMaPe</strong> y además que la dirección sea la indicada en la imagen:</p>", 3);
Page::_e("<img class='img_transparent' src='" . SMP_WEB_ROOT . SMP_LOC_IMGS . "ssl-pic.png' alt='Direccion del servidor: 5.224.0.250'/>", 3);
Page::_e(Page::getInput('hidden', 'formToken', $formToken->getToken()), 3);
Page::_e(Page::getForm(Page::FORM_TYPE_CLOSE), 2);

Page::printMainClose();
Page::printFooter();
Page::printBodyClose();