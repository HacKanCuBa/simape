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
 * loadothers.php
 * Permite cargar archivos adicionales que no puedan ser cargados por la 
 * autocarga de clases.
 * ¡Debe ser llamado después de load.php!
 * - Carga los archivos requeridos a través SESSION:
 * - - Como string:
 * - - - $_SESSION['inc'] = 'arch1,arch2,...'
 * - - - $_SESSION['inc_o'] = 'arch1,arch2,...'
 * - - - $_SESSION['req'] = 'arch1,arch2,...'
 * - - - $_SESSION['req_o'] = 'arch1,arch2,...'
 * 
 * - - Como array:
 * - - - $_SESSION['inc'] = ['arch1', 'arch2', ...]
 * - - - $_SESSION['inc_o'] = ['arch1', 'arch2', ...']
 * - - - $_SESSION['req'] = ['arch1', 'arch2', ...]
 * - - - $_SESSION['req_o'] = ['arch1', 'arch2', ...]
 * 
 * Reconoce todos los parámetros, y cargará el archivo pedido desde:
 * SMP_FS_ROOT . SMP_LOC_INC . [calificador] . ".php"
 * Los calificadores solo pueden tener letras, nros, - y _, ningún otro símbolo
 * está admitido.  
 * Longitud máxima: SMP_LOAD_MAXLEN caracteres.
 * Cantidad máxima de inclusiones (por cada tipo): SMP_LOAD_MAXCANT.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.2
 */

// Constantes
const SMP_LOAD_MAXLEN = 25;
const SMP_LOAD_MAXCANT = 10;
$actionArray = array('inc', 'inc_o', 'req', 'req_o');
// --

// Funciones
/**
 * Determina si un calificador es válido o no.
 *
 * @param string $calificador Calificador a validar
 * @return boolean TRUE si se trata de un calificador válido, FALSE si no.
 */
function isValid_calificador($calificador)
{
    if (!empty($calificador)
        && is_string($calificador)
        && preg_match('/^[a-zA-Z0-9]{1}[a-z0-9A-Z\_\-]{0,'
                      . (SMP_LOAD_MAXLEN - 1)
                      . '}$/', $calificador)
    ) {
        return TRUE;
    }
   
    return FALSE;
}
//--

// Carga los archivos requeridos, si los hay
if (Session::status() == PHP_SESSION_ACTIVE) {
    foreach ($actionArray as $action) {
        $params = Session::get($action, TRUE);
        if (!empty($params)) {
            if (is_string($params)) {
                $calificadorArray = explode(',', $params);
            } elseif (is_array($params)) {
                $calificadorArray = $params;
            } else {
                trigger_error('No se ha podido interpretar $_SESSION["' 
                              . $action
                              . '"], dado que no es un string ni un array',
                              E_USER_ERROR);
                break;
            }
            if (!empty($calificadorArray) && is_array($calificadorArray)) {
                $calificadorCant = 0;
                foreach ($calificadorArray as $calificador) {
                    $calificadorCant++;
                    if ($calificadorCant <= SMP_LOAD_MAXCANT) {
                        if (isValid_calificador($calificador)) {
                            // ATENCION: Debe representarse al archivo con la 
                            // ruta completa!!
                            // De otra manera, se habilita un LFI
                            $file = SMP_FS_ROOT . SMP_LOC_INC
                                    . $calificador . '.php';

                            switch ($action) {
                                case 'inc':
                                    include $file;
                                    break;

                                case 'inc_o':
                                    include_once $file;
                                    break;

                                case 'req':
                                    require $file;
                                    break;

                                case 'req_o':
                                    require_once $file;
                                    break;

                                default:
                                    break;
                            }
                        } else {
                            trigger_error('El calificador "'
                                          . $calificador . '"  no es valido! '
                                          . 'Posible intento de hackeo?',
                                          E_USER_ERROR);
                        }
                    } else {
                        if (strstr($action, 'req')) {
                            $errT = E_USER_ERROR;
                        } else {
                            $errT = E_USER_NOTICE;
                        }
                        trigger_error('Se ha alcanzado la cantidad limite de '
                                      . 'calificadores (' . SMP_LOAD_MAXCANT
                                      . ').  Se detiene la carga' , $errT);
                        break;
                    }
                }
            }
        }
        unset($_SESSION[$action]);
    }
}
// --

// --