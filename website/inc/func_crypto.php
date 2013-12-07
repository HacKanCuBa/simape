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

// -- Crypto
function hash_get($str) 
{
    return hash('sha512', $str, FALSE);
}

function password_get($plaintext) 
{
    /**
     * Devuelve un string que debe ser usado como contraseña para el sistema
     * de login.
     * 
     * @param string $plaintext Contraseña en texto plano que será encriptada.
     * @return string Contraseña encriptada.
     */
    
    // NOTA: no aumentar cost despues de 15 porque se hace muy lento!
    $options = array('cost' => 15);
    return password_hash($plaintext, PASSWORD_BCRYPT, $options);
}

function password_validate($plaintext, $password) 
{
    /** 
     * Esta función valida una contraseña en texto plano contra una encriptada-
     * Debe usarse para validar el login.
     * NOTA: A fin de evitar en cierta medida un ataque de timing oracle,
     * esta función implementa un restraso cuando algún parámetro es nulo.
     * 
     * @param string $plaintext Contraseña en texto plano que será validada.
     * @param string $password Contraseña encriptada que se usará para validar.
     * @return boolean TRUE si la contraseña es válida (idéntica a la 
     * encriptada), FALSE en caso contrario.
     */
    if (empty($plaintext) || empty($password)) {
        // Lamentablemente, password_verify se detiene si
        // alguno es empty, retornando con NULL y habilitando
        // un timing oracle...
        sleep(3);
        return FALSE;
    } else {
        return password_verify($plaintext, $password);
    }
}

function get_random_token ($lenght = NULL) 
{
    /**
     *  Devuelve un token aleatorio de la longitud especificada, 
     * o del largo completo si no se especifica nada.
     * 
     * @param integer $lenght Longitud del token
     * @return string Token aleatorio como caracteres hexadecimales
     * 
     */
    
    $token = hash_get(hash('sha512', openssl_random_pseudo_bytes(64), FALSE));
    if (empty($lenght)) {
        return $token;
    } else {
        return substr($token, 0, $lenght);
    }
}
// --

define('FUNC_CRYPTO', TRUE);