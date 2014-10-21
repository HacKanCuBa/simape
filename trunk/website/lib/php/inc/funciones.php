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
 * Devuelve un array a partir una lista separada por el separador indicado.  
 * Asimismo, si la lista tiene valores del tipo "llave=valor", el array será 
 * asociativo donde el índice será <i>llave</i>.  Si solo contiene valores, 
 * será numerado en el orden recibido.
 * 
 * @param string $list Lista de valores.
 * @param string $separator Separador.  Por defecto ','.
 * @return array|FALSE Array de valores o FALSE en caso de error.
 */
function array_from_string_list($list, $separator = ',')
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
        
        return $arr;
    }
    
    return FALSE;
}

/**
 * Devuelve una lista en string a partir de un array, ya sea asociativo, 
 * numerado o mixto.
 * @param array $array Array.
 * @param boolean $always_assoc TRUE para que la lista sea siempre asociativa, 
 * FALSE (por defecto) para evitar los índices numerados.<br />
 * Esto es [1, 'a' => 2] será devuelto como "0=1,a=2" en el primer caso, 
 * y "1,a=2" en el segundo.
 * @return string|FALSE String lista, o FALSE en caso de error.
 */
function string_list_from_array($array, $always_assoc = FALSE)
{
    if (is_array($array)) {
        $str = '';
        foreach ($array as $key => $value) {
            $str .= (is_numeric($key) && !$always_assoc) ? '' : $key . '=';
            $str .= $value . ',';
        }
        return substr($str, 0, -1);
    }
    
    return FALSE;
}

/**
 * Determina si la conexión actual se realiza vía SSL o no.
 * @return boolean TRUE indica conexión SSL.  FALSE, conexión plana.
 */
function is_connection_ssl()
{
    return boolval(Sanitizar::glSERVER('HTTPS'));
}

const FORCE_CONNECT_PLAIN = 1;
const FORCE_CONNECT_SSL = 2;
/**
 * Fuerza la conexión actual al modo seleccionado:
 * <ol>
 * <li>FORCE_CONNECT_PLAIN</li>
 * <li>FORCE_CONNECT_SSL</li>
 * </ol>
 * Si la conexión actual no se encuentra en el modo indicado, recarga el script.
 * Si no, continúa la ejecución.
 * Si se desea forzar el modo SSL, SMP_SSL debe ser TRUE, o la conexión 
 * permanecerá en modo actual.
 * @param int $mode Modo de conexión a forzar.
 */
function force_connect($mode = FORCE_CONNECT_PLAIN) 
{
    $file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0]['file'];
    $loc = str_ireplace(SMP_FS_ROOT, '', dirname($file) . '/');
    $loc = (empty($loc) ? '' : $loc) . basename($file);
    $exit = FALSE;
    switch ($mode) {
        case FORCE_CONNECT_PLAIN:
            $exit = is_connection_ssl() ? Page::go_to($loc, NULL, NULL, TRUE) : FALSE;
            break;

        case FORCE_CONNECT_SSL:
            $exit = is_connection_ssl() ? FALSE : (SMP_SSL ? Page::go_to($loc) : FALSE);
            break;
        
        default:
            break;
    }
    $exit ? exit() : NULL;
}