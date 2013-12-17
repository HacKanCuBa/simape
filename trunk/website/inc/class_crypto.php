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
 * @version 0.6
 */

class Crypto
{
    protected $Value;
    
    // __ SPECIALS
    // Esta es una clase extensible, conviene no emplear constructores
    
    // __ PRIV
    
    // __ PROT
    protected function Hash($string) 
    {
        if (is_string($string)) {
            return hash('sha512', $string, FALSE);
        }
    }

    // __ PUB
    public function setValue($Value = NULL) 
    {
        if (is_string($Value)) {
            $this->Value = $Value;
            return TRUE;
        }
        
        return FALSE;
    }

    public function getHash($string = NULL) 
    {
        if (isset($this->Value)) {
            return $this->Hash($this->Value);
        } elseif (is_string($string)) {
            return self::Hash($string);
        }
        
        return NULL;
    }

    /**
     * Devuelve un string de bytes aleatorios de la longitud indicada.
     * 
     * @param int $lenght Longitud del string
     * @return string String de bytes aleatorios de la longitud indicada.
     */
    public function getRandomBytes($lenght) 
    {
        if (!empty($lenght) && is_int($lenght)) {
            return openssl_random_pseudo_bytes($lenght);
        } else {
            return NULL;
        }
    }
    
    /**
     * Devuelve un string de caracteres hexadecimales aleatorio de la 
     * longitud indicada.
     * 
     * @param int $lenght Longitud del string
     * @return string String aleatorio de la longitud indicada.
     */
    public function getRandomStr($lenght)
    {
        if (!empty($lenght) && is_int($lenght)) {
            return substr(bin2hex(self::getRandomBytes((int) ($lenght / 2) + 1)), 
                          0, $lenght);
        }
        
        return NULL;
    }

    /**
     * Devuelve un token aleatorio de la longitud especificada, 
     * o del largo completo si no se especifica nada.
     * 
     * @param integer $lenght Longitud del token
     * @return string Token aleatorio como caracteres hexadecimales
     * 
     */
    public function getRandomTkn() 
    {        
        return self::Hash(self::Hash(self::getRandomBytes(128)));
    }

    /**
     * Devuelve un UID, que es un UUIDv4.
     * 
     * @return string Devuelve un UUIDv4.
     */
    function getUID() 
    {        
        //  https://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_.28random.29
        //  Version 4 UUIDs have the form xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx 
        //  where x is any hexadecimal digit and y is one of 8, 9, A, or B 
        //  (e.g., f47ac10b-58cc-4372-a567-0e02b2c3d479).
        return sprintf("%s-%s-4%s-%x%s-%s", self::getRandomStr(8), 
                                            self::getRandomStr(4), 
                                            self::getRandomStr(3), 
                                            mt_rand(8, 11), 
                                            self::getRandomStr(3), 
                                            self::getRandomStr(12));
    }
}
