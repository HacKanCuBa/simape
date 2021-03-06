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
 * @version 0.71
 */
class Fingerprint
{
    use Token;
    
    /**
     * Constantes de modo
     */
    const MODE_USEIP = TRUE;
    const MODE_DONTUSEIP = FALSE;

    /**
     * TRUE para generar Fingerprint Token teniendo en cuenta la
     * IP del usuario (por defecto), FALSE para no emplear la IP.
     * @var boolean
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
     * Devuelve un Token de Fingerprint armado.
     * 
     * @param boolean $mode TRUE para generar un token teniendo en cuenta la
     * IP del usuario (por defecto), FALSE para no emplear la IP.
     * @return string Token de Fingerprint.
     */
    private static function tokenMake($mode = self::MODE_USEIP)
    {    
        $tokenContent = Sanitizar::glSERVER('HTTP_USER_AGENT')
                            . Sanitizar::glSERVER('HTTP_HOST')
                            . Sanitizar::glSERVER('HTTP_X_HTTP_PROTO')
                            . Sanitizar::glSERVER('SERVER_PROTOCOL')
                            . is_connection_ssl()
                            . SMP_TKN_FINGERPRINT
                            ;
        
        $tokenContent .= ($mode == self::MODE_USEIP) ? IP::getClientIP() : ''; 

        return Crypto::getHash($tokenContent, 1);
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
     * Genera un Token que representa al usuario (navegador, IP, etc...).<br />
     * Debe fijarse el modo primero.  Por defecto el modo es 
     * <i>MODE_USEIP</i>.<br />
     * Para obtenerlo, usar getToken.
     * 
     * @see Fingerprint::mode()
     * @see Token::getToken()
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */ 
    public function generateToken()
    {
        $token = self::tokenMake($this->mode);
        if (self::isValid_fingerprintToken($token)) {
            $this->token = $token;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Recupera el Fingerprint Token almacenado en la DB y lo guarda en el 
     * objeto.  Usar getToken para obtener el valor.
     * 
     * @see Token::getToken()
     * @return boolean TRUE si tuvo exito, FALSE si no.
     */
    public function retrieve_fromDB() 
    {
        if (!empty($this->TokenId)) {
            $this->db->setQuery('SELECT Fingerprint_Token FROM Token '
                        . 'WHERE TokenId = ?');
            $this->db->setBindParam('i');
            $this->db->setQueryParams($this->TokenId);
            $this->db->queryExecute();
            
            return $this->setToken($this->db->getQueryData());
        }
        
        return FALSE;
    }
    
    /**
     * Almacena en la DB el Fingerprint Token guardado en el objeto.<br />
     * Debe fijarse primero el identificador de tabla Token y el valor del 
     * Token (mediante setToken o generateToken).
     * 
     * @see Token::setTokenId()
     * @see Token::setToken()
     * @see Fingerprint::generateToken()
     * @return boolean TRUE si se almacenó en la DB exitosamente, 
     * FALSE en caso contrario.
     */
    public function store_inDB() 
    {
        if (!empty($this->TokenId) 
                && !empty($this->token) 
                && isset($this->db)
        ) {
            $this->db->cambiarModo(TRUE);
            $this->db->setQuery('UPDATE Token SET Fingerprint_Token = ? '
                        . 'WHERE TokenId = ?');
            $this->db->setBindParam('si');
            $this->db->setQueryParams([$this->token, $this->TokenId]);
            //// atenti porque la func devuelve tb nro de error
            // ToDo: procesar nro de error
            $retval = $this->db->queryExecute();
            $this->db->cambiarModo(FALSE);
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
     * @see Token::setToken()
     * @return boolean TRUE si el Token de Fingerprint es auténtico, 
     * FALSE si no.<br />
     */
    public function authenticateToken() 
    {      
        if (isset($this->token) 
            && isset($this->mode)
            && ($this->token === $this->tokenMake($this->mode))
        ) {
            return TRUE;
        }

        return FALSE;
    }
    
    /**
     * Verifica si el Token de Fingerprint es válido.
     * 
     * @param string $fingerprintToken Token a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    public static function isValid_fingerprintToken($fingerprintToken)
    {
	return self::isValid_token($fingerprintToken);
    }
}