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
 * Funciones varias
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 */

/**
 * Elimina la columna indicada de la matriz dada (la primer columna es 0).
 * La matriz dada será modificada.
 * @param array $matrix Matriz
 * @param type $col Columna.
 * @return array Matriz modificada.
 * @link http://stackoverflow.com/questions/16564650/best-way-to-delete-column-from-multidimensional-array Fuente
 */
function delete_column_from_matrix(&$matrix, $col)
{
    return array_walk($matrix, 
                        function (&$v) use ($col) { 
                                                array_splice($v, $col, 1);
    });
}

/**
 * Elimina la fila indicada de la matriz dada (la primer fila es 0).  
 * La matriz dada será modificada.
 * @param array $matrix Matriz
 * @param type $row Fila.
 * @return array Matriz modificada.
 * @link http://stackoverflow.com/questions/16564650/best-way-to-delete-column-from-multidimensional-array Fuente
 */
function delete_row_from_matrix(&$matrix, $row)
{
    return array_splice($matrix, $row, 1);
}

/**
 * Devuelve la IP del servidor.
 * @return string IP del servidor.
 */
function server_ip()
{
    return SMP_SERVER_ADDR ?: 
                    (stristr(Sanitizar::glSERVER('SERVER_SOFTWARE'), 'win') ? 
                                        Sanitizar::glSERVER('LOCAL_ADDR') : 
                                        Sanitizar::glSERVER('SERVER_ADDR'));
}

/**
 * Devuelve la IP del cliente, tratando de resolver aún en caso de proxy.
 * No es 100% fiable, es más efectivo un script en java o similar.
 * @return string IP del cliente o string vacío.
 */
function client_ip()
{
    // https://stackoverflow.com/questions/15699101/get-the-client-ip-address-using-php
    $possible_ip = [    
                        Sanitizar::value(getenv('HTTP_CLIENT_IP')),
                        Sanitizar::value(getenv('HTTP_X_FORWARDED_FOR')),
                        Sanitizar::value(getenv('HTTP_X_FORWARDED')),
                        Sanitizar::value(getenv('HTTP_FORWARDED_FOR')),
                        Sanitizar::value(getenv('HTTP_CLIENT_IP')),
                        Sanitizar::value(getenv('HTTP_FORWARDED')),
                        Sanitizar::glSERVER('HTTP_CLIENT_IP'),
                        Sanitizar::glSERVER('HTTP_X_FORWARDED_FOR'),
                        Sanitizar::glSERVER('HTTP_X_FORWARDED'),
                        Sanitizar::glSERVER('HTTP_FORWARDED_FOR'),
                        Sanitizar::glSERVER('HTTP_FORWARDED'),
                        Sanitizar::glSERVER('REMOTE_ADDR'),
                        Sanitizar::value(getenv('REMOTE_ADDR')),
                    ];
    $client_ip = '';
    
    foreach ($possible_ip as $ip) {
        $client_ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if ($client_ip) {
            break;
        }
    }
    
    return $client_ip ?: '';
}

/**
 * Devuelve un array a partir una lista separada por el separador indicado.  
 * Asimismo, si la lista tiene valores del tipo "llave=valor", el array será 
 * asociativo donde el índice será <i>llave</i>.  Si solo contiene valores, 
 * será numerado en el orden recibido.  Si el valor recibido es un array, 
 * lo devuelve sin procesar.  Si el valor recibido es otra cosa, lo devuelve 
 * como array(valor).
 * 
 * @param string $list Lista de valores.
 * @param string $separator [opcional]<br />
 * Separador.  Por defecto ','.
 * @param boolean $no_assoc [opcional]<br />
 * TRUE para forzar al índice numerado 
 * (eliminará todas las llaves), FALSE para dejarlo como indique la lista 
 * (por defecto).
 * @return array Array de valores.
 */
function array_from_string_list($list, $separator = ',', $no_assoc = FALSE)
{
    if (is_string($list)) {
        $arr = array();
        foreach (explode($separator, $list) as $item) {
            if (stristr($item, '=')) {
                list($key, $value) = explode('=', $item);
                $arr[$key] = $value;
            } else {
                $arr[] = $item;
            }
        }      
        $ret = $no_assoc ? array_values($arr) : $arr;
    } elseif (is_array($list)) {
        $ret = $list;
    } else {
        $ret = array($list);
    }
    
    return $ret;
}

/**
 * Devuelve una lista en string a partir de un array, ya sea asociativo, 
 * numerado o mixto.  Si el valor recibido es un string, lo devuelve sin 
 * procesar.  Si es cualquier otra cosa, tratará de convertirlo a string 
 * mediante strval().
 * @param array $array Array.
 * @param string $separator [opcional]<br />
 * Separador.  Por defecto ','.
 * @param boolean $always_assoc [opcional]<br />
 * TRUE para que la lista sea siempre asociativa, 
 * FALSE (por defecto) para evitar los índices numerados.<br />
 * Esto es [1, 'a' => 2] será devuelto como "0=1,a=2" en el primer caso, 
 * y "1,a=2" en el segundo.
 * @return string String lista.
 */
function string_list_from_array($array, $separator = ',', $always_assoc = FALSE)
{
    if (is_array($array)) {
        $str = '';
        foreach ($array as $key => $value) {
            $str .= (is_numeric($key) && !$always_assoc) ? '' : $key . '=';
            $str .= $value . $separator;
        }
        $ret = substr($str, 0, -1);
    } else {
        $ret = strval($array);
    }
    
    return $ret;
}

/**
 * Determina si la conexión actual se realiza vía SSL o no.
 * @return boolean TRUE indica conexión SSL.  FALSE, conexión plana.
 */
function is_connection_ssl()
{
    return boolval(Sanitizar::glSERVER('HTTPS'));
}

function send_to_browser($data = NULL, $newtab = FALSE)
{
    header('Content-Type: application/pdf');
    if(!headers_sent()) {
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) OR empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
                // don't use length if server using compression
                header('Content-Length: ' . strlen($data));
        }
        header('Content-disposition: inline; filename="Ficha"');
        header('Cache-Control: public, must-revalidate, max-age=0'); 
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); 
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        //header('Content-disposition: attachment; filename="Ficha"');
        echo ($newtab ? "<script type='text/javascript'>window.open('data:application/pdf;base64, '" . base64_encode($data) .  ");</script>" : $data);
        return TRUE;
    }
    
    return FALSE;
}

/**
 * Determina si un array es asociativo o no.
 * @param array $array Array.
 * @return boolean TRUE si el array es asociativo, FALSE si no.
 * @link https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential Stackoverflow
 */
function is_assoc($array) 
{
  return (is_array($array) ? boolval(count(array_filter(array_keys($array), 
                                                        'is_string'))) 
                            : FALSE);
}

/**
 * Si se ha definido un email en SMP_ADMIN_EMAIL, creará un enlace mailto
 * en la palabra 'administrador'.  Si no, simplemente devolverá esta palabla.
 * 
 * @return string Palabra 'administrador' con enlace mailto o no.
 */
function contactar_administrador()
{
    return SMP_ADMIN_EMAIL 
            ?  "<a href='mailto:" . SMP_ADMIN_EMAIL . "'>administrador</a>" 
            : "administrador";
}

/**
 * Determina si el valor 'needle' se encuentra en el array 'haystack'.  No
 * distingue entre mayúsculas y minúsculas.
 * @param mixed $needle Valor buscado.
 * @param array $haystack Array donde buscar.
 * @return boolean TRUE si se encontró el valor, FALSE si no.
 */
function in_array_partial($needle, array $haystack) 
{
    $ret = FALSE;
    foreach ($haystack as $value) {
        if (stristr($value, $needle)) {
            $ret = TRUE;
            break;
        }
    }
    return $ret;
}