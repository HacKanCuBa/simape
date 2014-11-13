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
 * init.php
 * Carga todo lo que la aplicación requiere.
 * - Busca y carga el archivo de configuración (configload.php).
 * - Verifica la configuración (configverify.php).
 * - Carga las dependencias de clases automáticamente.
 * 
 * DEBE ESTAR SIEMPRE EN LA RAÍZ!
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.47
 */

// Para asegurar que toda la aplicación está bien hecha
error_reporting(E_ALL);

// Raiz para inclusion de archivos
$loc = dirname(__FILE__);
define('SMP_FS_ROOT', (substr($loc, -1) == '/') ? $loc : $loc .'/');

// Inclusiones
require_once 'configload.php';
require_once SMP_FS_ROOT . SMP_LOC_INC . 'funciones.php';

// Zona horaria
setlocale(LC_TIME, SMP_LOCALE);
date_default_timezone_set(SMP_TIMEZONE);

// Autocarga de dependencias
set_include_path(get_include_path() 
                . PATH_SEPARATOR . SMP_FS_ROOT . SMP_LOC_INC);
spl_autoload_extensions('_class.php,_trait.php,_interface.php');
spl_autoload_register();

// Carga de otras dependencias
//Session::initiate();
//Session::store('inc', [ 'dependencia1.php', 'dependencia2.php' ]);
//Session::store('inc_o', [ 'dependencia1.php', 'dependencia2.php' ]);
//Session::store('req', [ 'dependencia1.php', 'dependencia2.php' ]);
//Session::store('req_o', [ 'dependencia1.php', 'dependencia2.php' ]);
//require_once 'loadothers.php';
// --

// Modo mantenimiento?
if (file_exists(SMP_FS_ROOT . '.mantenimiento')
        && !in_array_partial(IP::getClientIP(), 
                                array_from_string_list(SMP_MAINTENANCE_IP))
) { 
    http_response_code(503);
    header('Location: ' . SMP_WEB_ROOT . 'mantenimiento.html');
    exit();
}

// --