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