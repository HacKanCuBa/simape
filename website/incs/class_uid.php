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

require_once SMP_INC_ROOT . SMP_LOC_INCS . 'class_crypto.php';

/**
 * Maneja la creación y validación de UIDs.
 * 
 * Ejemplo de uso:
 * <pre><code>
 * if (UID::isValid($miUid)) {
 *  echo "es un UID valido";
 * } else {
 *  echo "NO es un UID valido";
 * }
 * $nuevoUID = UID::getRandomUID();
 * 
 * $uid = new UID;
 * $uid->makeUID();
 * $otroUID = $uid->getUID();
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.3
 */
class UID
{  
    private $uid;
    
    // Metods
    // __ SPECIALS
    /**
     * Recibe un string de UID y lo almacena si el mismo el válido.  Emplear
     * getUID() para determinar si se trató de un UID válido.
     * 
     * @see getUID()
     * @param string $uid UID a almacenar.
     */
    public function __construct($uid = NULL) 
    {
        $this->setUID($uid);
    }
    // __ PRIV
    
    // __ PROT
    /**
     * Valida un string y determina si se trata de un código UUID4.
     * 
     * @param string $uuid String a validar.
     * @return boolean TRUE si el string cumple los requisitos y es un código 
     * UUID4 válido, FALSE si no lo es.
     */
    protected static function isValid_uuid($uuid) 
    {
        if (!empty($uuid) && is_string($uuid)) {
            return (bool) preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-'
                                     . '[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', 
                                     $uuid);
        }

        return FALSE;
    }
    // __ PUB
    
    /**
     * Genera y almacena un UID aleatorio
     */
    public function makeUID()
    {
        $this->uid = $this->getRandomUID();
    }
    
    /**
     * Recibe un string de UID y lo almacena si es válido.
     * 
     * @param string $uid UID a almacenar.
     * @return boolean TRUE si el UID recibido es válido, FALSE si no.
     */
    public function setUID($uid)
    {
        if ($this->isValid($uid)) {
            $this->uid = $uid;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve el UID generado por makeUID() o almacenado por setUID(), 
     * o NULL.
     * 
     * @return string UID o NULL.
     */
    public function getUID()
    {
        if(!empty($this->uid)) {
            return $this->uid;
        }
        
        return NULL;
    }

    /**
     * Devuelve un UID aleatorio.
     * 
     * @return string Devuelve un UID aleatorio.
     */
    public static function getRandomUID() 
    {        
        //  https://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_.28random.29
        //  Version 4 UUIDs have the form xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx 
        //  where x is any hexadecimal digit and y is one of 8, 9, A, or B 
        //  (e.g., f47ac10b-58cc-4372-a567-0e02b2c3d479).
        return sprintf("%s-%s-4%s-%x%s-%s", Crypto::getRandomStr(8), 
                                            Crypto::getRandomStr(4), 
                                            Crypto::getRandomStr(3), 
                                            mt_rand(8, 11), 
                                            Crypto::getRandomStr(3), 
                                            Crypto::getRandomStr(12));
    }

    /**
     * Determina si el string recibido es un UID válido.
     * 
     * @param string $uid UID a validar.
     * @return boolean TRUE si se trata de un UID válido, FALSE si no.
     */
    public static function isValid($uid)
    {
        return self::isValid_uuid($uid);
    }
}