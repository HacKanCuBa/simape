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
 * @version 0.6
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
     * Token especial de clase.
     * @var string
     */
    protected $token;
    
    /**
     * ID de la tabla Token en la DB.
     * @var int
     */
    protected $TokenId;
    
    /**
     * Tabla Token de la DB.
     * @var array
     */
    protected $tblToken;


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
     * Devuelve el identificador de tabla Token.
     * @return integer TokenId 
     */
    public function getTokenId ()
    {
        if (isset($this->TokenId)) {
            return $this->TokenId;
        }
        return 0;
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
    public function setTokenId($TokenId) 
    {
        if (DB::isValid_TblId($TokenId)) {
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
    
    /**
     * Recupera de la DB el ID de la tabla Token para el usuario dado, 
     * y lo almacena en el objeto.
     * 
     * @param string $username Nombre de usuario.
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */
    public function retrieve_fromDB_TokenId($username)
    {
        $db = new DB;
        return $this->setTokenId($db->retrieve_tableId('Usuario', 
                                                        'Nombre', 
                                                        's', 
                                                        $username));
    }
    
    /**
     * Recupera de la DB la tabla Token y la almacena en el objeto.  Debe 
     * haberse fijado el valor de TokenId previamente.
     * 
     * @see retrieve_fromDB_TokenId
     * @return boolean TRUE si se recuperó correctamente, FALSE si no.
     */
    public function retrieve_tblToken()
    {
        if(!empty($this->TokenId)) {
            $db = new DB;
            $tblToken = $db->retrieve_tblToken('Token', $this->TokenId);
            if (is_array($tblToken)) {
                $this->tblToken = $tblToken;
                return TRUE;
            }
        }
        return FALSE;
    }
}