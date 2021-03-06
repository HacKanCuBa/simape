<?php

/*****************************************************************************
 *  Este archivo forma parte de SiMaPe
 *  Sistema Integrado de Manejo de Personal
 *  Copyright (C) <2013, 2014>  <Ivan Ariel Barrera Oro>
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
 * Página de inicio para el usuario loggeado.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.12
 */

require_once 'load.php';

$session = new Session;
// -- VARS & CONST

// -- --
// -- CODE
$session->useSystemPassword();
$db = new DB(SMP_DB_CHARSET);
$fingp = new Fingerprint();
$usuario = new Usuario($db, $session->retrieveEnc(SMP_SESSINDEX_USERNAME));
$usuario->setFingerprint($fingp);
$usuario->setSession($session);

$page = new Page(SMP_LOC_USR . 'index.php', 
                 Session::retrieve(SMP_SESSINDEX_PAGE_RANDOMTOKEN), 
                 Session::retrieve(SMP_SESSINDEX_PAGE_TIMESTAMP), 
                 Sanitizar::glGET(SMP_GETINDEX_PAGE_TOKEN));

if ($page->authenticateToken() 
        && $usuario->sesionAutenticar()
) {
    $nav = SMP_LOC_USR . 'saper.php';
    $formToken = new FormToken;
    $formToken->prepare_to_auth(
                        Sanitizar::glPOST(SMP_SESSINDEX_FORM_TOKEN), 
                        Session::retrieve(SMP_SESSINDEX_FORM_RANDOMTOKEN), 
                        Session::retrieve(SMP_SESSINDEX_FORM_TIMESTAMP)
    );
    
    if ($formToken->authenticateToken()) {
        // Procesar POST
        
    }
} else {
    // Acceso denegado
    $usuario->sesionFinalizar();
    $nav = SMP_HTTP_ERROR;
    $params = 403;
}

isset($nav) ? $page->nav($nav, isset($params) ? $params : NULL) : NULL;

// Token de pagina
$page->setLocation(SMP_LOC_USR . 'index.php');
$page->generate();
Session::store(SMP_SESSINDEX_PAGE_RANDOMTOKEN, 
                    $page->getRandomToken());
Session::store(SMP_SESSINDEX_PAGE_TIMESTAMP, 
                    $page->getTimestamp());

// Token de formulario
$formToken->generateRandomToken();
$formToken->generateTimestamp();
$formToken->generateToken();
Session::store(SMP_SESSINDEX_FORM_RANDOMTOKEN, $formToken->getRandomToken());
Session::store(SMP_SESSINDEX_FORM_TIMESTAMP, $formToken->getTimestamp());
// -- --
// -- PAGE
Page::printHead('SiMaPe - Inicio', ['main', 'input', 'navbar']);
Page::printBody();
Page::printHeader();
Page::printHeaderClose();
Page::printDefaultNavbarVertical('');
Page::printMain();



Page::printMainClose();
Page::printFooter();
Page::printBodyClose();