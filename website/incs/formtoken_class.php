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
 * @version 0.2
 */

class FormToken 
{
    use Token;
    
    const SMP_FORMTOKEN_LIFETIME = 1800;
    
    protected $formToken;

    // __ SPECIALS
    
    // __ PRIV
    
    // __ PROT
    /**
     * Determina si el string indicado es un Token de Formulario válido.
     * 
     * @param string $formToken Token de Formulario a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    protected static function isValid_formToken($formToken)
    {
        return self::isValid_token($formToken);
    }

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
     * Fija un Token aleatorio.  Se emplea en la función de autenticación.<br />
     * <b>IMPORTANTE</b>: NO emplearlo para generar un Token de 
     * Formulario nuevo!<br /> 
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
     * <b>IMPORTANTE</b>: NO emplearlo para generar un Token de 
     * Formulario nuevo!<br />
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
     * Fija el valor del Token de Formulario que será autenticado.
     * 
     * @param string $formToken Token de Fromulario.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setToken($formToken)
    {
        if ($this->isValid_formToken($formToken)) {
            $this->formToken = $formToken;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un Token aleatorio, que es el mismo que se emplea para armar
     * el Token de Formulario.
     * 
     * @see getToken()
     * @return string Token aleatorio.
     */
    public function getRandomToken()
    {
        return $this->t_getRandomToken();
    }
    
    /**
     * Devuelve el timestamp empleado para crear el Token de Formulario.
     * 
     * @return float Timestamp.
     */
    public function getTimestamp()
    {
        return $this->t_getTimestamp();
    }
    
    /**
     * Devuelve un Token de Formulario.
     * Debe llamarse primero a getRandomToken() y getTimestamp().
     * 
     * @see getRandomToken()
     * @see getTimestamp()
     * @param boolean $notStrict Si es TRUE, permite usar valores 
     * externos<br />
     * vía setRandomToken() y setTimestamp() para generar el Token de 
     * Formulario.<br />
     * FALSE por defecto.
     * @return mixed Token de Formulario, o FALSE en caso de error.
     */ 
    public function getToken($notStrict = FALSE)
    {
        if (isset($this->randToken) 
            && isset($this->timestamp)
        ) {
            if ($notStrict) {
                return self::tokenMake($this->randToken, 
                                        $this->timestamp);
            } else {
                if (isset($this->ownrandToken) 
                    && $this->ownrandToken
                    && isset($this->ownTimestamp)
                    && $this->ownTimestamp 
                ) {
                    return self::tokenMake($this->randToken, 
                                            $this->timestamp);
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
            && ($now < ($this->timestamp + self::SMP_FORMTOKEN_LIFETIME))
            && isset($this->formToken)
        ) {
            // Verifico que getToken no sea FALSE.
            $formToken = $this->getToken(TRUE);
            if ($formToken && ($this->formToken === $formToken)) {
                return TRUE;
            }
        }

        return FALSE;  
    }
}