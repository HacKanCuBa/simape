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
 * @version 0.95
 */

/*
 * ToDo:
 *  - loguearse con email, legajo, dni o algun otro campo unico
 *  - reestablecer contraseña
 *   
 */

require_once 'load.php';

// Iniciar o continuar sesion
Session::initiate();

// Cerrar sesión si estaba abierta
Session::remove(SMP_SESSIONKEY_RANDOMTOKEN);
Session::remove(SMP_SESSIONKEY_TIMESTAMP);
Session::remove(SMP_SESSIONKEY_TOKEN);

// Obtener fingerprint y formtoken
$fingerprint = new Fingerprint;
$formToken = new FormToken;

// Recuperar el nombre de usuario
$user_form = Sanitizar::glPOST('frm_txt');

if (!empty(Sanitizar::glPOST('frm_btnLogin'))) {
    
    //$start = microtime(TRUE);
    
    $fingerprint->setRandomToken(Session::retrieve(SMP_FINGERPRINT_RANDOMTOKEN));
    $fingerprint->setToken(Session::retrieve(SMP_FINGERPRINT_TOKEN));
    
    $formToken->setRandomToken(Session::retrieve(SMP_FORM_RANDOMTOKEN));
    $formToken->setTimestamp(Session::retrieve(SMP_FORM_TIMESTAMP));
    $formToken->setToken(Sanitizar::glPOST(SMP_FORM_TOKEN));

    $password = new Password(Sanitizar::glPOST('frm_pwdLogin'));
    $password->retrieveFromDB($user_form);

    // Ejecuto la autenticación de la contraseña aún si el form o fingerprint
    // token no validan, para evitar Timing Oracle.
    if($password->authenticatePassword() 
       && $formToken->authenticateToken()
       && $fingerprint->authenticateToken()
    ) {
        // Login OK          
        $uid = new UID;
        $uid->retrieveFromDB($user_form);

        $session = new Session;
        $session->setPassword($uid->get());
        $session->setUID($uid);
        $session->storeEnc(SMP_SESSIONKEY_RANDOMTOKEN, 
                       $session->getRandomToken());
        $session->storeEnc(SMP_SESSIONKEY_TIMESTAMP, 
                       $session->getTimestamp());
        $session->storeEnc(SMP_SESSIONKEY_TOKEN, 
                       $session->getToken());

        $session->storeEnc(SMP_FINGERPRINT_RANDOMTOKEN, 
                       $fingerprint->getRandomToken());
        $session->storeEnc(SMP_FINGERPRINT_TOKEN, 
                       $fingerprint->getToken());

        $session->store(SMP_USERNAME, $user_form);

        $nav = SMP_LOC_MSGS;
    } else {
        // Enviar mensaje user pass incorrecto       
        Session::store(SMP_NOTIF_ERR, SMP_ERR_AUTHFAIL);
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
    // Enviar email
    /**
     * TODO
     */
    $pwdRestoreSent = TRUE;
} elseif (!empty(Sanitizar::glGET('passRestoreToken'))) {
    $password = new Password();
    $password->setToken(Sanitizar::glGET('passRestoreToken'));
}

// Crear tokens si no existen
if (empty(Session::retrieve(SMP_FINGERPRINT_RANDOMTOKEN))
    || empty(Session::retrieve(SMP_FINGERPRINT_TOKEN))
) {
    // Almacenar token de identificacion del usario
    Session::store(SMP_FINGERPRINT_RANDOMTOKEN, $fingerprint->getRandomToken());
    Session::store(SMP_FINGERPRINT_TOKEN, $fingerprint->getToken());
}

if (isset($nav)) {
    Page::nav($nav);
    exit();
}

Session::store(SMP_FORM_RANDOMTOKEN, $formToken->getRandomToken());
Session::store(SMP_FORM_TIMESTAMP, $formToken->getTimestamp());

echo Page::getHead('SiMaPe - Iniciar Sesi&oacute;n');
echo "\n\t<style type='text/css'>\n\t.data { margin-left: auto; }"
     . "\n\t</style>";
echo Page::getBody();
echo Page::getHeader();
echo Page::getHeaderClose();
echo Page::getMain();
//var_dump($end - $start);
echo "\n\t\t<h2 style='text-align: center;'>Sistema Integrado de Manejo de Personal</h2>";
echo "\n\t\t<form style='text-align: center; margin: 0 auto; width: 100%;' "
     . "name='loginform' id='loginform' method='post' >";
if (empty($pwdRestoreSent)) {
    echo "\n\t\t\t<address>Por favor, identif&iacute;quese para continuar</address>";
    echo "\n\t\t\t<br />";
    if (!empty(Session::retrieve(SMP_NOTIF_ERR))) {
        echo "\n\t\t\t<address class='fadeout' "
             . "style='color:red; text-align: center;' >" 
             . Session::retrieve(SMP_NOTIF_ERR) . "</address>\n"; 
        Session::remove(SMP_NOTIF_ERR);
    } else {
        echo "\n\t\t\t<br />";
    }
    echo "\n\t\t\t<table style='text-align: left; margin: auto; with: auto;' >";
    echo "\n\t\t\t\t<tbody>";
    echo "\n\t\t\t\t\t<tr>";
    echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
    echo "\n\t\t\t\t\t\t\t<br />";
    echo "\n\t\t\t\t\t\t\t<span style='font-weight: bold;' >Nombre de usuario:"
         . "</span>";
    echo "\n\t\t\t\t\t\t</td>";
    echo "\n\t\t\t\t\t\t<td style='text-align: left;'>";
    echo "\n\t\t\t\t\t\t\t<br />";
    echo "\n\t\t\t\t\t\t\t<input name='frm_txt' "
         . "title='Ingrese el nombre de usuario' maxlength='" 
         . SMP_USRNAME_MAXLEN . "' type='text' autofocus value='" . $user_form 
         . "'/>";
    echo "\n\t\t\t\t\t\t</td>";
    echo "\n\t\t\t\t\t</tr>";
    if (empty($pwdRestore)) {
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
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnForget' value='Me olvide la contrase&ntilde;a' "
             . "title='Proceso para reestablecer su contraseña' "
             . "value='Me olvid&eacute; la contrase&ntilde;a' type='submit' />";
        echo "\n\t\t\t\t\t\t\t<input name='frm_btnCancelLogin' value='Cancelar' " 
             . "title='Volver a la p&aacute;gina inicial' type='submit' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
    } else {
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
} else {
    // TODO: casilla de email enmascarada
    echo "\n\t\t\tSe ha enviado un email a su casilla con instrucciones "
         . "para continuar.<br />Puede cerrar &eacute;sta p&aacute;gina.";
}
echo "\n\t\t\t<input type='hidden' name='formToken' value='"
     . $formToken->getToken() . "' />";
echo "\n\t\t</form>";

echo Page::getMainClose();
echo Page::getFooter();