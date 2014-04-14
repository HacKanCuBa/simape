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
 * $fingToken = fing->getToken();
 * ...
 * $otherfing = new Fingerprint($fingToken);
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
 * @version 0.5
 */
class Fingerprint
{	
    protected $fingerprintToken;

    // __ SPECIALS
    /**
     * Fija los valores de el Token aleatorio y el Token de Fingerprint.
     * @param string $fingerprintToken Token de Fingerprint.
     */
    public function __construct($fingerprintToken = NULL) 
    {
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
	if (!empty($fingerprintToken) 
            && is_string($fingerprintToken)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un Token de Fingerprint armado.
     * 
     * @return string Token de Fingerprint.
     */
    protected static function tokenMake()
    {           
        return Crypto::getHash(Sanitizar::glSERVER('HTTP_USER_AGENT')
                                . Sanitizar::glSERVER('REMOTE_ADDR')
                                . Sanitizar::glSERVER('HTTP_HOST')
                                . Sanitizar::glSERVER('HTTP_X_HTTP_PROTO')
                                . Sanitizar::glSERVER('HTTP_X_REAL_IP')
                                . Sanitizar::glSERVER('SERVER_PROTOCOL')
                                . SMP_FINGERPRINT_TKN
                                );
    }

    // __ PUB    
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
     * Autentica un Token de Figerprint.
     * 
     * @return boolean TRUE si el Token de Fingerprint es auténtico, 
     * FALSE si no.<br />
     */
    public function authenticateToken() 
    {      
        if ($this->fingerprintToken === $this->tokenMake()) {
            return TRUE;
        }

        return FALSE;
    }
}