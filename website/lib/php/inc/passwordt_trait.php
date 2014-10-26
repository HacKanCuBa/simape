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
 * Maneja la creacion de contraseñas y la autenticación.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.15
 */
trait Passwordt
{   
    protected $passwordPT, $passwordEC, $passwordCost, 
              $passwordModificationTimestamp;

    // Metodos
    // __ SPECIALS

    // __ PRIV
    
    // __ PROT   
    /**
     * Fija el valor de PasswordCost según la configuración del sistema.
     * @access protected
     */
    protected function setPasswordCost()
    {
        if (constant('SMP_PASSWORD_COST') < 10) {
            $this->passwordCost = 10;
        } elseif (constant('SMP_PASSWORD_COST') > 31) {
            $this->passwordCost = 31;
        } else {
            $this->passwordCost = SMP_PASSWORD_COST;
        }
    }
    
    /**
     * Valida un string y determina si cumple las restricciones impuestas sobre
     * las contraseñas (planas). 
     * IMPORTANTE: ¡NO ES UNA FUNCIÓN DE AUTENTICACIÓN!
     * 
     * @param string $password Contraseña plana a ser validada.
     * @return boolean TRUE si el string es una contraseña plana válida, 
     * FALSE si no lo es.
     */
    protected static function isValid_ptPassword($password) 
    {
        if (!empty($password)
            && is_string($password)
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Valida un string y determina si cumple las restricciones respecto de 
     * contraseñas encriptadas (debe ser como la devuelta por password_hash()).
     * IMPORTANTE: ¡NO ES UNA FUNCIÓN DE AUTENTICACIÓN!
     * 
     * @param string $password Contraseña encriptada a ser validada.
     * @return boolean TRUE si el string es una contraseña encriptada válida, 
     * FALSE si no lo es.
     */
    protected static function isValid_encPassword($password) 
    {
        if (!empty($password)
            && is_string($password)
            && preg_match('/^[$]2y[$]([1-2][0-9]|[3][0-1])[$]'
                          . '[0-9A-Za-z\x{5c}\.\x{2f}]{53}$/', 
                          $password)
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    // __ PUB 
    /**
     * Determina si una contraseña en texto plano es fuerte (criptográficamente 
     * segura).
     * @param string $passwordPT Contraseña.
     * @return boolean TRUE si es una contraseña segura, FALSe si no.
     */
    public static function isStrong($passwordPT)
    {
        // Al menos una letra mayus y minus, y un nro, y puede contener letras, 
        // nros, y determinados simbolos.
        // http://stackoverflow.com/a/11874336
        if (self::isValid_ptPassword($passwordPT)
            && preg_match('/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])' 
                          . '[\x{20}-\x{af}\p{L}]'
                          . '{' . constant('SMP_PWD_MINLEN') . ','
                          . constant('SMP_PWD_MAXLEN') . '}$/u', 
                          $passwordPT)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Genera y almacena un nuevo Token.  Requiere previamente del
     * Random Token, Timestamp y UID.
     * 
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */
    public function generateToken()
    {
        if(!empty($this->randToken)
           && !empty($this->timestamp) 
           && !empty($this->uid)
        ) {
           $token = self::tokenMake($this->randToken,
                                    SMP_TKN_PWDRESTORE,
                                    $this->timestamp,
                                    SMP_PASSWORD_RESTORETIME,
                                    $this->uid); 
           return $this->setToken($token);
        }
        
        return FALSE;
    }
    
    /**
     * Almacena una contraseña en texto plano, si la misma cumple las 
     * restricciones (es decir, es válida), y prepara para encriptarla.
     * 
     * @param string $passwordPT Contraseña en texto plano
     * @param boolean $requireStrong Si es TRUE, requiere que la contraseña 
     * sea <i>fuerte</i>.
     * @return boolean TRUE si la contraseña es válida y fue almacenada, 
     * FALSE si no.  Por defecto: SMP_PASSWORD_REQUIRESTRONG.
     */
    public function setPasswordPlaintext($passwordPT, 
                                    $requireStrong = SMP_PASSWORD_REQUIRESTRONG)
    {
        $plaintext = trim($passwordPT);
        if ($this->isValid_ptPassword($plaintext)) {
            if ($requireStrong) {
                if (self::isStrong($plaintext)) {
                    $this->passwordPT = $plaintext;
                    return TRUE;
                }
            } else {
                $this->passwordPT = $plaintext;
                return TRUE;
            }
            
        }
        
        return FALSE;
    }
    
    /**
     * Almacena una contraseña encriptada, si la misma cumple las 
     * restricciones (es decir, es válida).
     * 
     * @param string $passwordEC Contraseña encriptada.
     * @return boolean TRUE si la contraseña es válida y fue almacenada, 
     * FALSE si no.
     */
    public function setPasswordEncrypted($passwordEC)
    {
        if ($this->isValid_encPassword($passwordEC)) {
            $this->passwordEC = $passwordEC;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena el valor de la última vez que fue modificada la contraseña
     * (Password Timestamp).
     * 
     * @param int $password_modification_time Timestamp.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setModificationTimestamp($password_modification_time)
    {
        if (!empty($password_modification_time)
            && is_int($password_modification_time)
        ) {
            $this->passwordModificationTimestamp = $password_modification_time;
            return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Almacena en la DB el Random Token y el Timestamp guardados en el objeto.
     * <br />
     * Debe fijarse primero el identificador de tabla Token y los valores 
     * respectivos.
     * 
     * @see Token::setTokenId()
     * @see Token::setRandomToken()
     * @see Passwordt::generateToken()
     * @see Token::setTimestamp()
     * @see Token::generateTimestamp()
     * @return boolean TRUE si se almacenó en la DB exitosamente, 
     * FALSE en caso contrario.
     */
    public function store_inDB_PwdRestore()
    {
        if (!empty($this->TokenId) 
            && isset($this->randToken)
            && isset($this->timestamp)
        ) {
            $db = new DB(SMP_DB_CHARSET, TRUE);
            $db->setQuery('UPDATE Token '
                        . 'SET PasswordRestore_RandomToken = ?, '
                        . 'PasswordRestore_Timestamp = ? '
                        . 'WHERE TokenId = ?');
            $db->setBindParam('sii');
            $db->setQueryParams([$this->randToken, $this->timestamp, $this->TokenId]);
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
     * Devuelve la contraseña encriptada.  Debe haberse llamado primero a 
     * encryptPassword() o en su defecto setPasswordEncrypted().
     * 
     * @see Passwordt::encryptPassword()
     * @return string|FALSE La contraseña encriptada.
     */
    public function getPasswordEncrypted()
    {
        if (isset($this->passwordEC)) {
            return $this->passwordEC;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Devuelve la contraseña almacenada en texto plano, o NULL si no hay 
     * ninguna.
     * 
     * @return string|FALSE La contraseña en texto plano.
     */
    public function getPasswordPlaintext()
    {
        if (isset($this->passwordPT)) {
            return $this->passwordPT;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Devuelve el timestamp de la contraseña almacenado, o FALSE si no hay 
     * ninguno.
     * 
     * @return int|boolean Timestamp de modificación de contraseña o FALSE.
     */
    public function getModificationTimestamp()
    {
        if (isset($this->passwordModificationTimestamp)) {
            return $this->passwordModificationTimestamp;
        } else {
            return FALSE;
        }
    }

    /**
     * This code will benchmark your server to determine how high of a cost you can
     * afford. You want to set the highest cost that you can without slowing down
     * you server too much. 10 is a good baseline, and more is good if your servers
     * are fast enough.
     * 
     * @link http://www.php.net/manual/en/function.password-hash.php
     * @return int Optimal value for <i>cost</i> parameter.
     */
    public static function getOptimalCost()
    {   
        $timeTarget = 0.5; 

        $cost = 9;
        do {
            $cost++;
            $start = microtime(true);
            password_hash("test", PASSWORD_DEFAULT, ["cost" => $cost]);
            $end = microtime(true);
        } while ((($end - $start) < $timeTarget) && ($cost < 31));

        return $cost;
    }
    
    /**
     * Recupera el Random Token y el Timestamp almacenado en la DB y lo guarda 
     * en el objeto.  Usar los respectivos get... para obtener los valores.
     * 
     * @return boolean TRUE si tuvo exito, FALSE si no.
     * @see Token::setTokenId()
     * @access public
     */
    public function retrieve_fromDB_PwdRestore() 
    {
        if (!empty($this->TokenId)) {
            $db = new DB(SMP_DB_CHARSET);
            $db->setQuery('SELECT PasswordRestore_RandomToken, '
                            . 'PasswordRestore_Timestamp '
                            . 'FROM Token WHERE TokenId = ?');
            $db->setBindParam('i');
            $db->setQueryParams($this->TokenId);
            if ($db->queryExecute()) {
                $tokens = $db->getQueryData();
                if ($this->setRandomToken($tokens['PasswordRestore_RandomToken'])
                    && $this->setTimestamp($tokens['PasswordRestore_Timestamp'])
                ) {
                    return TRUE;
                }
            }            
        }
        
        return FALSE;
    }
    
    /**
     * Remueve de la DB el random token y el timestamp de Password Restore.  
     * Requiere TokenId.
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     * @access public
     */
    public function remove_fromDB_PwdRestore()
    {
        if (!empty($this->TokenId)) {
            $this->randToken = NULL;
            $this->timestamp = 0;
            return $this->store_inDB_PwdRestore();
        }
        
        return FALSE;
    }

    /**
     * Encripta la contraseña almacenada en texto plano.  Para obtener el 
     * resultado: getPasswordEncrypted().
     * NOTA: ¡puede demorar varios segundos!
     * @see Passwordt::getPasswordEncrypted()
     * @return boolean TRUE si se encriptó correctamente, FALSE si no.
     */
    public function encryptPassword() 
    {
        if (!empty($this->passwordPT)) {
            $options = array('cost' => $this->passwordCost);
            $passwordEC = password_hash($this->passwordPT, 
                                               PASSWORD_DEFAULT, 
                                               $options);
            return $this->setPasswordEncrypted($passwordEC);
        }
        
        return FALSE;
    }
    
    /** 
     * Autentica la contraseña en texto plano contra la contraseña encriptada.
     * NOTA: A fin de evitar en cierta medida un ataque de timing oracle,
     * esta función implementa un restraso cuando passwordEC es nulo.
     * 
     * @see Passwordt::setPasswordPlaintext()
     * @see Passwordt::setPasswordEncrypted()
     * @return boolean TRUE si la contraseña es válida (idéntica a la 
     * encriptada), FALSE en caso contrario.
     */
    public function authenticatePassword() 
    {
        if (empty($this->passwordEC) 
            || empty($this->passwordPT)
        ) {
            // Lamentablemente, password_verify se detiene si
            // passwordEC no es un hash válido, retornando con NULL y 
            // habilitando un timing oracle...
            // Fuerzo entonces la verificación con un hash válido cualquiera
            password_verify('simape', '$2y$' 
                                      . $this->passwordCost 
                                      . '$olndK9yRKbD9q3mK3SQE'
                                      . 'qeWqTDCIgwzKcw.fSDx6k'
                                      . 'f44Vyjngvf3a');
            
            return FALSE;
        } else {
            return password_verify($this->passwordPT, 
                                    $this->passwordEC);
        }
    }
    
    /**
     * Autentica el Token de restablecimiento de contraseña.<br />
     * Devuelve TRUE si es auténtico, FALSE en cualquier otro caso.
     * 
     * @return boolean TRUE si el Token de restablecimiento de contraseña
     * es auténtico, FALSE si no.
     */
    public function authenticateToken() 
    {
        $now = time();

        if (isset($this->timestamp)
            && ($now >= $this->timestamp) 
            && ($now < ($this->timestamp + SMP_PASSWORD_RESTORETIME))
            && isset($this->token)
        ) {
            // Verifico que tokenMake no sea FALSE.
            $passtoken = self::tokenMake($this->randToken,
                                            SMP_TKN_PWDRESTORE,
                                            $this->timestamp,
                                            SMP_PASSWORD_RESTORETIME,
                                            $this->uid);
            if ($passtoken && ($this->token === $passtoken)) {
                return TRUE;
            }  
        }

        return FALSE; 
    }


    /**
     * Determina si la contraseña ya ha expirado o no.<br />
     * Debe fijarse el valor de Password Timestamp.
     * 
     * @see Passwordt::setModificationTimestamp()
     * @return boolean|null TRUE si la contraseña expiró, FALSE si no.<br />
     * Si no se puede determinar, devuelve NULL.
     */
    public function isExpired()
    {
        if (SMP_PASSWORD_MAXDAYS > 0) {
            if (empty($this->passwordModificationTimestamp)) {
                return NULL;
            } elseif (($this->passwordModificationTimestamp + 
                       (SMP_PASSWORD_MAXDAYS * 86400)) < time()) {
                return TRUE;
            }
        }
        
        return FALSE;
    }
}