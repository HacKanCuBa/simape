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
 * @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 * 
 *****************************************************************************/

function fingerprint_token_get() 
{
    /**
     * Devuelve un string (token) que representa al navegador del usuario, y 
     * será Valido solo durante el dia de la fecha en la que fue creado.
     * 
     * @param void
     * @return string Fingerprint token
     */ 
    return hash_get($_SERVER['HTTP_USER_AGENT']
                    . constant('FINGERPRINT_TKN')
                    . $_SERVER['REMOTE_ADDR']
                    . timestamp_get_today()
    );
}

function fingerprint_token_validate($token_to_validate = NULL) 
{
    /**
     * Valida el token recibido con el fingerprint. 
     * Si no se le pasa un token, valida respecto del almacenado
     * en la sesión.
     * Devuelve TRUE si son IDÉNTICOS, o FALSE si no lo son.
     * Si el token almacenado o recibido es NULL, devuelve NULL.
     * 
     * @param string $token_to_validate [opcional] Token a ser validado
     * @return bool TRUE si son IDÉNTICOS, NULL si el recibido o almacenado
     * en sesión es NULL, FALSE en otro caso.
     * 
     */
    
    if (empty($token_to_validate)) {
        $token_to_validate = session_get_fingerprinttkn();
    }
    
    if (empty($token_to_validate)) {
        return NULL;
    } elseif ($token_to_validate === fingerprint_token_get()) {
        return TRUE;
    } else {
        return FALSE;
    }
}

// --

define('FUNC_FINGERPRINT', TRUE);