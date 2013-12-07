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

function sessionkey_key_make($uid, $timestamp, $token) 
{
    /**
     * Arma una llave de sesión
     * 
     * @param string $uid UID del usuario, debería ser tipo UUID4.
     * @param integer $timestamp Número entero de la hora y fecha en Unix 
     * Timestamp.
     * @param string $token Token aleatorio.
     */
    
    // Para forzar una vida util durante solo el mismo dia
    // Si cambia el dia, el valor de la operacion cambiara
    $time = $timestamp - timestamp_get_today();
    
    // Se utiliza timestamp_get_thisSeconds para fozar la vida útil máxima
    return hash_get(hash_get($time 
                            . $token 
                            . timestamp_get_thisSeconds(constant('SESSIONKEY_LIFETIME')) 
                            . $uid 
                            . constant('SESSIONKEY_TKN')
    ));
}

function sessionkey_get_new($usuario)
{    
    /**
     * Devuelve una llave de sesión nueva, o NULL en caso de error.
     * 
     * @param string $usuario Nombre de usuario.
     * @return array Devuelve un array asociativo con las componentes de la 
     * llave de sesión, o NULL en caso de error.
     */
    
    $uid = db_auto_get_user_uid($usuario);
    
    if (!empty($uid)) {
        $timestamp = time();
        $token = get_random_token();
        $key = sessionkey_key_make($uid, $timestamp, $token);
        return array('tkn' => $token, 
                     'key' => $key, 
                     'timestamp' => $timestamp
        );
    } else {
        return NULL;
    }
}

function sessionkey_get_token($sessionkey)
{
    return sanitizar_str($sessionkey['tkn']);
}

function sessionkey_get_key($sessionkey)
{
    return sanitizar_str($sessionkey['key']);
}

function sessionkey_get_timestamp($sessionkey)
{
    return sanitizar_str($sessionkey['timestamp']);
}

function sessionkey_validate($usuario, $sessionkey) 
{
    /**
     * Valida la llave de sesion.  Devuelve TRUE si es válida, 
     * FALSE en cualquier otro caso.
     * 
     * @param string $usuario Nombre de usuario.
     * @param array $sessionkey Array de llave de sesión, como el devuelto 
     * por sessionkey_get_new().
     */
   
    $timestamp = sessionkey_get_timestamp($sessionkey);
    $now = time();
    
    if (!empty($sessionkey) 
        && !empty($usuario) 
        && ($now >= $timestamp) 
        && ($now < ($timestamp + constant('SESSIONKEY_LIFETIME')))
    ) { 
        $uid = db_auto_get_user_uid($usuario);

        if (!empty($uid)) {
            $token =  sessionkey_get_token($sessionkey);
            $key = sessionkey_key_make($uid, $timestamp, $token);
          
            if (sessionkey_get_key($sessionkey) == $key) {
                return TRUE;
            }
        }            
    }
    
    return FALSE;  
}

function sessionkey_set($new_sessionkey) 
{
    /**
     * Guarda una llave de sesión en la sesión.
     * Es un alias para session_set_sessionkey.
     * 
     * @param array $new_sessionkey Array de llave de sesión.
     */
    session_set_sessionkey($new_sessionkey);
}

function sessionkey_unset() 
{
    session_unset_sessionkey();
}
// --

define('FUNC_SESSIONKEY', TRUE);