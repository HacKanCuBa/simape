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
 * @version 0.64
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
     * Devuelve un Token especial armado.
     * 
     * @param string $randToken Token aleatorio.
     * @param string $constToken Token constante.
     * @param int $timestamp Timestamp.
     * @param int $lifetime Tiempo de vida del token.
     * @param string $extra Información adicional para generar el token.
     * @return string|FALSE Token especial o FALSE en caso de error.
     */
    protected static function tokenMake($randToken, 
                                        $constToken, 
                                        $timestamp = NULL, 
                                        $lifetime = NULL,
                                        $extra = NULL)
    {
        if (self::isValid_token($randToken) 
            && self::isValid_token($constToken)
            && (is_null($timestamp) || self::isValid_timestamp($timestamp)) 
            && (is_null($lifetime) || self::isValid_timestamp($lifetime))
            && (is_null($extra) || is_string($extra))
        ) {  
            $time = 0;
            if (!is_null($timestamp) && !is_null($lifetime)) {
                // Esta operación siempre dará -1 cuando 
                // $timestamp < time < $timestamp + lifetime
                // Devolverá cualquier otro valor en otro caso.
                $time = intval(($timestamp - time() - $lifetime) / $lifetime);
            }

            return Crypto::getHash($time 
                                    . $randToken 
                                    . $extra
                                    . $constToken, 1);
        }
        
        return FALSE;
    }
    
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
     * Genera todos los tokens
     */
    public function generate()
    {
        $this->generateRandomToken();
        $this->generateTimestamp();
        method_exists($this, 'generateUID') ? $this->{'generateUID'}() : NULL;
        $this->generateToken();
    }

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
     * Almacena en el objeto el valor de Timestamp.  
     * Si el valor pasado no es entero, almacenará su valor equivalente.  
     * Emplearlo para la función de autenticación.
     * 
     * @param int $timestamp Timestamp.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = intval($timestamp);
        return TRUE;
//        if (self::isValid_timestamp($timestamp)) {
//            $this->timestamp = $timestamp;
//            return TRUE;
//        }
//        
//        return FALSE;
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
     * Fija los valores pasados a fin de prepararlos para la función de 
     * autenticación.  Idem a llamar cada set() individual.
     * 
     * @param string $token Token.
     * @param string $randToken RanomToken.
     * @param int $timestamp Timestamp.
     * @param string $uid UID.
     */
    public function prepare_to_auth($token = NULL,
                                    $randToken = NULL,
                                    $timestamp = NULL,
                                    $uid = NULL)
    {
        $this->setRandomToken($randToken);
        $this->setToken($token);
        $this->setTimestamp($timestamp);
        method_exists($this, 'setUID') ? $this->{'setUID'}($uid) : NULL;
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
            $tblToken = $db->retrieve_table('Token', $this->TokenId);
            if (is_array($tblToken)) {
                $this->tblToken = $tblToken;
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Crea una nueva tabla Token vacía en la DB, y almacena el ID de ésta.
     * 
     * @return boolean TRUE si tuvo éxito, o FALSE en caso de error.
     */
    public function table_new_Token()
    {
        $db = new DB(TRUE);
        return $this->setTokenId($db->insert('Token'));
    }
}