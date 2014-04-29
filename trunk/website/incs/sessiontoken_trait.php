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
 * @version 0.51
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
            // Para forzar una vida util durante sólo el mismo dia.
            // Si cambia el dia, el valor de la operacion cambiara.
            $time = $timestamp - (float) Timestamp::getToday();

            // Se utiliza Timestamp::getThisSeconds para fozar la vida útil máxima
            return Crypto::getHash(Crypto::getHash($time 
                        . $randToken 
                        . Timestamp::getThisSeconds(SMP_SESSIONKEY_LIFETIME) 
                        . $uid->getHash()
                        . constant('SMP_SESSIONKEY_TKN')));
        } else {
            return FALSE;
        }        
    }
    // __ PUB
    /**
     * Genera y almacena un nuevo token de sesión.  Requiere previamente del
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
}