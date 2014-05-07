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
 * @version 1.1
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
const LOGIN_ERR_CAPTCHA = 'La operación de verificación no ha sido resuelta correctamente';

/**
 * Nombre de indice de _SESSION donde se almacena el valor del captcha.
 */
const LOGIN_SESSINDEX_CAPTCHA = 'CAPTCHA';

// Iniciar o continuar sesion
Session::initiate();

// Cerrar sesión si estaba abierta
Session::remove(SMP_SESSINDEX_SESSIONKEY_TOKEN);

// Inicializaciones
$formToken = new FormToken;

// Recuperar el nombre de usuario
$username = Sanitizar::glPOST('frm_txtLogin');

if (!empty(Sanitizar::glPOST('frm_btnLogin'))) {
    
    //$start = microtime(TRUE);

    // Pruebo captcha primero
    // si aun no hay captcha, ambos seran equivalentes (NULL y STRING NULL)
    if (Session::retrieve(LOGIN_SESSINDEX_CAPTCHA) == Sanitizar::glPOST('frm_txtCaptcha'))
    {
        // captcha OK
        $password = new Password(Sanitizar::glPOST('frm_pwdLogin'));
        $password->retrieve_fromDB($username);
            
        $formToken->setRandomToken(Session::retrieve(SMP_SESSINDEX_FORM_RANDOMTOKEN));
        $formToken->setTimestamp(Session::retrieve(SMP_SESSINDEX_FORM_TIMESTAMP));
        $formToken->setToken(Sanitizar::glPOST(SMP_SESSINDEX_FORM_TOKEN));
        
        // Ejecuto la autenticación de la contraseña aún si el form
        // token no valida, para evitar Timing Oracle.
        if($password->authenticatePassword() 
           && $formToken->authenticateToken()
        ) {
            // Login OK 
            // ToDo: verificaciones (guardo ok? leyo ok?)
            $uid = new UID;
            $uid->retrieve_fromDB($username);
            // Iniciar sesion...
            $session = new Session;
            $session->generateRandomToken();
            $session->generateTimestamp();
            $session->setUID($uid);
            $session->generateToken();
            // Guardar RandToken y Timestamp de sesion en DB
            $session->retrieve_fromDB_TokenID($username);
            $session->store_inDB();
            // Guardar Token de sesion en SESSION
            $session->store(SMP_SESSINDEX_SESSIONKEY_TOKEN, $session->getToken());
            
            // Fingerprint
            $fingerprint = new Fingerprint;
            $fingerprint->setMode(Fingerprint::MODE_USEIP);
            $fingerprint->generateToken();
            // Guardarlo en DB
            $fingerprint->retrieve_fromDB_TokenID($username);
            $fingerprint->store_inDB();
            
            // Guardar nombre de usuario en SESSION
            $session->store(SMP_SESSINDEX_USERNAME, $username);

            // elimino el captcha, si existiese
            Session::remove(LOGIN_SESSINDEX_CAPTCHA);
            
            // Sesion iniciada, ir a la pagina de inicio del usuario
            $nav = SMP_HOME;
        } else {
            // Enviar mensaje user pass incorrecto       
            Session::store(SMP_SESSINDEX_NOTIF_ERR, SMP_ERR_AUTHFAIL);

            $login_atempt = TRUE;
        }  
    } else {
        // Enviar mensaje captcha incorrecto       
        Session::store(SMP_SESSINDEX_NOTIF_ERR, LOGIN_ERR_CAPTCHA);
        $login_atempt = TRUE;
    }
    //$end = microtime(TRUE);
} elseif (!empty(Sanitizar::glPOST('frm_btnCancelLogin'))) {
    // Volver a la pág inicial
    $nav = SMP_WEB_ROOT;
} elseif (!empty(Sanitizar::glPOST('frm_btnCancelRestore'))) {
    // Cargar Login normal
} elseif (!empty(Sanitizar::glPOST('frm_btnForget'))) {
    // Cargar form de reestablecimiento de contraseña
    $pwdRestore = TRUE;
} elseif (!empty(Sanitizar::glPOST('frm_btnRestore'))) {
    // Crear tokens de reestablecimiento de pwd
    $password = new Password;
    $password->generateRandomToken();
    $password->generateTimestamp();
    
    $uid = new UID();
    $uid->retrieve_fromDB($username);
    
    $password->setUID($uid);
    $password->generateToken();
    
    // Guardar RandTkn y Timestamp en la DB
    $password->retrieve_fromDB_TokenID($username);
    $password->store_inDB_PwdRestore($username);
    
    // Enviar email
    // Cargar clase PHPMailer
    require SMP_INC_ROOT . SMP_LOC_INCS . 'phpmailer/PHPMailerAutoload.php';
    
    $mail = new PHPMailer(FALSE);
    $mail->CharSet = SMP_PAGE_CHARSET;
    $mail->Debugoutput = 'error_log';
    $mail->IsSMTP();
    $mail->SMTPDebug = 0;
    $mail->SMTPAuth = TRUE;
    $mail->SMTPSecure = SMP_EMAIL_SMTP_PROTO;
    $mail->Host = SMP_EMAIL_SMTP_HOST;
    $mail->Port = SMP_EMAIL_SMTP_PORT;
    $mail->Username = SMP_EMAIL_USER;
    $mail->Password = SMP_EMAIL_PSWD;
    $mail->From = SMP_EMAIL_FROM;
    $mail->FromName = "SiMaPe";
    $mail->IsHTML(TRUE);
    
    $mail->AddAddress("hackan@gmail.com");
    
    $mail->Subject = "Test PHPMailer Message";
    $mail->msgHTML("Hi! \n\n This was sent with phpMailer_example3.php.\nPass restore: " . $password->getToken());
    $mail->AltBody = "This is the body in plain text for non-HTML mail clients";
    
    $pwdRestoreSent = $mail->send();
} elseif (!empty(Sanitizar::glGET('passRestoreToken'))) {
    $password = new Password();
    $password->setToken(Sanitizar::glGET('passRestoreToken'));
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
$formToken->generateRandomToken();
$formToken->generateTimestamp();
$formToken->generateToken();
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
if (isset($pwdRestoreSent)) {
    // email enviado?
    if ($pwdRestoreSent) {
        // TODO: mostrar casilla de email enmascarada
        echo "\n\t\t\tSe ha enviado un email a su casilla con instrucciones "
             . "para continuar.<br />Puede cerrar &eacute;sta p&aacute;gina.";
    } else {
        echo "\n\t\t\tNo se ha podido enviar el email correctamente por razones "
            . "desconocidas.<br />Por favor, <i>contacte con un administrador</i>.";
    }
} else {
    echo "\n\t\t\t<address>Por favor, identif&iacute;quese para continuar</address>";
    echo "\n\t\t\t<br />";
    echo "\n\t\t\t<table style='text-align: left; margin: auto; with: auto;' >";
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
    // captcha? solo si no tengo q mostrar form de reestablecimiento de pwd
    if (empty($pwdRestore)) {
        $retry_count = Session::retrieve(LOGIN_SESSINDEX_RETRY_COUNT);
        if ($retry_count > LOGIN_RETRY_MAX) {
            // mostrar captcha
            echo "\n\t\t\t\t\t<tr>";
            echo "\n\t\t\t\t\t\t<td style='text-align:left;'>";
            echo "\n\t\t\t\t\t\t\t<br />";
            echo "\n\t\t\t\t\t\t\t<span style='font-style:italic;' >"
                 . "&iquest;Cuánto d&aacute; ";
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
                    . "title='Ingrese el resultado de la operación' maxlength='3' />";
            echo "\n\t\t\t\t\t\t</td>";
            echo "\n\t\t\t\t\t</tr>";
        }
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
    if (empty($pwdRestore)) {
        // Fomulario de login
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
    } else {
        // Fromulario de reestablecimiento de pwd
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;' >";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<address style='width: auto; max-width: 350px;'>"
             . "Se enviará un email a su dirección registrada en el sistema "
             . "para continuar con el proceso de reestablecimiento de "
             . "contrase&ntilde;a</address>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnRestore' value='Reestablecer "
             . "contrase&ntilde;a' title='Env&iacute;a un email a su casilla registrada' "
             . "type='submit' />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnCancelRestore' value='Cancelar' " 
             . "title='Volver a la p&aacute;gina anterior' type='submit' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
    }
    echo "\n\t\t\t\t</tbody>";
    echo "\n\t\t\t</table>";
}
echo "\n\t\t\t<input type='hidden' name='formToken' value='"
     . $formToken->getToken() . "' />";
echo "\n\t\t</form>";

echo Page::getMainClose();
echo Page::getFooter();