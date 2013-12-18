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
 * Maneja la carga y almacenamiento de datos en la variable super global SESSION
 * 
 * Ejemplo de uso:
 * <pre><code>
 * 
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.4
 */
class Session
{
    // __ SPECIALS
    /**
     * Guarda el valor en la sesión: $_SESSION[$key] = $value.
     * 
     * @param mixed $key Índice, puede ser un string o un entero.
     * @param mixed $value Valor, puede ser cualquier elemento serializable
     * (se recomienta emplear valores escalares o arrays, y evitar objetos).
     * @param boolean $sanitize Si es TRUE, sanitiza el valor antes de 
     * almacenarlo. (FALSE por defecto)
     */
    public function __construct($key, $value = NULL, $sanitize = FALSE) 
    {
        if (isset($key)) {
            self::set($key, $value, $sanitize);
        }
    }
    // __ PRIV
    
    // __ PROT
    
    // __ PUB
    /**
     * Guarda un valor en la sesión: $_SESSION[$key] = $value.
     * 
     * @param mixed $key Índice, puede ser un string o un entero.
     * @param mixed $value Valor, puede ser cualquier elemento serializable
     * (se recomienta emplear valores escalares o arrays, y evitar objetos).
     * @param boolean $sanitize Si es TRUE, sanitiza el valor antes de 
     * almacenarlo. (FALSE por defecto)
     * @return boolean TRUE si se almacenó el valor satisfactoriamente, 
     * FALSE si no.
     */
    public static function set($key, $value = NULL, $sanitize = FALSE)
    {
        if (isset($key) 
            && (is_string($key) || is_integer($key))
        ) {
            if ($sanitize) {
                $_SESSION[$key] = Sanitizar::value($value);
            } else {
                $_SESSION[$key] = $value;
            }
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un valor almacenado en la sesión: $_SESSION[$key].  Si el valor
     * no existe, devuelve NULL.  El parámetro $sanitize determina si se 
     * sanitizará el valor previamente (TRUE) o no (FALSE, por defecto).  En 
     * caso de error, se empleara una llamada del sistema para notificarlo.
     * 
     * @param mixed $key Índice, string o int.
     * @param boolean $sanitize TRUE para sanitizar el valor antes de 
     * devolverlo, FALSE para devolverlo sin sanitizar (por defecto).
     * @return mixed Valor almacenado en $_SESSION[$key] o NULL si dicho valor
     * no existe.  En caso de error, realiza una llamada del sistema para 
     * notificarlo.  Usar error_get_last() u otra para determinarlo.
     */
    public static function get($key, $sanitize = FALSE)
    {
        if (isset($key) 
            && (is_int($key) || is_string($key))
        ) {
            if (isset($_SESSION[$key])) {
                if ($sanitize) {
                    return Sanitizar::glSESSION($key);
                } else {
                    return $_SESSION[$key];
                }
            } else {
                return NULL;
            }
        } else {
            trigger_error(__METOD__ . '(): El indice $key indicado ($_SESSION["$key"]) '
                          . 'no es valido');
        }
    }
}

