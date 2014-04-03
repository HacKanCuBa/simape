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
 * Maneja la creación y autenticación del fingerprint
 * 
 * Ejemplo de uso:
 * <pre><code>
 * $fing = new Fingerprint;
 * $randToken = fing->getRandomToken();
 * $fingToken = fing->getToken();
 * ...
 * $otherfing = new Fingerprint($randToken, $fingToken);
 * if ($otherfing->authenticateToken()) {
 *      echo "Token de Fingerprint auténtico!";
 * } else {
 *      echo "Token de Fingerprint NO es auténtico";
 * }
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.43
 */
class Fingerprint
{
    use Token;
	
    protected $fingerprintToken;

    // __ SPECIALS
    /**
     * Fija los valores de el Token aleatorio y el Token de Fingerprint.
     * @param string $randToken Token aleatorio.
     * @param string $fingerprintToken Token de Fingerprint.
     */
    public function __construct($randToken = NULL,  
                                 $fingerprintToken = NULL) 
    {
        $this->setRandomToken($randToken);
        $this->setToken($fingerprintToken);
    }
    // __ PRIV
    
    // __ PROT
    /**
     * Verifica si el Token de Fingerprint es válido.
     * 
     * @param type $fingerprintToken Token a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    protected static function isValid_fingerprintToken($fingerprintToken)
    {
		// No difiere de un token estandard
        return self::isValid_token($fingerprintToken);
    }
    
    /**
     * Devuelve un Token de Fingerprint armado.
     * 
     * @param string $randToken Token aleatorio.
     * @return mixed Token de Fingerprint o FALSE en caso de error.
     */
    protected static function tokenMake($randToken)
    {
        if (self::isValid_token($randToken) 
        ) {            
            return Crypto::getHash(Sanitizar::glSERVER('HTTP_USER_AGENT')
                                    . Sanitizar::glSERVER('REMOTE_ADDR')
                                    . SMP_FINGERPRINT_TKN
                                    . $randToken
            );
        }
        
        return FALSE;
    }

    // __ PUB    
    /**
     * Devuelve un Token aleatorio, que es el mismo que se emplea para armar
     * el Token de Fingerprint.
     * 
     * @see getToken()
     * @return string Token aleatorio.
     */
    public function getRandomToken()
    {
        return $this->t_getRandomToken();
    }
    
    /**
     * Devuelve un Token que representa al usuario (navegador, IP, etc...).
     * Debe llamarse primero a getRandomToken().
     * 
     * @see getRandomToken()
     * @param boolean $notStrict Si es TRUE, permite usar valores externos vía<br />
     * setRandomToken() para generar el Token de Fingerprint.<br />
     * FALSE por defecto.
     * @return mixed Fingerprint Token, o FALSE en caso de error.
     */ 
    public function getToken($notStrict = FALSE)
    {
        if (isset($this->randToken)) {
            if ($notStrict) {
                return $this->tokenMake($this->randToken);
            } else {
                if (isset($this->ownrandToken) && $this->ownrandToken) {
                    return $this->tokenMake($this->randToken);
                } 
            }
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve el timestamp empleado para crear el Token de Fingerprint.
     * 
     * @return float Timestamp.
     */
//    public function getTimestamp() 
//    {
//        return $this->t_getTimestamp();
//    }


    /**
     * Fija un Token aleatorio.  Se emplea en la función de autenticación.<br />
     * <b>IMPORTANTE</b>: NO emplearlo para generar un Token de página nuevo!<br /> 
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
     * Fija el valor del Token de Fingerprint que será autenticado.
     * 
     * @param string $fingerprintToken Token de Fingerprint.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setToken($fingerprintToken)
    {
        if (self::isValid_fingerprintToken($fingerprintToken)) {
            $this->fingerprintToken = $fingerprintToken;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Fija el valor de Timestamp para la función de autenticación.<br />
     * <b>IMPORTANTE</b>: NO emplearlo para generar un Token de Fingeprint
     * nuevo!<br />
     * Usar el método getTimestamp() a este fin.
     * 
     * @see getTimestamp()
     * @param float $timestamp Timestamp.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
//    public function setTimestamp($timestamp) 
//    {
//        $this->t_setTimestamp($timestamp);
//    }

    /**
     * Autentica un Token de Figerprint.<br />
     * Debe fijarse primero el Token aleatorio con el que fue creado el 
     * Token de Fingerprint a autenticar.
     * 
     * @see setRandomToken()
     * @return boolean TRUE si el Token de Fingerprint es auténtico, 
     * FALSE si no.<br />
     * Si algún Token es nulo, devuelve NULL.
     */
    public function authenticateToken() 
    {      
        if (empty($this->fingerprintToken) 
            || empty($this->randToken) 
        ) {
            return NULL;
        } else {
            // Verifico que getToken no sea FALSE.
            $fingerprintToken = $this->getToken(TRUE);
            if ($fingerprintToken 
                && ($this->fingerprintToken === $fingerprintToken)
            ) {
                return TRUE;
            }
        }

        return FALSE;
    }
}