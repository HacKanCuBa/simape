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
 * @version 0.32 untested
 */

trait Token
{
    protected $randToken, $timestamp, $uid;
    protected $ownrandToken = FALSE, $ownTimestamp = FALSE;
    
    // __ PRIV
    
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
            && (is_float($timestamp))
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Determina si un valor es un tipo UID válido.
     * 
     * @param UID $uid UID a validar.
     * @return boolean TRUE si es un UID válido, FALSE si no.
     */
    protected static function isValid_UID(UID $uid)
    {
        if (!empty($uid) && is_a($uid, 'UID') && !empty($uid->get())) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un Token aleatorio
     * 
     * @return string Token aleatorio.
     */
    protected function t_getRandomToken()
    {
        $this->randToken = Crypto::getRandomTkn();
        $this->ownrandToken = TRUE;
        return $this->randToken;
    }
    
    /**
     * Devuelve el timestamp
     * 
     * @return float Timestamp.
     */
    protected function t_getTimestamp()
    {
        $this->timestamp = microtime(TRUE);
        $this->ownTimestamp = TRUE;
        return $this->timestamp;
    }
	
    /**
     * Fija un Token aleatorio.
     * 
     * @param string $randToken Token aleatorio.
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    protected function t_setRandomToken($randToken)
    {
        if (self::isValid_token($randToken)) {
            $this->randToken = $randToken;
            $this->ownrandToken = FALSE;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Fija el valor de Timestamp.
     * 
     * @param float $timestamp Timestamp.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    protected function t_setTimestamp($timestamp)
    {
        if (self::isValid_timestamp($timestamp)) {
            $this->timestamp = $timestamp;
            $this->ownTimestamp = FALSE;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena el UID del usuario, pasado como objeto UID.<br />
     * 
     * @param UID $uid UID del usuario
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    protected function t_setUID($uid)
    {
        if (self::isValid_UID($uid)) {
            $this->uid = $uid;
            return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Fuerza la implementación de un método para obtener el Token
     * especial armado.
     */
    abstract protected function tokenMake();
	
    // __ PUB
    /**
     * Fuerza la implementación de un método para obtener el Token
     * aleatorio donde esté documentado el procedimiento.
     */
    abstract public function getRandomToken();
    
    /**
     * Fuerza la implementación de un método para obtener el Timestamp
     * donde esté documentado el procedimiento.
     */
    //abstract public function getTimestamp();
    
    /**
     * Fuerza la implementación de un método para obtener el Token
     * especial de la clase implementadora.
     * 
     * @param boolean $notStrict Si es TRUE, permite usar valores externos vía<br />
     * getRandomToken() y getTimestamp() para generar el Token especial.<br />
     * FALSE por defecto.
     */
     abstract public function getToken($notStrict = FALSE);
	
    /**
     * Fuerza la implementación de un método para fijar el Token
     * aleatorio donde esté documentado el procedimiento.
     * 
     * @param string $randToken Token aleatorio.
     */
    abstract public function setRandomToken($randToken);
    
    /**
     * Fuerza la implementación de un método para fijar el Timestamp
     * donde esté documentado el procedimiento.
     * 
     * @param float $timestamp Timestamp.
     */
    //abstract public function setTimestamp($timestamp);
	
    /**
    * Fuerza la implementación de un método para fijar el Token
    * especial de la clase implementadora.
    */
    abstract public function setToken();

    /**
    * Fuerza la implementación de un método para autenticar el Token
    * especial de la clase implementadora.
    */
    abstract public function authenticateToken();
}