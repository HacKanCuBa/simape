<?php

/*****************************************************************************
 *  SiMaPe
 *  Sistema Integrado de Manejo de Personal
 *  Copyright (C) <2013>  <Ivan Ariel Barrera Oro>
 *  ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *****************************************************************************/

/**
 * Clase que comprende funciones varias para sanitizar entrada de usuario.
 * 
 * Ejemplo de uso:
 * <pre><code>
 * $miStr_sanitizado = Sanitizar::value($miStr);
 * $array_sanitizado = Sanitizar::value(array('', ..., array(...), ...));
 * 
 * $miSanitizado = new Sanitizar;
 * $miSanitizado->setValue($miVar_dirty);
 * $miVar_sanitizada = $miSanitizado->getValue();
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.71
 */
class Sanitizar
{
    protected $DirtyValue;
    
    // __ SPECIALS
    /**
     * Almacena un valor a ser sanitizado.  Usar getValue() 
     * para obtener el valor sanitizado.
     * 
     * @param mixed $value Valor a ser sanitizado.
     */
    public function __construct($value = NULL) 
    {
        $this->setValue($value);
    }
    // __ PRIV
    
    // __ PROT
    /**
     * Sanitiza un string.
     * 
     * @param string $str String a sanitizar
     * @return mixed Devuelve un string sanitizado, o bien FALSE en caso de 
     * error.
     */
    protected static function string($str) 
    {
        if (isset($str) && is_string($str)) {
            return filter_var($str, 
                              FILTER_SANITIZE_STRING, 
                              FILTER_FLAG_STRIP_LOW);
        }
        
        return FALSE;
    }
    
    /**
     * Sanitiza un array uni o multidimensional.  Devuelve el mismo, con las
     * mismas dimensiones e índices, sanitizado.  Si ocurre algún error, 
     * devuelve FALSE
     * 
     * @param array $array Array uni o multidimensional.
     * @return mixed Array sanitizado, o boolean FALSE si ocurre algún error.
     */
    protected static function arreglo(array $array) 
    {
        if (isset($array) && is_array($array)) {
            $sanitized = array();
            foreach ($array as $key => $value) {
                $sanitized[$key] = self::value($value);
            }

            return $sanitized;
        }
        
        return FALSE;
    }
    // __ PUB
    /**
     * Sanitiza y devuelve un valor cualquiera (string, array, escalares, 
     * objetos, etc.).
     * NOTA: valores de tipo distinto a string o array serán devueltos sin 
     * sanitizar, dado que no requieren ser sanitizados. 
     * 
     * @param mixed $valor Valor a sanitizar.
     * @return mixed Valor sanitizado.  
     * En caso de error, realiza una llamada del sistema para 
     * notificarlo.  Usar error_get_last() u otra para determinarlo.
     */
    public static function value($value)
    {
        try {
            if (isset($value)) {
                if (is_string($value)) {
                    return self::string($value);
                } elseif (is_array($value)) {
                    return self::arreglo($value);
                } else {
                    // No es sanitizable
                    // P.E.: enteros, floats, objetos
                    return $value;
                }
            }
            
            return NULL;
        } catch (Exception $err) {
            trigger_error(__METHOD__ . '(): El valor indicado no es valido.  '
                          . 'Detalles: ' . $err->getMessage());
        }
    }

    /**
     * Sanitiza un valor de la variable super global $_POST
     * 
     * @param mixed $POSTkey Nombre del índice de $_POST, string o int.
     * @return mixed Devuelve un string sanitizado.
     * En caso de error, realiza una llamada del sistema para 
     * notificarlo.  Usar error_get_last() u otra para determinarlo.
     */
    public static function glPOST($POSTkey) 
    {
        try {
            if (isset($POSTkey)) {
                return filter_input(INPUT_POST, $POSTkey, FILTER_SANITIZE_STRING, 
                                    FILTER_FLAG_STRIP_LOW);
            }
            
            return NULL;
        } catch (Exception $err) {
            trigger_error(__METHOD__ . '(): El valor indicado no es valido.  '
                          . 'Detalles: ' . $err->getMessage());
        }
    }

    /**
     * Sanitiza un valor de la variable super global $_GET
     * 
     * @param mixed $GETkey Nombre del índice de $_GET, string o int.
     * @return mixed Devuelve un string sanitizado.
     * En caso de error, realiza una llamada del sistema para 
     * notificarlo.  Usar error_get_last() u otra para determinarlo.
     */
    public static function glGET($GETkey) 
    {
        try {
            if (isset($GETkey)) {
                return filter_input(INPUT_GET, $GETkey, FILTER_SANITIZE_STRING, 
                                    FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
            }
        
            return NULL;
        } catch (Exception $err) {
            trigger_error(__METHOD__ . '(): El valor indicado no es valido.  '
                          . 'Detalles: ' . $err->getMessage());
        }
    }

    /**
     * Sanitiza un valor de la variable super global $_SERVER
     * 
     * @param string $SERVERkey Nombre del índice de $_SERVER
     * @return mixed Devuelve un string sanitizado.
     * En caso de error, realiza una llamada del sistema para 
     * notificarlo.  Usar error_get_last() u otra para determinarlo.
     */
    public static function glSERVER($SERVERkey) 
    {
        try {
            if (isset($SERVERkey)) {
                return filter_input(INPUT_SERVER, $SERVERkey, FILTER_SANITIZE_STRING, 
                                    FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
            }
        
            return NULL;
        } catch (Exception $err) {
            trigger_error(__METHOD__ . '(): El valor indicado no es valido.  '
                          . 'Detalles: ' . $err->getMessage());
        }
    }
    
    /**
     * Sanitiza y devuelve un valor de la variable super global $_SESSION.
     * 
     * @param mixed $SESSIONkey Nombre del índice de $_SESSION, string o int.
     * @return mixed Devuelve un valor sanitizado.
     * En caso de error, realiza una llamada del sistema para 
     * notificarlo.  Usar error_get_last() u otra para determinarlo.
     */
    public static function glSESSION($SESSIONkey) 
    {
        try {
            if (session_status() == PHP_SESSION_ACTIVE 
                && isset($_SESSION[$SESSIONkey])
            ) {
                return self::value($_SESSION[$SESSIONkey]);
            }
            
            return NULL;
        } catch (Exception $err) {
            trigger_error(__METHOD__ . '(): El valor indicado no es valido.  '
                          . 'Detalles: ' . $err->getMessage());
        }
    }
    
    /**
     * Almacena un valor a ser sanitizado.  Usar getValue() 
     * para obtener el valor sanitizado.
     * 
     * @param mixed $value Valor a ser sanitizado.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setValue($value)
    {
        // Atento con los tipos numéricos!
        if (isset($value)) {
            $this->DirtyValue = $value;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve el valor sanitizado que habia sido almacenado con setValue.
     * 
     * @return mixed Devuelve el valor almacenado, sanitizado, 
     * o NULL si no hay valor almacenado.
     */
    public function getValue()
    {
        if (isset($this->DirtyValue)) {
            return $this->value($this->DirtyValue);
        }
        
        return NULL;
    }
}