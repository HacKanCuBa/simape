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

function form_token_get_new() 
{
    /**
     * Devuelve un array conteniendo un token aleatorio y un form token
     * que debe ser guardado en la sesion
     * 
     * @param void
     * @return array 'randtoken' => token aleatorio
     *               'formtoken' => token a ser almacenado en la sesion
     */
    $randtoken = hash_get(get_random_token());
    $formtoken = form_token_make($randtoken);
    
    return array('randtoken' => $randtoken,
                 'formtoken' => $formtoken
    );
}

function form_token_validate($current_token) 
{
    /**
     * Valida un token de formulario recibido contra el que se encuentra 
     * almacenado en la sesión.  Devuelve TRUE si son idénticos, FALSE si no
     * lo son y NULL en caso de que alguno sea VACIO.
     */
    
    $form_token_session = session_get_frmtkn();
    if (empty($current_token) || empty($form_token_session)) {
        return NULL;
    } elseif (form_token_make($current_token) === $form_token_session) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function form_token_make($randtkn) 
{
    return hash_get(timestamp_get_thisHours(1) 
                    . $randtkn 
                    . constant('__SMP_FORM_TKN')
    );
}

function form_token_get_randtkn($form_token) 
{
    return sanitizar_str($form_token['randtoken']);
}

function form_token_get_formtkn($form_token) 
{
    return sanitizar_str($form_token['formtoken']);
}

// --
define('FUNC_FORM', TRUE);