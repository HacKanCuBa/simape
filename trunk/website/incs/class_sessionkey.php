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

require_once SMP_INC_ROOT . SMP_LOC_INCS . 'class_crypto.php';
require_once SMP_INC_ROOT . SMP_LOC_INCS . 'class_uid.php';
require_once SMP_INC_ROOT . SMP_LOC_INCS . 'class_timestamp.php';

/**
 * Maneja la creación y autenticación de la llave de sesión.
 * 
 * Ejemplo de uso:
 * <pre><code>
 * $sessk = new Sessionkey('uid-correspondiente');
 * $sessk->makeNew();
 * $_SESSION['sessionkey'] = $sessk->getArray();
 * ...
 * $othersessk = new Sessionkey('uid-correspondiente', $_SESSION['sessionkey']);
 * if ($othersessk->authenticateSessionkey()) {
 *      echo "Llave de sesion autentica"
 * } else {
 *      echo "Llave de session INCORRECTA"
 * }
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.4
 */
class Sessionkey
{
    protected $key, $token, $timestamp;
    protected $uid;
    
    // Metodos
    // __ SPECIALS
    /**
     * Si recibe un objeto UID del usuario, lo almacena.
     * Si recibe un array de llave de sesión, lo almacena.
     * 
     * @param UID $uid UID del usario.
     * @param array $sessionkey_array Array de llave de sesión.
     */
    public function __construct(UID $uid = \NULL, 
                                 array $sessionkey_array = \NULL) 
    {        
        if (!empty($uid)) {
            $this->setUID($uid);
        }
        if (!empty($sessionkey_array)) {
            $this->setSessionkey($sessionkey_array);
        }
    }
    // __ PRIV
    
    // __ PROT
    protected static function isValid_UID(UID $uid)
    {
        if (!empty($uid) && is_a($uid, 'UID')) {
            return TRUE;
        }
        
        return FALSE;
    }

    protected static function isValid_token($token)
    {
        if (!empty($token)
            && is_string($token)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    protected static function isValid_key($key)
    {
        if (!empty($key)
            && is_string($key)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    protected static function isValid_timestamp($timestamp)
    {
        if (!empty($timestamp)
            && is_int($timestamp)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Genera una clave de sesión.
     * 
     * @return string La llave de sesión si se generó exitosamente, o 
     * NULL en caso contrario.
     */
    protected function keyMake() 
    {

        if (!empty($this->token) 
            && !empty($this->timestamp)
            && !empty($this->uid->getUID())) {        
            // Para forzar una vida util durante sólo el mismo dia.
            // Si cambia el dia, el valor de la operacion cambiara.
            $time = $this->timestamp - Timestamp::getToday();

            // Se utiliza Timestamp::getThisSeconds para fozar la vida útil máxima
            return Crypto::getHash(Crypto::getHash($time 
                    . $this->token 
                    . Timestamp::getThisSeconds(constant('SMP_SESSIONKEY_LIFETIME')) 
                    . $this->uid->getUID()
                    . constant('SMP_SESSIONKEY_TKN')));
        } else {
            return NULL;
        }
    }
    // __ PUB
    /**
     * Almacena el UID del usuario, pasado como objeto UID.
     * 
     * @param UID $uid UID del usuario
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    public function setUID(UID $newUID) 
    {
        if ($this->isValid_UID($newUID)) {
            $this->uid = $newUID;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena los elementos de la llave de sesión obtenidos del array 
     * recibido.  Solo almacenará los elementos encontrados en el array obtenido
     * que sean válidos.
     * 
     * @param array $sessionkey_array Array con los elementos de la llave de 
     * sesión.
     * @return boolean TRUE si se almacenaron todos los elementos correctamente, 
     * FALSE si al menos uno no.
     */
    public function setSessionkey(array $sessionkey_array)
    {        
        if (!empty($sessionkey_array) && is_array($sessionkey_array)) {
            if (isset($sessionkey_array['tkn'])) {
                $result1 = $this->setToken($sessionkey_array['tkn']);
            }
            if (isset($sessionkey_array['key'])) {
                $result2 = $this->setKey($sessionkey_array['key']);
            }
            if (isset($sessionkey_array['timestamp'])) {
                $result3 = $this->setTimestamp($sessionkey_array['timestamp']);
            }
        }
        
        if (empty($result1) || empty($result2) || empty($result3)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    public function setToken($newToken)
    {
        if ($this->isValid_token($newToken)) {
            $this->token = $newToken;
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function setKey($newKey)
    {
        if ($this->isValid_key($newKey)) {
            $this->key = $newKey;
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function setTimestamp($newTimestamp)
    {
        if ($this->isValid_timestamp($newTimestamp)) {
            $this->timestamp = $newTimestamp;
            return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Genera y almacena una llave de sesión nueva.
     * 
     * @return boolean TRUE si se generó y almacenó con éxito una nueva llave
     * de sesión, FALSE en caso contrario.
     */
    public function makeNew()
    {    
        if (!empty($this->uid)) {
            $this->timestamp = time();
            $this->token = Crypto::getRandomTkn();
            $this->key = $this->keyMake();
            if (!empty($this->key)) {
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un array con los componentes de la llave se sesión
     * 
     * @return array Array con los componentes de la llave se sesión
     */
    public function getArray()
    {
       return array('tkn' => $this->token,
                    'key' => $this->key, 
                    'timestamp' => $this->timestamp);
    }

//    public function getToken()
//    {
//        if (isset($this->token)) {
//            return $this->token;
//        }
//    }
//
//    public function getKey()
//    {
//        if (isset($this->key)) {
//            return $this->key;
//        }
//    }
//
//    public function getTimestamp()
//    {
//        if (isset($this->timestamp)) {
//            return $this->timestamp;
//        }
//    }

    /**
     * Autentica la llave de sesion.  Devuelve TRUE si es válida, 
     * FALSE en cualquier otro caso.
     * 
     * @return boolean TRUE si la llave de sesión es auténtica, FALSE si no.
     */
    public function authenticateSessionkey() 
    {
        $now = time();

        if (!empty($this->token) 
            && !empty($this->uid)
            && ($now >= $this->timestamp) 
            && ($now < ($this->timestamp + constant('SMP_SESSIONKEY_LIFETIME')))
            && ($this->key === $this->keyMake())
        ) {
            return TRUE;            
        }

        return FALSE;  
    }
}