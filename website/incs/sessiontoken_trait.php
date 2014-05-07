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
 * Maneja la creación y autenticación del Token de Sesión.
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.6
 */
trait SessionToken 
{
    use Token;
    // __ SPECIALS
    
    // __ PRIV
    
    // __ PROT    
    /**
     * Devuelve un Token de Sesión armado.
     * 
     * @param string $randToken Token aleatorio.
     * @param float $timestamp Timestamp.
     * @param UID $uid UID del usuario.
     * @return mixed Token de Sesión o FALSE en caso de error.
     */
    protected static function tokenMake($randToken, $timestamp, UID $uid)
    {
        if (self::isValid_token($randToken) 
            && self::isValid_timestamp($timestamp)
            && self::isValid_UID($uid)
        ) {        
            // Esta operación siempre dará -1 cuando 
            // $timestamp < microtime < $timestamp + lifetime
            // Devolverá cualquier otro valor en otro caso.
            $time = intval(($timestamp - microtime(TRUE) 
                                - SMP_SESSIONKEY_LIFETIME) 
                                    / SMP_SESSIONKEY_LIFETIME);

            return Crypto::getHash($time 
                                    . $randToken 
                                    . $uid->getHash()
                                    . constant('SMP_TKN_SESSIONKEY'));
        } else {
            return FALSE;
        }        
    }
    // __ PUB
    /**
     * Genera y almacena un nuevo Token.  Requiere previamente del
     * Random Token, Timestamp y UID.
     * 
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */
    public function generateToken()
    {
        if(isset($this->randToken)
           && isset($this->timestamp) 
           && isset($this->uid)
        ) {
           $token = self::tokenMake($this->randToken,
                                    $this->timestamp,
                                    $this->uid); 
           if(self::isValid_sessionToken($token)) {
               $this->token = $token;
               return TRUE;
           }
        }
        
        return FALSE;
    }

    /**
     * Autentica el Token de Sesión.  Devuelve TRUE si es auténtico, 
     * FALSE en cualquier otro caso.
     * 
     * @return boolean TRUE si el Token de Sesión es auténtico, FALSE si no.
     */
    public function authenticateToken() 
    {
        $now = microtime(TRUE);

        if (isset($this->timestamp)
            && ($now >= $this->timestamp) 
            && ($now < ($this->timestamp + SMP_SESSIONKEY_LIFETIME))
            && isset($this->token)
            && isset($this->randToken)
        ) {
            // Verifico que tokenMake no sea false
            $sessToken = self::tokenMake($this->randToken,
                                         $this->timestamp,
                                         $this->uid);
            if ($sessToken && ($sessToken === $this->token)) {
                return TRUE;            
            }
        }

        return FALSE;  
    }
    
    /**
     * Determina si el Token de Sesión indicado es válido.
     * @param string $sessionToken Token de Sesión a validar.
     * @return boolean TRUE si es un Token de Sesión válido, FALSE si no.
     */
    public static function isValid_sessiontoken($sessionToken)
    {
        return self::isValid_token($sessionToken);
    }
    
    /**
     * Recupera el Random Token y el Timestamp almacenado en la DB y lo guarda 
     * en el objeto.  Usar los respectivos get... para obtener los valores.
     * 
     * @return boolean TRUE si tuvo exito, FALSE si no.
     * @see setTokenID
     */
    public function retrieve_fromDB() 
    {
        if (!empty($this->TokenId)) {
            $db = new DB;
            $db->setQuery('SELECT Session_RandomToken, Session_Timestamp '
                        . 'FROM Token WHERE TokenId = ?');
            $db->setBindParam('i');
            $db->setQueryParams($this->TokenId);
            if ($db->queryExecute()) {
                $tokens = $db->getQueryData();
                $this->setRandomToken($tokens[Session_RandomToken]);
                $this->setTimestamp($tokens[Session_Timestamp]);
                
                return TRUE;
            }            
        }
        
        return FALSE;
    }
    
    /**
     * Almacena en la DB el Random Token y el Timestamp guardados en el objeto.
     * <br />
     * Debe fijarse primero el identificador de tabla Token y los valores 
     * respectivos.
     * 
     * @see setTokenId
     * @see setRandomToken
     * @see generateToken
     * @see setTimestamp
     * @see generateTimestamp
     * @return boolean TRUE si se almacenó en la DB exitosamente, 
     * FALSE en caso contrario.
     */
    public function store_inDB() 
    {
        if (!empty($this->TokenId) 
            && !empty($this->randToken)
            && !empty($this->timestamp)
        ) {
            $db = new DB(TRUE);
            $db->setQuery('UPDATE Token '
                        . 'SET Session_RandomToken = ?, Session_Timestamp = ? '
                        . 'WHERE TokenId = ?');
            $db->setBindParam('ssi');
            $db->setQueryParams([$this->randToken, $this->timestamp, $this->TokenId]);
            //// atenti porque la func devuelve tb nro de error
            // ToDo: procesar nro de error
            $retval = $db->queryExecute();
            if (is_bool($retval)) {
                return $retval;
            }
        }
        
        return FALSE;
    }
}