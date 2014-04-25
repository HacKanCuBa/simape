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
 * $nuevoUID = UID::getRandom();
 * 
 * $uid = new UID;
 * $uid->generate();
 * $otroUID = $uid->get();
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.5
 */
class UID
{  
    private $uid;
    
    // Metods
    // __ SPECIALS
    /**
     * Recibe un string de UID y lo almacena si el mismo el válido.  Emplear
     * get() para determinar si se trató de un UID válido.
     * 
     * @see get()
     * @param string $uid UID a almacenar.
     */
    public function __construct($uid = NULL) 
    {
        $this->set($uid);
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
    public function generate()
    {
        $this->uid = $this->getRandom();
    }
    
    /**
     * Recibe un string de UID y lo almacena si es válido.
     * 
     * @param string $uid UID a almacenar.
     * @return boolean TRUE si el UID recibido es válido, FALSE si no.
     */
    public function set($uid)
    {
        if ($this->isValid($uid)) {
            $this->uid = $uid;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena en la DB el UID guardado en el objeto, si lo hay.
     * 
     * @see generate
     * @see set
     * @param string $username Nombre de usuario al que le pertenece el UID.
     * @return boolean TRUE si se almacenó en la DB exitosamente, 
     * FALSE en caso contrario.
     */
    public function store_inDB(string $username) 
    {
        if (!empty($this->uid) && !empty($username)) {
            $db = new DB(TRUE);
            $db->setQuery('UPDATE Usuario SET UID = ? '
                        . 'WHERE Nombre = ?');
            $db->setBindParam('ss');
            $db->setQueryParams([$this->uid, $username]);
            //// atenti porque la func devuelve tb nro de error
            // ToDo: procesar nro de error
            $retval = $db->queryExecute();
            if (is_bool($retval)) {
                return $retval;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve el UID generado por generate o almacenado por set.
     * 
     * @see generate
     * @see set
     * @return string UID o string vacío.
     */
    public function get()
    {
        if(isset($this->uid)) {
            return $this->uid;
        }
        
        return '';
    }
      
    /**
     * Devuelve el Hash del UID almacenado, o string vacío si no hay ninguno 
     * almacenado.
     * 
     * @return string Hash del UID o string vacío.
     */
    public function getHash()
    {
        if(!empty($this->uid)) {
            return Crypto::getHash($this->uid);
        }
        
        return '';
    }

    /**
     * Devuelve un UID aleatorio.
     * 
     * @return string UID aleatorio.
     */
    public static function getRandom() 
    {        
        return Crypto::getUUIDv4();
    }
    
    /**
     * Recupera el UID del usuario indicado de la DB y lo almacena en el objeto.
     * Usar get para obtener el valor.
     * 
     * @see get
     * @param string $username Nombre de usuario.
     * @return boolean TRUE si se encontró y almacenó correctamente, 
     * FALSE si no. 
     */
    public function retrieve_fromDB($username)
    {
        if (!empty($username) && is_string($username)) {
            $db = new DB;
            
            $db->setQuery('SELECT UID FROM Usuario WHERE Nombre = ?');
            $db->setBindParam('s');
            $db->setQueryParams($username);
            $db->queryExecute();
            return $this->set($db->getQueryData());
        }
        
        return FALSE;
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