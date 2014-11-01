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
 * @version 1.48
 */

require_once 'load.php';

// Iniciar o continuar sesion
$session = new Session;

$action = Sanitizar::glGET(SMP_NAV_ACTION);

// Inicializo variables de redireccion
$params = Sanitizar::glGET(SMP_NAV_PARAMS);
$intLink = NULL;

$page = new Page;
$session->useSystemPassword();
$db = new DB(SMP_DB_CHARSET);
$fingp = new Fingerprint();

$usuario = new Usuario($db, $session->retrieveEnc(SMP_SESSINDEX_USERNAME));
$usuario->setFingerprint($fingp);
$usuario->setSession($session);

switch($action) {
    case NULL:
    case '':
    case SMP_WEB_ROOT:
        $page->setLocation(SMP_WEB_ROOT);
        // Ya se que da FALSE, es para que se entienda.
        // Location=NULL lleva a WEBROOT
        break;
    
    case SMP_HTTP_ERROR:
        $page->setLocation('errors.php');
        $params = [ SMP_HTTP_ERROR => $params ];
        break;
    
    case SMP_LOGOUT:
        $usuario->sesionFinalizar();
        $page->setLocation('login.php');
        $params = [ SMP_LOGOUT => '1' ];
        break;
    
    case SMP_RESTOREPWD:
          $page->setLocation('login.php');
//        $page->generateRandomToken();
//        $page->generateTimestamp();
//        $page->generateToken();
//        $session->store(SMP_SESSINDEX_PAGE_RANDOMTOKEN, $page->getRandomToken());
//        $session->store(SMP_SESSINDEX_PAGE_TIMESTAMP, $page->getTimestamp());
        
          $params = Sanitizar::glGET(Sanitizar::ALL);
//        $params[SMP_SESSINDEX_PAGE_TOKEN] = $page->getToken();
        break;
    
    case SMP_LOGIN:
        $page->setLocation('login.php');
        break;
           
    case SMP_LOC_USR . 'mensajes.php':
        $intLink = "tabR"; 
        // omito break para que ejecute default
    default:
        // si la página no existe, 404...
        if (Page::pageExists($action)) {
            // Si el usuario está loggeado, dirigirse a la pag solicitada con un
            // page token.
            // Si no esta loggeado, darán error las comprobaciones
            if ($usuario->sesionAutenticar()) {
                // Login OK
                // Page Token
                $page->generateRandomToken();
                $page->generateTimestamp();
                $page->setLocation($action);
                $page->generateToken();
                
                // Guardo Page RandTkn y Timestamp en SESSION                
                $session->store(SMP_SESSINDEX_PAGE_RANDOMTOKEN, 
                                                $page->getRandomToken());
                $session->store(SMP_SESSINDEX_PAGE_TIMESTAMP, 
                                                $page->getTimestamp());
                
                // Paso por GET el Page Token
                $params = [ SMP_SESSINDEX_PAGE_TOKEN => $page->getToken() ];
            } else {
                $page->setLocation('errors.php');
                $params = [ SMP_HTTP_ERROR => 403 ];
            }
        } else {
            // No existe la pagina
            $page->setLocation('errors.php');
            $params = [ SMP_HTTP_ERROR => 404 ];
        }
        break;
}

$page->go($params, $intLink);
exit();
