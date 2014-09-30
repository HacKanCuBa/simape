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
 * @version 1.34
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

if (isset($nav)) {
    Page::nav($nav);
    exit();
}

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
echo Page::getHead('SiMaPe - Iniciar Sesi&oacute;n');
echo Page::getBody();
echo Page::getHeader();
echo Page::getHeaderClose();
echo Page::getMain();
//var_dump($end - $start);
echo "\n\t\t<h2 style='text-align: center;'>Sistema Integrado de Manejo de Personal</h2>";
echo "\n\t\t<form style='text-align: center; margin: 0 auto; width: 100%;' "
     . "name='loginform' id='loginform' method='post' >";

switch ($display) {
    case LOGIN_DISPLAY_NEWPWD_ERROR:
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<p>Ha ocurrido un error al tratar de restablecer su "
             . "contrase&ntilde;a: &iquest;escribi&oacute; correctamente su nueva "
             . "contrase&ntilde;a ambas veces? Si considera que as&iacute; lo "
             . "hizo, <i>contacte con un administrador</i> y expl&iacute;quele "
            . "lo sucedido.</p>";
        echo "\n\t\t\t<p>Para volver a intentarlo, revise su email y haga "
            . "click nuevamente en el v&iacute;nculo especificado, o bien en "
            . "el siguiente bot&oacute;n.</p>";
        echo "\n\t\t\t<p style='text-align:center;'><input "
            . "value='Volver' onClick='history.back()'" 
            . "title='Volver a la p&aacute;gina anterior' type='button' />"
            . "</p>";
        break;
    
    case LOGIN_DISPLAY_NEWPWD_OK:
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<p>¡Se ha restablecido satisfactoriamente su contrase&ntilde;a!</p>";
        echo "\n\t\t\t<p style='text-align:center;'><input "
            . "name='frm_btnCancelRestore' value='Continuar' " 
            . "title='Volver a la p&aacute;gina de inicio de sesi&oacute;n' "
            . "type='submit' />"
            . "</p>";
        break;
    
    case LOGIN_DISPLAY_NEWPWD_FORM:
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        if (!empty(Session::retrieve(SMP_SESSINDEX_NOTIF_ERR))) {
            echo "\n\t\t\t<p><address class='fadeout' "
                 . "style='color:red; text-align: center;' >" 
                 . Session::retrieve(SMP_SESSINDEX_NOTIF_ERR) . "</address></p>";
            Session::remove(SMP_SESSINDEX_NOTIF_ERR);
        }
        echo "\n\t\t\t<p>Ingrese su nueva contrase&ntilde;a para continuar.  "
             . "<b>No cierre esta p&aacute;gina:</b> el proceso de establecimiento de la nueva contrase&ntilde;a "
             . "puede demorar varios segundos.</p>";
        echo "\n\t\t\t<p>La contrase&ntilde;a debe contener, al menos, una "
            . "letra may&uacute;scula, una min&uacute;scula y un "
            . "n&uacute;mero, y una logitud de entre " 
            . SMP_PWD_MINLEN . " y " . SMP_PWD_MAXLEN . " caracteres.</p>";
        echo "\n\t\t\t<table style='text-align: left; margin: auto; width: auto;' >";
        echo "\n\t\t\t\t<tbody>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<span style='font-weight: bold;' >"
             . "Contrase&ntilde;a nueva:</span>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
         echo "\n\t\t\t\t\t\t\t<input name='frm_pwdLogin[0]' "
             . "title='Ingrese nueva constrase&ntilde;a' type='password' "
             . "maxlength='" . SMP_PWD_MAXLEN . "' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<span style='font-weight: bold;' >"
             . "Verificar contrase&ntilde;a nueva:</span>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
         echo "\n\t\t\t\t\t\t\t<input name='frm_pwdLogin[1]' "
             . "title='Repita su nueva constrase&ntilde;a' type='password' "
             . "maxlength='" . SMP_PWD_MAXLEN . "' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        // mostrar captcha
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align:left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<span style='font-style:italic;' >"
             . "&iquest;Cu&aacute;nto d&aacute; ";
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
        echo $captcha_string . "?";
        echo "</span>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t\t<td style='text-align:left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_txtCaptcha' type='number' "
                . "title='Ingrese el resultado de la operaci&oacute;n' "
                . "maxlength='3' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        // -- fin captcha
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;' >";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnNewPwd' "
             . "value='Cambiar contrase&ntilde;a' "
             . "type='submit' />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnCancelRestore' value='Cancelar' " 
             . "title='Volver a la p&aacute;gina anterior' type='submit' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t</tbody>";
        echo "\n\t\t\t</table>";
        break;
    
    case LOGIN_DISPLAY_EMAIL_SENT:
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<p>Se ha enviado un email a su casilla " 
             . $usuario->getEmail(TRUE) . " con instrucciones para "
             . "continuar.</p>";
        echo "\n\t\t\t<p>Puede cerrar esta p&aacute;gina.</p>";
        break;
        
    case LOGIN_DISPLAY_EMAIL_NOTSENT:
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<p>No se ha podido enviar el email correctamente por "
            . "razones desconocidas.</p>";
        echo "\n\t\t\t<p>Por favor, <i>contacte con un administrador</i>.</p>";
        echo "\n\t\t\t<p style='text-align:center;'><input "
            . "name='frm_btnCancelRestore' value='Volver' " 
            . "title='Volver a la p&aacute;gina anterior' type='submit' />"
            . "</p>";
        break;
    
    case LOGIN_DISPLAY_EMAIL_NOTFOUND:
        // no se encontro email para ese usuario, o no es un usuario valido
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<p>No se ha encontrado una direcci&oacute;n de email para el usuario "
                . "indicado, o bien no es un usuario v&aacute;lido de este "
                . "sistema.<br/>"
                . "Si considera que se trata de un error, por favor "
                . "<i>contacte con un administrador</i>.</p>";
        echo "\n\t\t\t<p style='text-align:center;'><input "
                . "name='frm_btnCancelRestore' value='Volver' " 
                . "title='Volver a la p&aacute;gina anterior' type='submit' />"
                . "</p>";
        break;
    
    case LOGIN_DISPLAY_PWDRESTORE_FORM:
        // Fromulario de restablecimiento de pwd
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<table style='text-align: left; margin: auto; width: auto;' >";
        echo "\n\t\t\t\t<tbody>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<span style='font-weight: bold;' >Nombre de usuario:"
             . "</span>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_txtLogin' "
             . "title='Ingrese el nombre de usuario' maxlength='" 
             . SMP_USRNAME_MAXLEN . "' type='text' autofocus value='" . $username 
             . "' required='true'/>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;' >";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<address style='width: auto; max-width: 350px;'>"
             . "Se enviar&aacute; un email a su direcci&oacute;n registrada en el sistema "
             . "para continuar con el proceso de restablecimiento de "
             . "contrase&ntilde;a</address>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnRestore' value='Reestablecer "
             . "contrase&ntilde;a' title='Env&iacute;a un email a su casilla registrada' "
             . "type='submit' />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnCancelRestore' value='Cancelar' " 
             . "title='Volver a la p&aacute;gina anterior' type='submit' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t</tbody>";
        echo "\n\t\t\t</table>";
        break;
    
    case LOGIN_DISPLAY_LOGGEDOUT:
        echo "\n\t\t\t<p>¡Hasta luego " . $username . "!  Su sesi&oacute;n ha finalizado "
            . "satisfactoriamente.</p>";
        // omito el break
    default:
        echo "\n\t\t\t<address>Por favor, identif&iacute;quese para continuar</address>";
        echo "\n\t\t\t<br />";
        echo "\n\t\t\t<table style='text-align: left; margin: auto; width: auto;' >";
        echo "\n\t\t\t\t<tbody>";
        if (!empty(Session::retrieve(SMP_SESSINDEX_NOTIF_ERR))) {
            echo "\n\t\t\t\t\t<tr>";
            echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align:center;'>";
            echo "\n\t\t\t\t\t\t\t<address class='fadeout' "
                 . "style='color:red; text-align: center;' >" 
                 . Session::retrieve(SMP_SESSINDEX_NOTIF_ERR) . "</address>";
            echo "\n\t\t\t\t\t\t</td>";
            echo "\n\t\t\t\t\t</tr>";
            Session::remove(SMP_SESSINDEX_NOTIF_ERR);
        }

        $retry_count = Session::retrieve(LOGIN_SESSINDEX_RETRY_COUNT);
        if ($retry_count > LOGIN_RETRY_MAX) {
            // mostrar captcha
            echo "\n\t\t\t\t\t<tr>";
            echo "\n\t\t\t\t\t\t<td style='text-align:left;'>";
            echo "\n\t\t\t\t\t\t\t<br />";
            echo "\n\t\t\t\t\t\t\t<span style='font-style:italic;' >"
                 . "&iquest;Cu&aacute;nto d&aacute; ";
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
            echo "</span>";
            echo "\n\t\t\t\t\t\t</td>";
            echo "\n\t\t\t\t\t\t<td style='text-align:left;'>";
            echo "\n\t\t\t\t\t\t\t<br />";
            echo "\n\t\t\t\t\t\t\t<input name='frm_txtCaptcha' type='number' "
                    . "title='Ingrese el resultado de la operaci&oacute;n' "
                    . "maxlength='3' />";
            echo "\n\t\t\t\t\t\t</td>";
            echo "\n\t\t\t\t\t</tr>";
        }
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<span style='font-weight: bold;' >Nombre de usuario:"
             . "</span>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_txtLogin' "
             . "title='Ingrese el nombre de usuario' maxlength='" 
             . SMP_USRNAME_MAXLEN . "' type='text' autofocus value='" . $username 
             . "'/>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;' >";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<span style='font-weight: bold;' >"
             . "Contrase&ntilde;a:</span>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t\t<td style='text-align: left;' >";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_pwdLogin' "
             . "title='Ingrese la constrase&ntilde;a' type='password' maxlength='" 
             . SMP_PWD_MAXLEN . "' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;' >";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnLogin' value='Iniciar sesi&oacute;n' "
             . "type='submit' />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnForget' "
             . "title='Proceso para reestablecer su contraseña' "
             . "value='Me olvid&eacute; la contrase&ntilde;a' type='submit' />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnCancelLogin' value='Cancelar' " 
             . "title='Volver a la p&aacute;gina inicial' type='submit' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t</tbody>";
        echo "\n\t\t\t</table>";
        break;
}
echo "\n\t\t\t<input type='hidden' name='formToken' value='"
     . $formToken->getToken() . "' />";
echo "\n\t\t</form>";

echo Page::getMainClose();
echo Page::getFooter();
echo Page::getBodyClose();