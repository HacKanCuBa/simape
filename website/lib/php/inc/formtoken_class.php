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
 * @version 0.32
 */

class FormToken 
{
    use Token;
    
    /**
     * Tiempo de vida de un Form Token.
     */
    const FORMTOKEN_LIFETIME = 1800;

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
    
    // __ PUB
    /**
     * Genera y almacena en el objeto un nuevo Form Token.  Requiere previamente
     * del Random Token y Timestamp.
     * 
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */
    public function generateToken()
    {
        if (!empty($this->randToken)
            && !empty($this->timestamp)
        ) {
            $token = self::tokenMake($this->randToken, 
                                        SMP_TKN_FORM,
                                        $this->timestamp,
                                        self::FORMTOKEN_LIFETIME);
            return $this->setToken($token);
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
        $now = time();

        if (isset($this->randToken)
            && isset($this->token)
            && isset($this->timestamp)
            && ($now >= $this->timestamp) 
            && ($now < ($this->timestamp + self::FORMTOKEN_LIFETIME))
        ) {
            // Verifico que tokenMake no sea FALSE.
            $formToken = self::tokenMake($this->randToken, 
                                        SMP_TKN_FORM,
                                        $this->timestamp,
                                        self::FORMTOKEN_LIFETIME);
            if ($formToken && ($this->token === $formToken)) {
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