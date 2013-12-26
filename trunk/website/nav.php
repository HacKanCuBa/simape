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

// ESTA PAGINA NO DEBE SER ACCEDIDA DIRECTAMENTE POR EL USUARIO

/**
 * Esta página se emplea para navegar entre las distintas secciones del sitio.
 * Todas las paginas deben llamar a ésta y ésta redireccionará adecuadamente.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.0
 */

require_once 'load.php';

// Iniciar o continuar sesion
Session::initiate();

$session = new Session;
$uid = new UID;
$uid->retrieveFromDB($session->retrieve(SMP_USERNAME));
$session->setPassword($uid->get());

$session->setRandomToken($session->retrieveEnc(SMP_SESSIONKEY_RANDOMTOKEN));
$session->setTimestamp($session->retrieveEnc(SMP_SESSIONKEY_TIMESTAMP));
$session->setUID($uid);
$session->setToken($session->retrieveEnc(SMP_SESSIONKEY_TOKEN));

$fingerprint = new Fingerprint;
$fingerprint->setRandomToken($session->retrieveEnc(SMP_FINGERPRINT_RANDOMTOKEN));
$fingerprint->setToken($session->retrieveEnc(SMP_FINGERPRINT_TOKEN));

if ($fingerprint->authenticateToken() && $session->authenticateToken()) {
    // Login OK 
    // Realizar navegacion...  
    
    $action = Sanitizar::glGET(SMP_NAV_ACTION);
    
    switch($action) {
        case 'logout':
            Session::remove(SMP_SESSIONKEY_RANDOMTOKEN);
            Session::remove(SMP_SESSIONKEY_TIMESTAMP);
            Session::remove(SMP_SESSIONKEY_TOKEN);
            $params = NULL;
            $redirect = NULL;
            break;

        default:
            if (Page::isValid($action)){
                $page = new Page;
                
                $session->storeEnc(SMP_PAGE_RANDOMTOKEN, 
                                    $page->getRandomToken());
                $session->storeEnc(SMP_PAGE_TIMESTAMP, 
                                    $page->getTimestamp());

                $params = "pagetkn=" . $page->getToken();
                
                // Parche para la página de mensajes
                if ($action == SMP_LOC_MSGS) {
                    $params .= "#tabR";
                }
                $redirect = $action;
            } else {
                $redirect = NULL;
                $params = NULL;
            }
            break;
    }
}
else
{
    // Error de autenticacion
    Session::terminate();
    Session::initiate();
    Session::store(SMP_NOTIF_ERR, SMP_ERR_AUTHFAIL);
    $redirect = SMP_LOC_LOGIN;  
    $params = NULL;
}

Page::go_to($redirect, $params);
exit();