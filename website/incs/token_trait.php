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
 * Trait de manejo de Tokens.
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.43
 */

trait Token
{
    /**
     * Random Token.
     * @var string
     */
    protected $randToken;
    
    /**
     * Timestamp.
     * @var float
     */
    protected $timestamp;
    
    /**
     * Objeto UID.
     * @var UID
     */
    protected $uid;
    
    /**
     * Token especial de clase.
     * @var string
     */
    protected $token;
    
    /**
     * ID de la tabla Token en la DB.
     * @var int
     */
    protected $TokenId;
    
    // __ PRIV
    /**
     * Fuerza la implementación de un método para armar el Token
     * especial.
     */
    abstract protected function tokenMake();
    
    // __ PROT
    /**
     * Determina si el Token indicado es válido.
     * 
     * @param string $token Token a validar.
     * @return boolean TRUE si es un Token válido, FALSE si no.
     */
    protected static function isValid_token($token)
    {
        if (!empty($token) 
            && is_string($token)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Determina si el valor indicado es un timestamp válido.
     * 
     * @param float $timestamp Timestamp a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    protected static function isValid_timestamp($timestamp)
    {
        if (!empty($timestamp)
            && (is_int($timestamp))
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Determina si un valor es un tipo UID válido.
     * 
     * @param string|UID $uid UID a validar, como string o como objeto.
     * @return boolean TRUE si es un UID válido, FALSE si no.
     */
    protected static function isValid_UID($uid)
    {
        if (!empty($uid) && 
            ((is_a($uid, 'UID') && !empty($uid->get())) || is_string($uid))
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Verifica si el TokenId es válido (entero no vacío).
     * 
     * @param int $TokenId TokenId a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    protected static function isValid_TokenID($TokenId)
    {
	if (!empty($TokenId) 
            && is_int($TokenId)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    // __PUB
    
    /**
     * Genera un nuevo token aleatorio y lo almacena en el objeto.
     */
    public function generateRandomToken()
    {
        $this->randToken = Crypto::getRandomTkn();
    }
    
    /**
     * Genera un nuevo timestamp y lo almacena en el objeto.
     */
    public function generateTimestamp()
    {
        $this->timestamp = time();
    }
    
    /**
     * Estructura del método para generar y almacenar el token especial.
     */
    abstract public function generateToken();

    /**
     * Devuelve el Token aleatorio almacenado, si existe.
     * 
     * @return string Token aleatorio o string vacío.
     */
    public function getRandomToken()
    {
        if (isset($this->randToken)) {
            return $this->randToken;
        }
        
        return '';
    }
    
    /**
     * Devuelve el timestamp almacenado o (float) 0 si no hay ninguno.
     * 
     * @return int Timestamp o (float) 0.
     */
    public function getTimestamp()
    {
        if (isset($this->timestamp)) {
            return $this->timestamp;
        }
        
        return 0;
    }
    
    /**
     * Devuelve el token almacenado o string vacío si no hay ninguno.
     * 
     * @return string Token.
     */
    public function getToken()
    {
        if(isset($this->token)) {
            return $this->token;
        }
        return '';
    }
    
    /**
     * Recupera de la DB el ID de la tabla Token para el usuario dado, 
     * y lo almacena en el objeto.
     * 
     * @param string $username Nombre de usuario.
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */
    public function retrieve_fromDB_TokenID($username)
    {
        $db = new DB;
        $db->setQuery('SELECT TokenId FROM Usuario WHERE Nombre = ?');
        $db->setBindParam('s');
        $db->setQueryParams($username);
        $db->queryExecute();

        return $this->setTokenID($db->getQueryData());
    }

    /**
     * Almacena en el objeto un Token aleatorio.  Emplearlo para la función
     * de autenticación.
     * 
     * @param string $randToken Token aleatorio.
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    public function setRandomToken($randToken)
    {
        if (self::isValid_token($randToken)) {
            $this->randToken = $randToken;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena en el objeto el valor de Timestamp.  Emplearlo para la función
     * de autenticación.
     * 
     * @param float $timestamp Timestamp.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setTimestamp($timestamp)
    {
        if (self::isValid_timestamp($timestamp)) {
            $this->timestamp = $timestamp;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena el UID del usuario.  Puede recibirlo como string o como objeto.
     * 
     * @param string|UID $uid UID del usuario, como string o como objeto.
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    public function setUID($uid)
    {
        if (self::isValid_UID($uid)) {
            if(is_string($uid)) {
                $this->uid = new UID();
                return $this->uid->set($uid);
            } else {
                $this->uid = $uid;
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Almacena un Token especial en el objeto.  Emplearlo para la función
     * de autenticación.
     * 
     * @param string $token Token.
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    public function setToken($token)
    {
        if(self::isValid_token($token)) {
            $this->token = $token;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Fija el valor del identificador de tabla Token de la DB.
     * 
     * @param int $TokenId Identificador de la tabla Token.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setTokenID($TokenId) 
    {
        if (self::isValid_TokenID($TokenId)) {
            $this->TokenId = $TokenId;
            return TRUE;
        }
        
        return FALSE;
    }

    /**
    * Fuerza la implementación de un método para autenticar el Token
    * especial de la clase implementadora.
    */
    abstract public function authenticateToken();
}