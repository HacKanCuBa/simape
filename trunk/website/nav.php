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
 * nav.php
 * Esta página se emplea para navegar entre las distintas secciones del sitio.
 * Todas las paginas deben llamar a ésta y ésta redireccionará adecuadamente.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.3
 */

require_once 'load.php';

// Iniciar o continuar sesion
Session::initiate();

$action = Sanitizar::glGET(SMP_NAV_ACTION);

// Inicializo variables de redireccion
$params = NULL;
$intLink = NULL;

$page = new Page;

switch($action) {
    case SMP_LOGOUT:
    case NULL:
    case '':
    case SMP_WEB_ROOT:
        Session::remove(SMP_SESSINDEX_SESSIONKEY_TOKEN);
        $page->setLocation(SMP_WEB_ROOT);
        // Ya se que da FALSE, es para que se entienda.
        // Location=NULL lleva a WEBROOT
        break;
    
    case SMP_LOC_LOGIN:
        $page->setLocation(SMP_LOC_LOGIN);
        break;
    
    case SMP_LOC_MSGS:
        $intLink = "tabR"; // no uso break para que ejecute default
    default:
        // si la página no existe, 404...
        if (Page::pageExists($action)) {
            // Si el usuario está loggeado, dirigirse a la pag solicitada con un
            // page token.
            // Si no esta loggeado, darán error las comprobaciones
            $username = Session::retrieve(SMP_SESSINDEX_USERNAME);
            
            $uid = new UID;
            $uid->retrieve_fromDB($username);
            
            $session = new Session;
            $session->setUID($uid);
            $session->retrieve_fromDB_TokenID($username);
            $session->retrieve_fromDB();
            $session->setToken(Session::retrieve(SMP_SESSINDEX_SESSIONKEY_TOKEN));
            
            $fingerprint = new Fingerprint;
            $fingerprint->retrieve_fromDB_TokenID($username);
            $fingerprint->retrieve_fromDB();
            
            if ($fingerprint->authenticateToken() 
                && $session->authenticateToken()
            ) {
                // Login OK
                // Page Token
                $page->generateRandomToken();
                $page->generateTimestamp();
                $page->setLocation($action);
                $page->generateToken();
                
                // Guardo Page RandTkn y Timestamp en SESSION encriptado
                $session->setPassword($uid->getHash());
                $session->setPasswordSalt($session->getRandomToken());
                
                $session->storeEnc(SMP_SESSINDEX_PAGE_RANDOMTOKEN, 
                                    $page->getRandomToken());
                $session->storeEnc(SMP_SESSINDEX_PAGE_TIMESTAMP, 
                                    $page->getTimestamp());
                
                // Paso por GET el Page Token
                $params = "pagetkn=" . $page->getToken();
            }
        } else {
            // No existe la pagina
            $page->setLocation(SMP_LOC_404);
        }
        break;
}

$page->go($params, $intLink);
exit();