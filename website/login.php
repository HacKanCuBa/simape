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

/**
 * Texto de error que se mostrará el token de restablecimiento de pwd sea incorrecto.
 */
const LOGIN_ERR_RESTORETKN = '&iexcl;El token de restablecimiento de contrase&ntilde;a no es v&aacute;lido!';

/**
 * Constantes que manejan lo que será mostrado al usuario.
 */
const DISPLAY_DEFAULT = NULL;
const DISPLAY_PWDRESTORE_FORM = 1;
const DISPLAY_PWDRESTORE_ERROR = 2;
const DISPLAY_PWDRESTORE_OK = 3;
const DISPLAY_EMAIL_SENT = 4;
const DISPLAY_EMAIL_NOTSENT = 5;
const DISPLAY_EMAIL_NOTFOUND = 6;
const DISPLAY_NEWPWD_FORM = 7;
const DISPLAY_NEWPWD_OK = 8;
const DISPLAY_NEWPWD_ERROR = 9;

/**
 * @var string Indica qué se mostrará al usuario en la página.
 */
$display = DISPLAY_DEFAULT;

// Iniciar o continuar sesion
Session::initiate();

// Cerrar sesión si estaba abierta
Session::remove(SMP_SESSINDEX_SESSIONKEY_TOKEN);

// Inicializaciones
$formToken = new FormToken;

// Recuperar el nombre de usuario
$username = trim(Sanitizar::glPOST('frm_txtLogin'));
if (empty($username)) {
    $username = trim(Sanitizar::glGET('username'));
}

if (!empty(Sanitizar::glPOST('frm_btnLogin'))) {
    
    //$start = time();

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
        }  
    } else {
        // Enviar mensaje captcha incorrecto       
        Session::store(SMP_SESSINDEX_NOTIF_ERR, LOGIN_ERR_CAPTCHA);
    }
    $login_atempt = TRUE;
    $display = DISPLAY_DEFAULT;
    //$end = time();
} elseif (!empty(Sanitizar::glPOST('frm_btnCancelLogin'))) {
    // Volver a la pág inicial
    $nav = SMP_WEB_ROOT;
} elseif (!empty(Sanitizar::glPOST('frm_btnCancelRestore'))) {
    // Cargar Login normal
    $nav = SMP_LOGIN;
} elseif (!empty(Sanitizar::glPOST('frm_btnForget'))) {
    // Cargar form de restablecimiento de contraseña
    $display = DISPLAY_PWDRESTORE_FORM;
} elseif (!empty(Sanitizar::glPOST('frm_btnRestore'))) {
    if (empty($username)) {
        $display =  DISPLAY_EMAIL_NOTFOUND;
    } else {
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
        $password->store_inDB_PwdRestore();

        if (!empty(Sanitizar::glSERVER('HTTPS'))) {
            $passrestore_url = 'https://';
        } else {
            $passrestore_url = 'http://';
        }
        $passrestore_url .= Sanitizar::glSERVER('SERVER_NAME') 
                            . Sanitizar::glSERVER('SCRIPT_NAME') 
                            . '?username=' . $username 
                            . '&passRestoreToken=' . $password->getToken();

        // Enviar email
        // Cargar clase PHPMailer
        $email = new Email;

        $email->setFrom('SiMaPe', SMP_EMAIL_FROM);
        $email->addAddress("hackan@gmail.com");/*!!!!!!!!!!!!!!!!!!!!!!!!*/
        $email->setSubjet('Restablecimiento de contraseña para SiMaPe');
        $email->setBody('<html>
	<head>
		<title></title>
	</head>
	<body style="background:#e0e0e0;">
		<h2 style="text-align: center;">
                    <span style="font-family:courier new,courier,monospace;">Sistema Integrado de Manejo de Personal</span>
                </h2>
		<p>
                    <span style="font-family:courier new,courier,monospace;">Ha solicitado restablecer su contrase&ntilde;a en SiMaPe, y por eso recibe este correo.&nbsp; Si no realiz&oacute; esta acci&oacute;n, puede omitir este mensaje sin m&aacute;s, su cuenta sigue estando segura.</span>
                </p>
		<p>
                    <span style="font-family:courier new,courier,monospace;">Para continuar con el proceso, dir&iacute;jase a este enlace (o bien copie y pegue en su navegador):<br /><a href="' . $passrestore_url . '">' . $passrestore_url . '</a></span>
                </p>
                <p>
                    <span style="font-family:courier new,courier,monospace;">Tenga en cuenta que el v&iacute;nculo arriba indicado caducar&aacute; a los ' . (SMP_PASSWORD_RESTORETIME / 60)  . ' minutos de recibido este email (exactamente a las ' . strftime('%H:%M:%S del %d de %B del %G' , $password->getTimestamp()) . '), y deber&aacute; solicitar restablecer su contrase&ntilde;a nuevamente.</span>
                </p>
		<p>
                    <span style="font-family:courier new,courier,monospace;">Atte.:<br />
                    SiMaPe</span>
                </p>
		<p>
                    <span style="font-family:courier new,courier,monospace;"><em><small>P. D.: este mensaje ha sido generado autom&aacute;ticamente.&nbsp; Por favor, no responder al mismo dado que ninguna persona lo leer&aacute;.</small></em></span>
                </p>
	</body>
</html>');
    
        if($email->send()) {
            $display = DISPLAY_EMAIL_SENT;
        } else {
            $display = DISPLAY_EMAIL_NOTSENT;
        }
    }   
} elseif (!empty(Sanitizar::glGET('passRestoreToken'))) {
    $password = new Password;
    $password->retrieve_fromDB_TokenID($username);
    $password->retrieve_fromDB_PwdRestore();
    
    $uid = new UID();
    $uid->retrieve_fromDB($username);
    
    $password->setUID($uid);
    $password->setToken(Sanitizar::glGET('passRestoreToken'));
    
    if ($password->authenticateToken()) {
        // Token válido
        if (empty(Sanitizar::glPOST('frm_btnNewPwd'))) {
            // mostrar formulario
            $display = DISPLAY_NEWPWD_FORM;
        } else {
            // guardar nueva contraseña
            $display = DISPLAY_NEWPWD_FORM;
            
            $formToken->setRandomToken(Session::retrieve(SMP_SESSINDEX_FORM_RANDOMTOKEN));
            $formToken->setTimestamp(Session::retrieve(SMP_SESSINDEX_FORM_TIMESTAMP));
            $formToken->setToken(Sanitizar::glPOST(SMP_SESSINDEX_FORM_TOKEN));
            if ($formToken->authenticateToken()) {
                $newpasswd = Sanitizar::glPOST('frm_pwdLogin');
                $newpasswd = [trim($newpasswd[0]), trim($newpasswd[1])];
                if ($newpasswd[0] === $newpasswd[1]) {
                    if ($password->setPlaintext($newpasswd[0], TRUE)) {
                        $display = DISPLAY_NEWPWD_ERROR;
                        if ($password->encryptPassword()) {
                            if ($password->store_inDB($username)) {
                                // nueva contraseña establecida
                                // borrar tokens de restablecimiento
                                $password->setTimestamp(1);
                                $password->generateRandomToken();
                                $password->retrieve_fromDB_TokenID($username);
                                $password->store_inDB_PwdRestore();
                                
                                $display = DISPLAY_NEWPWD_OK;
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
            }
        }
    } else {
        // Token inválido
        $display = DISPLAY_DEFAULT;
        Session::store(SMP_SESSINDEX_NOTIF_ERR, LOGIN_ERR_RESTORETKN);
    }
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

switch ($display) {
    case DISPLAY_NEWPWD_ERROR:
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<p>Ha ocurrido un error al tratar de restablecer su "
             . "contrase&ntilde;a: &iquest;escribió correctamente su nueva "
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
    
    case DISPLAY_NEWPWD_OK:
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<p>¡Se ha restablecido satisfactoriamente su contrase&ntilde;a!</p>";
        echo "\n\t\t\t<p style='text-align:center;'><input "
            . "name='frm_btnCancelRestore' value='Continuar' " 
            . "title='Volver a la p&aacute;gina de inicio de sesi&oacute;n' "
            . "type='submit' />"
            . "</p>";
        break;
    
    case DISPLAY_NEWPWD_FORM:
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
    
    case DISPLAY_EMAIL_SENT:
        // TODO: mostrar casilla de email enmascarada
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<p>Se ha enviado un email a su casilla con instrucciones "
             . "para continuar.</p>";
        echo "\n\t\t\t<p>Puede cerrar esta p&aacute;gina.</p>";
        break;
        
    case DISPLAY_EMAIL_NOTSENT:
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
    
    case DISPLAY_EMAIL_NOTFOUND:
        // no se encontro email para ese usuario, o no es un usuario valido
        echo "\n\t\t\t<h3 style='text-align: center;'>Restablecimiento de "
             . "contrase&ntilde;a</h3>";
        echo "\n\t\t\t<p>No se ha encontrado una dirección de email para el usuario "
                . "indicado, o bien no es un usuario v&aacute;lido de este "
                . "sistema.<br/>"
                . "Si considera que se trata de un error, por favor "
                . "<i>contacte con un administrador</i>.</p>";
        echo "\n\t\t\t<p style='text-align:center;'><input "
                . "name='frm_btnCancelRestore' value='Volver' " 
                . "title='Volver a la p&aacute;gina anterior' type='submit' />"
                . "</p>";
        break;
    
    case DISPLAY_PWDRESTORE_FORM:
        // Fromulario de reestablecimiento de pwd
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
        echo "\n\t\t\t\t</tbody>";
        echo "\n\t\t\t</table>";
        break;
    
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
                    . "title='Ingrese el resultado de la operación' "
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