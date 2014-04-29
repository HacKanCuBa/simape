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
 * Maneja la creación y autenticación del Token de Formulario.
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.3
 */

class FormToken 
{
    use Token;
    
    /**
     * Tiempo de vida de un Form Token.
     */
    const SMP_FORMTOKEN_LIFETIME = 1800;

    // __ SPECIALS
    /**
     * Objeto Form Token.  Recibe los valores para emplearlos en el método
     * de autenticación.
     * 
     * @param string $token Form Token.
     * @param string $randToken Random Token.
     * @param float $timestamp Timestamp.
     * @see authenticateToken
     */
    public function __construct($token = NULL,
                                $randToken = NULL, 
                                $timestamp = NULL
    ){
        $this->setToken($token);
        $this->setRandomToken($randToken);
        $this->setTimestamp($timestamp);
    }
    
    // __ PRIV
    
    // __ PROT
    /**
     * Devuelve un Token de Formulario armado.
     * 
     * @param string $randToken Token aleatorio.
     * @param float $timestamp Timestamp.
     * @return mixed Token de Formulario o FALSE en caso de error.
     */
    protected static function tokenMake($randToken, $timestamp)
    {
        if (self::isValid_token($randToken) 
            && self::isValid_timestamp($timestamp)
        ) {        
            // Para forzar una vida util durante sólo el mismo dia.
            // Si cambia el dia, el valor de la operacion cambiara.
            $time = $timestamp - (float) Timestamp::getToday();

            // Se utiliza Timestamp::getThisSeconds para fozar la vida útil máxima
            return Crypto::getHash(Crypto::getHash($time 
                        . $randToken 
                        . Timestamp::getThisSeconds(self::SMP_FORMTOKEN_LIFETIME) 
                        . SMP_FORM_TKN));
        } else {
            return FALSE;
        }        
    }
    
    // __ PUB
    /**
     * Genera y almacena en el objeto un nuevo Form Token.  Requiere previamente
     * del Random Token y Timestamp.
     * 
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */
    public function generateToken()
    {
        if (isset($this->randToken)
            && isset($this->timestamp)
        ) {
            $token = self::tokenMake($this->randToken, $this->timestamp);
            if(self::isValid_formToken($token)) {
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

        if (isset($this->randToken)
            && isset($this->token)
            && isset($this->timestamp)
            && ($now >= $this->timestamp) 
            && ($now < ($this->timestamp + self::SMP_FORMTOKEN_LIFETIME))
        ) {
            // Verifico que getToken no sea FALSE.
            $formToken = self::tokenMake($this->randToken, $this->timestamp);
            if ($formToken && ($this->formToken === $formToken)) {
                return TRUE;
            }
        }

        return FALSE;  
    }
    
    /**
     * Determina si el string indicado es un Token de Formulario válido.
     * 
     * @param string $formToken Token de Formulario a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    public static function isValid_formToken($formToken)
    {
        return self::isValid_token($formToken);
    }
}