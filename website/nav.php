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
 * Todas las paginas deben llamar a ésta y ésta redireccionara adecuadamente.
 */

if (!defined('CONFIG')) { require_once 'loadconfig.php'; }
    
session_do();

if (fingerprint_token_validate() &&
        sessionkey_validate(session_get_username(), session_get_sessionkey())) {
    // Login OK 
    // Realizar navegacion...
    // 
    // FIXME
    // mejorar esto: cambiar la navegacion por paginas
    session_unset_data(); // debo limpiar en caso q el usuario halla llegado x la nav bar
    //
    
    $params = "pagetkn=" . page_token_get_new();
    switch(get_get_action()) {
        case 'logout':
            session_unset_sessionkey();
            $params = NULL;
            $redirect = NULL;
            break;

        case 'perfilusr':
            $redirect = LOC_USUARIO;
            break;

        case 'perfilemp':
            $redirect = LOC_EMPLEADO;
            break;

        case 'mensajes':
        default:
            $redirect = LOC_MSGS;
            $params .= "#tabR";
            break;
    }
}
else
{
    // Error de autenticacion
    //
    session_terminate();
    session_do();
    session_set_errt($err_authfail);
    $redirect = LOC_LOGIN;  
    $params = NULL;
}

page_goto($redirect, $params);
exit();
?>