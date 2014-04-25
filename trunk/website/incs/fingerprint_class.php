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
 * $fing->setMode(Fingerprint::MODE_USEIP);
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
 * @version 0.66
 */
class Fingerprint
{
    /**
     * Constantes de modo
     */
    const MODE_USEIP = TRUE;
    const MODE_DONTUSEIP = FALSE;
    
    /**
     *
     * @var string Fingerprint Token
     */
    protected $fingerprintToken;
    
    /**
     *
     * @var int ID de la tabla Token en la DB
     */
    protected $TokenId;

    /**
     *
     * @var boolean TRUE para generar Fingerprint Token teniendo en cuenta la
     * IP del usuario (por defecto), FALSE para no emplear la IP.
     */
    protected $mode = self::MODE_USEIP;

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
     * @param string $fingerprintToken Token a validar.
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
     * Verifica si el TokenId es válido (entero no vacío).
     * 
     * @param int $TokenId TokenId a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    protected static function isValid_TokenId($TokenId)
    {
	if (!empty($TokenId) 
            && is_int($TokenId)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un Token de Fingerprint armado.
     * 
     * @param boolean $mode TRUE para generar un token teniendo en cuenta la
     * IP del usuario (por defecto), FALSE para no emplear la IP.
     * @return string Token de Fingerprint.
     */
    protected static function tokenMake($mode = self::MODE_USEIP)
    {    
        if ($mode) {
            $tokenContent = Sanitizar::glSERVER('HTTP_USER_AGENT')
                                . Sanitizar::glSERVER('REMOTE_ADDR')
                                . Sanitizar::glSERVER('HTTP_HOST')
                                . Sanitizar::glSERVER('HTTP_X_HTTP_PROTO')
                                . Sanitizar::glSERVER('HTTP_X_REAL_IP')
                                . Sanitizar::glSERVER('SERVER_PROTOCOL')
                                . SMP_FINGERPRINT_TKN
                                ;
        } else {
            $tokenContent = Sanitizar::glSERVER('HTTP_USER_AGENT')
                                . Sanitizar::glSERVER('HTTP_HOST')
                                . Sanitizar::glSERVER('HTTP_X_HTTP_PROTO')
                                . Sanitizar::glSERVER('SERVER_PROTOCOL')
                                . SMP_FINGERPRINT_TKN
                                ;
        }
        return Crypto::getHash($tokenContent);
    }

    // __ PUB
    /**
     * Fija el modo en que se generará el Fingerprint Token: teniendo en
     * cuenta la IP del usuario o no.
     * 
     * @param boolean $mode <b>MODE_USEIP</b> para tener en cuenta la 
     * IP del usuario.  <b>MODE_DONTUSEIP</b> para no emplear la IP.
     */
    public function setMode($mode = self::MODE_USEIP)
    {
        $this->mode = boolval($mode);
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
     * Fija el valor del identificador de tabla Token de la DB.
     * 
     * @param int $TokenId
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setTokenId($TokenId) 
    {
        if (self::isValid_TokenId($TokenId)) {
            $this->TokenId = $TokenId;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve el Fingerprint Token almacenado en el objeto.
     * @return string Fingerprint Token.
     */
    public function getToken()
    {
        if (isset($this->fingerprintToken)) {
            return $this->fingerprintToken;
        }
        return '';
    }
    
    /**
     * Genera un Token que representa al usuario (navegador, IP, etc...).<br />
     * Debe fijarse el modo primero.  Por defecto el modo es 
     * <i>MODE_USEIP</i>.<br />
     * Para obtenerlo, usar getToken.
     * 
     * @see mode
     * @see getToken
     * @return string Fingerprint Token.
     */ 
    public function generateToken()
    {
        $this->fingerprintToken = self::tokenMake($this->mode);
    }
    
    /**
     * Recupera el Fingerprint Token almacenado en la DB y lo guarda en el 
     * objeto.  Usar getToken para obtener el valor.
     * 
     * @see getToken
     * @return boolean TRUE si tuvo exito, FALSE si no.
     */
    public function retrieve_fromDB() 
    {
        if (!empty($this->TokenId)) {
            $db = new DB;
            $db->setQuery('SELECT Fingerprint_Token FROM Token '
                        . 'WHERE TokenId = ?');
            $db->setBindParam('i');
            $db->setQueryParams($this->TokenId);
            $db->queryExecute();
            
            return $this->setToken($db->getQueryData());
        }
        
        return FALSE;
    }
    
    /**
     * Almacena en la DB el Fingerprint Token guardado en el objeto.<br />
     * Debe fijarse primero el identificador de tabla Token y el valor del 
     * Token (mediante setToken o generateToken).
     * 
     * @see setTokenId
     * @see setToken
     * @see generateToken
     * @return boolean TRUE si se almacenó en la DB exitosamente, 
     * FALSE en caso contrario.
     */
    public function store_inDB() 
    {
        if (!empty($this->TokenId) && !empty($this->fingerprintToken)) {
            $db = new DB(TRUE);
            $db->setQuery('UPDATE Token SET Fingerprint_Token = ? '
                        . 'WHERE TokenId = ?');
            $db->setBindParam('si');
            $db->setQueryParams([$this->fingerprintToken, $this->TokenId]);
            //// atenti porque la func devuelve tb nro de error
            // ToDo: procesar nro de error
            $retval = $db->queryExecute();
            if (is_bool($retval)) {
                return $retval;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Autentica el Token de Figerprint almacenado en el objeto contra uno 
     * generado nuevo.
     * 
     * @see setToken
     * @return boolean TRUE si el Token de Fingerprint es auténtico, 
     * FALSE si no.<br />
     */
    public function authenticateToken() 
    {      
        if ($this->fingerprintToken === $this->tokenMake($this->mode)) {
            return TRUE;
        }

        return FALSE;
    }
}