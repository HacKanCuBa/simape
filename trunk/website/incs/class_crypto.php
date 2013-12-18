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

/**
 * Esta clase comprende funciones criptográficas o del estilo.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.7
 */

class Crypto
{  
    // __ SPECIALS
        
    // __ PRIV
    
    // __ PROT
    
    // __ PUB
    /**
     * Devuelve el hash de un string
     * 
     * @param string $string String
     * @return string El hash del string indicado, o FALSE en caso de error.
     */
    public static function getHash($string) 
    {
        if (is_string($string)) {
            return hash('sha512', $string, FALSE);
        } else {
            return FALSE;
        }
    }

    /**
     * Devuelve un string de bytes aleatorios de la longitud indicada.
     * 
     * @param int $lenght Longitud del string
     * @return string String de bytes aleatorios de la longitud indicada, 
     * o FALSE en caso de error.
     */
    public static function getRandomBytes($lenght) 
    {
        if (!empty($lenght) && is_int($lenght)) {
            return openssl_random_pseudo_bytes($lenght);
        } else {
            return FALSE;
        }
    }
    
    /**
     * Devuelve un string de caracteres hexadecimales aleatorio de la 
     * longitud indicada.
     * 
     * @param int $lenght Longitud del string
     * @return string String aleatorio de la longitud indicada,
     * o FALSE en caso de error.
     */
    public static function getRandomStr($lenght)
    {
        if (!empty($lenght) && is_int($lenght)) {
            return substr(bin2hex(self::getRandomBytes((int) ($lenght / 2) + 1)), 
                          0, $lenght);
        }
        
        return FALSE;
    }

    /**
     * Devuelve un token aleatorio.
     *
     * @return string Token aleatorio como string de caracteres hexadecimales.
     * 
     */
    public static function getRandomTkn() 
    {        
        return self::Hash(self::Hash(self::getRandomBytes(128)));
    }
}
