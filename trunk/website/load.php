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
 * load.php
 * Es un bootstrap loader para el sitio:
 * - Busca y carga el archivo de configuración (config.php, loadconfig.php)
 * - Verifica la configuración (verifyconfig.php)
 * - Carga los archivos requeridos por SESSION:
 * - - inc=arch1,arch2,...
 * - - inc_o=arch1,arch2,...
 * - - req=arch1,arch2,...
 * - - req_o=arch1,arch2,...
 * Reconoce todos los parámetros, y cargará el archivo pedido desde:
 * __SMP_INC_ROOT . __SMP_LOC_INCS . [calificador] . php
 * Los calificadores solo pueden tener letras, nros, - y _, ningún otro símbolo 
 * está admitido.  
 * Longitud máxima: __SMP_LOAD_MAXLEN caracteres.
 * Cantidad máxima de inclusiones: __SMP_LOAD_MAXCANT.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.7
 */
//
const __SMP_LOAD_MAXLEN = 25;
const __SMP_LOAD_MAXCANT = 20;
$actionArray = array('inc', 'inc_o', 'req', 'req_o');
//

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
        && preg_match('/^[a-z0-9A-Z_-]{1,' 
                      . constant('__SMP_LOAD_MAXLEN') 
                      . '}$/', $calificador)
    ) {
        return TRUE;
    }
    
    return FALSE;
}

require 'configload.php';
include 'configverify.php';

require __SMP_INC_ROOT . __SMP_LOC_INCS . 'class_sanitizar.php';

// Carga los archivos requeridos, si los hay
if (session_status() == PHP_SESSION_ACTIVE) {
    foreach ($actionArray as $action) {
        $params = Sanitizar::glSESSION($action);
        if (!empty($params)) {
            if (is_string($params)) {
                $calificadorArray = explode(',', $params);
            } elseif (is_array($params)) {
                $calificadorArray = $params;
            } else {
                trigger_error('No se ha podido interpretar $_SESSION["' . $action 
                              . '"], dado que no es un string ni un array', 
                              E_USER_ERROR);
                break;
            }
            if (!empty($calificadorArray) && is_array($calificadorArray)) {
                $calificadorCant = 0;
                foreach ($calificadorArray as $calificador) {
                    $calificadorCant++;
                    if ($calificadorCant <= __SMP_LOAD_MAXCANT) {
                        if (isValid_calificador($calificador)) {
                            // ATENCION: Debe representarse al archivo con la ruta 
                            // completa!!
                            // De otra manera, se habilita un LFI
                            $file = __SMP_INC_ROOT . __SMP_LOC_INCS 
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
                                      . 'calificadores (' . __SMP_LOAD_MAXCANT 
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
define('__SMP_LOAD', TRUE);