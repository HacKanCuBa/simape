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
 * Maneja la creación y autenticación de la Token de Sesión.
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.41
 */
trait SessionToken 
{
    use Token;
    
    protected $sessionToken;

    // __ SPECIALS
    
    // __ PRIV
    
    // __ PROT 
    /**
     * Determina si el Token de Sesión indicado es válido.
     * @param string $sessionToken Token de Sesión a validar.
     * @return boolean TRUE si es un Token de Sesión válido, FALSE si no.
     */
    protected static function isValid_sessiontoken($sessionToken)
    {
        return self::isValid_token($sessionToken);
    }
    
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
            // Para forzar una vida util durante sólo el mismo dia.
            // Si cambia el dia, el valor de la operacion cambiara.
            $time = $timestamp - (float) Timestamp::getToday();

            // Se utiliza Timestamp::getThisSeconds para fozar la vida útil máxima
            return Crypto::getHash(Crypto::getHash($time 
                        . $randToken 
                        . Timestamp::getThisSeconds(SMP_SESSIONKEY_LIFETIME) 
                        . $uid->getUIDHash()
                        . constant('SMP_SESSIONKEY_TKN')));
        } else {
            return FALSE;
        }        
    }
    // __ PUB
    /**
     * Almacena el UID del usuario, pasado como objeto UID.<br />
     * Se emplea tanto en la función de autenticación como en la de generación.
     * 
     * @param UID $uid UID del usuario
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    public function setUID(UID $newUID) 
    {
        return $this->t_setUID($newUID);
    }
    
    /**
     * Fija un Token aleatorio.  Se emplea en la función de autenticación.<br />
     * <b>IMPORTANTE</b>: NO emplearlo para generar un Token de Sesión nuevo!<br /> 
     * Usar el método getRandomToken() a este fin.
     * 
     * @see getRandomToken()
     * @param string $randToken Token aleatorio.
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    public function setRandomToken($randToken)
    {
        return $this->t_setRandomToken($randToken);
    }
	 
    /**
     * Fija el valor de Timestamp para la función de autenticación.<br />
     * <b>IMPORTANTE</b>: NO emplearlo para generar un Token de Sesión nuevo!<br />
     * Usar el método getTimestamp() a este fin.
     * 
     * @see getTimestamp()
     * @param float $timestamp Timestamp.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setTimestamp($timestamp)
    {
        return $this->t_setTimestamp($timestamp);
    }
    
    /**
     * Fija el valor del Token de Sesión que será autenticado.
     * 
     * @param string $sessionToken Token de Sesión.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setToken($sessionToken)
    {
        if ($this->isValid_sessionToken($sessionToken)) {
            $this->sessionToken = $sessionToken;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un Token aleatorio, que es el mismo que se emplea para armar
     * el Token de Sesión.
     * 
     * @see getToken()
     * @return string Token aleatorio.
     */
    public function getRandomToken()
    {
        return $this->t_getRandomToken();
    }
    
    /**
     * Devuelve el timestamp empleado para crear el Token de Sesión.
     * 
     * @return float Timestamp.
     */
    public function getTimestamp()
    {
        return $this->t_getTimestamp();
    }
    
    /**
     * Devuelve un Token de Sesión.
     * Debe llamarse primero a getRandomToken(), getTimestamp() y setUID().
     * 
     * @see getRandomToken()
     * @see getTimestamp()
     * @see setUID()
     * @param boolean $notStrict Si es TRUE, permite usar valores externos<br />
     * vía setRandomToken() y setTimestamp() para generar el Token de Sesión.<br />
     * FALSE por defecto.
     * @return mixed Token de Sesión, o FALSE en caso de error.
     */ 
    public function getToken($notStrict = FALSE)
    {
        if (isset($this->randToken) 
            && isset($this->timestamp)
            && isset($this->uid)
        ) {
            if ($notStrict) {
                return self::tokenMake($this->randToken, 
                                        $this->timestamp, 
                                        $this->uid);
            } else {
                if (isset($this->ownrandToken) 
                    && $this->ownrandToken
                    && isset($this->ownTimestamp)
                    && $this->ownTimestamp 
                ) {
                    return self::tokenMake($this->randToken, 
                                            $this->timestamp, 
                                            $this->uid);
                } 
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
            && isset($this->sessionToken)
            && ($this->sessionToken === $this->getToken(TRUE))
        ) {
            return TRUE;            
        }

        return FALSE;  
    }
}