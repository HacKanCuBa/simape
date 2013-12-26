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
 * @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 * 
 *****************************************************************************/

/**
 * Esta clase comprende funciones criptográficas o del estilo.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.94
 */

class Crypto
{  
    const ENC_ID = 'SMP_ENC';
    const ENC_SEPARATOR = '$';
    const IV_LEN = 16;

    // __ SPECIALS
        
    // __ PRIV
    
    // __ PROT    
    /**
     * Devuelve un string encriptado.
     * 
     * @param string $string String a encriptar.
     * @param string $password Contraseña.
     * @param string $iv String de inicialización.
     * @return mixed String encriptado o FALSE en caso de error.
     */
    protected static function encryptStr($string, $password, $iv) 
    {
        if (isset($string) && isset($password) && isset($iv)
            && is_string($password) && is_string($string) && is_string($iv)
        ) {
            // IV debe ser = 16 BYTES
            //$iv = substr(hash('sha256', $iv), 0, 16);
            if (strlen($iv) == self::IV_LEN) {
                return openssl_encrypt($string, 'AES-256-CTR', $password, 
                                       OPENSSL_ZERO_PADDING, $iv);
            }
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un string desencriptado.
     * 
     * @param string $encString String a desencriptar.
     * @param string $password Contraseña.
     * @param string $iv String de inicialización.
     * @return mixed String desencriptado o FALSE en caso de error.
     */
    protected static function decryptStr($encString, $password, $iv) 
    {
        if (isset($encString) && isset($password) && isset($iv)
            && is_string($password) && is_string($encString) && is_string($iv)
        ) {
            // IV debe ser = 16 BYTES
            //$iv = substr(hash('sha256', $iv), 0, 16);
            if (strlen($iv) == self::IV_LEN) {
                return openssl_decrypt($encString, 'AES-256-CTR', $password, 
                                       OPENSSL_ZERO_PADDING, $iv);
            }
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un identificador de texto encriptado por encrypt().
     * @return string Identificador de texto encriptado por encrypt().
     */
    protected static function getEncID()
    {
        return self::ENC_SEPARATOR . self::ENC_ID;
    }

    /**
     * Devuelve un string formateado del tipo encriptado.
     * 
     * @param string $iv IV.
     * @param string $encString String encriptado (devuelto por encryptStr()).
     * @return string String formateado del tipo encriptado.
     */
    protected static function getEncFormat($iv, $encString)
    {
        return self::getEncID() 
               . self::ENC_SEPARATOR . $iv 
               . self::ENC_SEPARATOR . $encString;
    }

    /**
     * Recibe un string encriptado con encrypt() y devuelve un array de la 
     * siguiente manera:
     * <ul>
     * <li>'IV' = <i>IV</i></li>
     * <li>'ENC' = <i>Datos encriptados</i></li>
     * </ul>
     * O bien FALSE en caso de error.
     * 
     * @param string $smpEncString String encriptado con encypt().
     * @return mixed Array como se indica más arriba, o FALSE en caso de error.
     */
    protected static function getEncParts($smpEncString)
    {
        if (self::isEncrypted($smpEncString)) {
            $iv = substr($smpEncString, 
                         strlen(self::getEncID()) 
                         + strlen(self::ENC_SEPARATOR), 
                         self::IV_LEN);
            $encStr = substr($smpEncString, 
                             strlen(self::getEncID()) 
                             + (2 * strlen(self::ENC_SEPARATOR))
                             + self::IV_LEN);
            return array('IV' => $iv, 'ENC' => $encStr);
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve el IV desde un array obtenido con getEncParts().
     * 
     * @param array $encParts Array obtenido con getEncParts()
     * @return mixed String IV o FALSE en caso de errror.
     */
    protected static function getIV_fromEncParts(array $encParts)
    {
        if (isset($encParts['IV'])) {
            return $encParts['IV'];
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve el string encriptado desde un array obtenido con getEncParts().
     * 
     * @param array $encParts Array obtenido con getEncParts()
     * @return mixed String encriptado o FALSE en caso de errror.
     */
    protected static function getENC_fromEncParts(array $encParts)
    {
        if (isset($encParts['ENC'])) {
            return $encParts['ENC'];
        }
        
        return FALSE;
    }

    // __ PUB
    /**
     * Devuelve el hash de un string
     * 
     * @param string $string String
     * @return string El hash del string indicado, o FALSE en caso de error.
     */
    public static function getHash($string) 
    {
        if (is_string($string)) {
            return hash('sha512', $string, FALSE);
        } else {
            return FALSE;
        }
    }

    /**
     * Devuelve un string de bytes aleatorios de la longitud indicada.
     * 
     * @param int $lenght Longitud del string
     * @return string String de bytes aleatorios de la longitud indicada, 
     * o FALSE en caso de error.
     */
    public static function getRandomBytes($lenght) 
    {
        if (!empty($lenght) && is_int($lenght)) {
            return openssl_random_pseudo_bytes($lenght);
        } else {
            return FALSE;
        }
    }
    
    /**
     * Devuelve un string de caracteres hexadecimales aleatorio de la 
     * longitud indicada.
     * 
     * @param int $lenght Longitud del string
     * @return string String aleatorio de la longitud indicada,
     * o FALSE en caso de error.
     */
    public static function getRandomHexStr($lenght)
    {
        if (!empty($lenght) && is_int($lenght)) {
            if ($lenght < 32) {
                $byteLen = 32;
            } else {
                $byteLen = (int) ($lenght / 2) + 1;
            }
            return substr(bin2hex(self::getRandomBytes($byteLen)), 
                          0, $lenght);
        }
        
        return FALSE;
    }

    /**
     * Devuelve un token aleatorio.
     *
     * @return string Token aleatorio como string de caracteres hexadecimales.
     * 
     */
    public static function getRandomTkn() 
    {        
        return self::getHash(self::getHash(self::getRandomBytes(128)));
    }
    
    /**
     * Devuelve un UUID v4 aleatorio.
     * 
     * @return string UUID v4 aleatorio.
     */
    public static function getUUIDv4()
    {
        //  https://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_.28random.29
        //  Version 4 UUIDs have the form xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx 
        //  where x is any hexadecimal digit and y is one of 8, 9, A, or B 
        //  (e.g., f47ac10b-58cc-4372-a567-0e02b2c3d479).
        return sprintf("%s-%s-4%s-%x%s-%s", self::getRandomHexStr(8), 
                                            self::getRandomHexStr(4), 
                                            self::getRandomHexStr(3), 
                                            mt_rand(8, 11), 
                                            self::getRandomHexStr(3), 
                                            self::getRandomHexStr(12));
    }
    
    /**
     * Devuelve un IV aleatorio para los métodos encrypt() y decrypt().
     * 
     * @return string IV aleatorio de 16Bytes.
     * @see encrypt()
     * @see decrypt()
     */
    public static function getRandomIV()
    {
        return self::getRandomHexStr(self::IV_LEN);
    }

    /**
     * Encripta cualquier elemento (string, numero, array, objeto, ...) y 
     * devuelve el mismo encriptado como string.  <br />
     * Para desencriptar, usar decrypt().
     * 
     * @param mixed $data Datos a encriptar.
     * @param string $password Contraseña.
     * @param string $iv Vector de inicialización.  Debe ser de 16 Bytes.<br />
     * Conviene emplear el método getRandomIV(). <br />
     * Si no se define ninguno, se asignará uno aleatorio (recomendado).
     * @return mixed String con los datos encriptados
     * o bien FALSE en caso de error.
     * @see getRandomIV()
     * @see decypt()
     */
    public static function encrypt($data, $password, $iv = '')
    {
        if (isset($data) 
            && isset($password)
            && is_string($password)
            && is_string($iv)
        ) {
            $password = self::getHash($password);
            
            if (empty($iv)) {
                $iv = self::getRandomIV();
            }
            
            $codedData = base64_encode(serialize($data));
            if ($codedData) {
                $encString = self::encryptStr($codedData, $password, $iv);
                if ($encString) {
                    return self::getEncFormat($iv, $encString);
                }
            }
        }
        
        return FALSE;
    }
    
    /**
     * Desencripta un string (encriptado por encrypt()).
     * 
     * @param string $encString String a desencriptar.
     * @param string $password Contraseña.
     * @return mixed Elemento desencriptado (string, número, array, objeto, ...)
     * o bien FALSE en caso de error.
     */
    public static function decrypt($encString, $password)
    {
        if (isset($encString) && isset($password)
            && is_string($password)
            && self::isEncrypted($encString)
        ) {
            $iv = self::getIV_fromEncParts(self::getEncParts($encString));
            $encStr = self::getENC_fromEncParts(self::getEncParts($encString));
            $password = self::getHash($password);
            
            $decData = self::decryptStr($encStr, 
                                         $password, 
                                         $iv);
            if ($decData) {
                $decodedData = base64_decode($decData, TRUE);
                if ($decodedData) {
                    return unserialize($decodedData);
                }
            }
            
            return FALSE;
        }  
    }
    
    /**
     * Determina si un string fue encriptado con encrypt(), es decir, 
     * si es un string encriptado válido.
     * 
     * @param string $encString String a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    public static function isEncrypted($encString)
    {
        if (!empty($encString) && is_string($encString)) {
            $encID = self::getEncID();
            $encIDlen = strlen($encID);
            $ivSeparatorPos = $encIDlen;
            $encSeparatorPos = $encIDlen 
                               + self::IV_LEN 
                               + strlen(self::ENC_SEPARATOR);

            if ((strlen($encString) > $encSeparatorPos)
                && (strpos($encString, $encID) == 0) 
                && (strpos($encString, self::ENC_SEPARATOR, 
                           $encIDlen) == $ivSeparatorPos)
                && (strpos($encString, self::ENC_SEPARATOR, 
                           $encSeparatorPos) == $encSeparatorPos)
            ) {
                return TRUE;
            }
        }
        
        return FALSE;
    }
}
