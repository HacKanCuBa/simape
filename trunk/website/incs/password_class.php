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
 * Ejemplo de uso:
 * <pre><code>
 * $pass = new Password();
 * $pass->setPlaintext($ptPass);
 * $pass->encryptPassword();
 * // Obtengo la contraseña encriptada
 * $encPass = $pass->getEncrypted();
 * // Valido una nueva contraseña plana
 * $pass->setPlaintext($NewptPass); 
 * if ($pass->authenticatePassword()) {
 *      echo "Contraseña valida";
 * } else {
 *      echo "Contraseña incorrecta";
 * }
 * // Reestablecimiento de contraseña
 * $randToken = $pass->getRandomToken();
 * $timestamp = $pass->getTimestamp();
 * $restToken = $pass->getToken();
 * </code></pre>
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.24
 */
class Password
{
    use SessionToken {
        getToken as sesst_getToken;
        isValid_sessiontoken as isValid_restoreToken;
    }
    
    protected $plaintextPassword, $encryptedPassword, $PasswordCost, 
              $PasswordTimestamp, $passRestoreToken;

    // Metodos
    // __ SPECIALS
    /**
     * Crea un nuevo objeto Password.  Si recibe el parámetro, lo almacena como 
     * una contraseña en texto plano, si la misma cumple las 
     * restricciones (es decir, es válida), y prepara para encriptarla.
     * Llamar a encryptPassword() para encriptarla.
     * 
     * @see encryptPassword()
     * @param string $plaintextPassword Contraseña en texto plano
     * @param string $saltedPassword Contraseña encriptada.
     */
    public function __construct($plaintextPassword = NULL, 
                                 $saltedPassword = NULL)
    {
        if (((int) constant('SMP_PASSWORD_COST')) < 10) {
            $this->PasswordCost = 10;
        } elseif (((int) constant('SMP_PASSWORD_COST')) > 31) {
            $this->PasswordCost = 31;
        } else {
            $this->PasswordCost = SMP_PASSWORD_COST;
        }
        
        $this->setPlaintext($plaintextPassword);
        $this->setEncrypted($saltedPassword);
    }
    // __ PRIV
    
    // __ PROT    
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
     * @param string $plaintextPassword Contraseña.
     * @return boolean TRUE si es una contraseña segura, FALSe si no.
     */
    public static function isStrong($plaintextPassword)
    {
        // Al menos una letra mayus y minus, y un nro, y puede contener letras, 
        // nros, y determinados simbolos.
        // http://stackoverflow.com/a/11874336
        if (self::isValid_ptPassword($plaintextPassword)
            && preg_match('/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])' 
                          . '[\x{20}-\x{af}\p{L}]'
                          . '{' . constant('SMP_PWD_MINLEN') . ','
                          . constant('SMP_PWD_MAXLEN') . '}$/u', 
                          $plaintextPassword)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Almacena una contraseña en texto plano, si la misma cumple las 
     * restricciones (es decir, es válida), y prepara para encriptarla.
     * 
     * @param string $plaintextPassword Contraseña en texto plano
     * @return boolean TRUE si la contraseña es válida y fue almacenada, 
     * FALSE si no.
     */
    public function setPlaintext($plaintextPassword)
    {
        if ($this->isValid_ptPassword($plaintextPassword)) {
            $this->plaintextPassword = $plaintextPassword;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena una contraseña encriptada, si la misma cumple las 
     * restricciones (es decir, es válida).
     * 
     * @param string $encryptedPassword Contraseña encriptada.
     * @return boolean TRUE si la contraseña es válida y fue almacenada, 
     * FALSE si no.
     */
    public function setEncrypted($encryptedPassword)
    {
        if ($this->isValid_encPassword($encryptedPassword)) {
            $this->encryptedPassword = $encryptedPassword;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena el valor de la última vez que fue modificada la contraseña
     * (Password Timestamp).
     * 
     * @param int $passwordTimestamp
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setPasswordTimestamp($passwordTimestamp)
    {
        if (!empty($passwordTimestamp) && is_int($passwordTimestamp)) {
            $this->PasswordTimestamp = $passwordTimestamp;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena el Token aleatorio para la autenticación del Token de 
     * reestablecimiento de contraseña.<br />
     * <b>IMPORTANTE</b>: NO emplearlo para generar un Token de 
     * reestablecimiento nuevo!<br /> 
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
     * Fija el valor de Timestamp para la función de autenticación del Token de 
     * reestablecimiento de contraseña.<br />
     * <b>IMPORTANTE</b>: NO emplearlo para generar un Token de 
     * reestablecimiento nuevo!<br />
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
     * Fija el valor del Token de reestablecimiento de contraseña que será 
     * autenticado.
     * 
     * @param string $passRestoreToken Token de reestablecimiento de 
     * contraseña.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setToken($passRestoreToken)
    {
        if ($this->isValid_restoreToken($passRestoreToken)) {
            $this->passRestoreToken = $passRestoreToken;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena el UID del usuario, pasado como objeto UID.<br />
     * Se emplea tanto en la función de autenticación del Token de <br />
     * reestablecimiento de contraseña como en la de generación del mismo.
     * 
     * @param UID $uid UID del usuario
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    public function setUID(UID $newUID) 
    {
        return $this->t_setUID($newUID);
    }

    /**
     * Devuelve la contraseña encriptada.  Debe haberse llamado primero a 
     * encryptPassword() o en su defecto setEncrypted().
     * 
     * @see encryptPassword()
     * @return string La contraseña encriptada.
     */
    public function getEncrypted()
    {
        if (isset($this->encryptedPassword)) {
            return (string) $this->encryptedPassword;
        } else {
            return NULL;
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
     * Devuelve un Token aleatorio, que es el mismo que se emplea para armar
     * el Token de restablecimiento de contraseña.
     * 
     * @see getToken()
     * @return string Token aleatorio.
     */
    public function getRandomToken()
    {
        return $this->t_getRandomToken();
    }
    
    /**
     * Devuelve el timestamp empleado para crear el Token de restablecimiento 
     * de contraseña.
     * 
     * @return float Timestamp.
     */
    public function getTimestamp()
    {
        return $this->t_getTimestamp();
    }
    
    /**
     * Devuelve un Token de restablecimiento de contraseña.<br />
     * Debe llamarse primero a getRandomToken(), getTimestamp() y setUID().
     * 
     * @see getRandomToken()
     * @see getTimestamp()
     * @see setUID()
     * @param boolean $notStrict Si es TRUE, permite usar valores externos<br />
     * vía setRandomToken() y setTimestamp() para generar el Token de 
     * restablecimiento de contraseña.<br />
     * FALSE por defecto.
     * @return mixed Token de restablecimiento de contraseña, 
     * o FALSE en caso de error.
     */ 
    public function getToken($notStrict = FALSE)
    {
        return $this->sesst_getToken($notStrict);
    }
    
    /**
     * Encripta la contraseña almacenada en texto plano.  Para obtener el 
     * resultado: getEncrypted().
     * NOTA: ¡puede demorar varios segundos!
     * @see getEncrypted()
     * @return void No devuelve nada.
     */
    public function encryptPassword() 
    {
        if (!empty($this->plaintextPassword)) {
            $options = array('cost' => $this->PasswordCost);
            $this->encryptedPassword = password_hash($this->plaintextPassword, 
                                                  PASSWORD_DEFAULT, 
                                                  $options);
        }
    }
    
    /** 
     * Autentica la contraseña en texto plano contra la contraseña encriptada.
     * NOTA: A fin de evitar en cierta medida un ataque de timing oracle,
     * esta función implementa un restraso cuando encryptedPassword es nulo.
     * 
     * @see setPlaintext()
     * @see setEncrypted()
     * @return boolean TRUE si la contraseña es válida (idéntica a la 
     * encriptada), FALSE en caso contrario.
     */
    public function authenticatePassword() 
    {
        if (empty($this->encryptedPassword) 
            || empty($this->plaintextPassword)
        ) {
            // Lamentablemente, password_verify se detiene si
            // encryptedPassword no es un hash válido, retornando con NULL y 
            // habilitando un timing oracle...
            // Fuerzo entonces la verificación con un hash válido cualquiera
            password_verify('simape', '$2y$' 
                                      . $this->PasswordCost 
                                      . '$olndK9yRKbD9q3mK3SQE'
                                      . 'qeWqTDCIgwzKcw.fSDx6k'
                                      . 'f44Vyjngvf3a');
            
            return FALSE;
        } else {
            return (boolean) password_verify($this->plaintextPassword, 
                                             $this->encryptedPassword);
        }
    }
    
    /**
     * Autentica el Token de reestablecimiento de contraseña.<br />
     * Devuelve TRUE si es auténtico, FALSE en cualquier otro caso.
     * 
     * @return boolean TRUE si el Token de reestablecimiento de contraseña
     * es auténtico, FALSE si no.
     */
    public function authenticateToken() 
    {
        $now = microtime(TRUE);

        if (isset($this->timestamp)
            && ($now >= $this->timestamp) 
            && ($now < ($this->timestamp + SMP_PASSWORD_RESTORETIME))
            && isset($this->passRestoreToken)
            && ($this->passRestoreToken === $this->getToken(TRUE))
        ) {
            return TRUE;            
        }

        return FALSE; 
    }


    /**
     * Determina si la contraseña ya ha expirado o no.<br />
     * Debe fijarse el valor de Password Timestamp.
     * 
     * @see setPasswordTimestamp()
     * @return boolean|null TRUE si la contraseña expiró, FALSE si no.<br />
     * Si no se puede determinar, devuelve NULL.
     */
    public function isExpired()
    {
        if (SMP_PASSWORD_MAXDAYS > 0) {
            if (empty($this->PasswordTimestamp)) {
                return NULL;
            } elseif (($this->PasswordTimestamp + 
                       (SMP_PASSWORD_MAXDAYS * 86400)) < time()) {
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Busca en la DB la contraseña encriptada del usuario indicado y la 
     * almacena.
     * 
     * @param string $username Nombre de usuario.
     * @return boolean TRUE si se encontró y almacenó correctamente, 
     * FALSE si no.
     */
    public function retrieveFromDB($username)
    {
        if (!empty($username) && is_string($username)) {
            $db = new DB;
            $pass = $db->auto(DB::AUTO_PASSWORD, $username);
            if ($pass) {
                return $this->setEncrypted($pass);
            }
        }
        
        return FALSE;
    }
}