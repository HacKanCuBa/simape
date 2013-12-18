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
 * configload.php
 * Busca y carga config.php
 * Sirve para poder rediseñar el sitio con facilidad, y mover config.php a 
 * otro directorio (superior) fuera del alcance del usuario de apache, por 
 * seguridad.  Si se desea poner config.php en un directorio determinado, 
 * comentar las lineas indicadas y forzar la ruta como se especifica.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.4
 */

// Para forzar la ruta de config.php, borrar '//' de las siguientes 2 lineas:
//require_once '/mi/ruta/a/config.php';
///* Buscar config.php
$location = dirname(__FILE__);
do {
    if (file_exists($location . '/config.php')) {
        require_once $location . '/config.php';
        $location = '/';
    } else {
        $location = dirname($location);
    }
} while ($location != '/');
// -- */

if (!defined('__SMP_CONFIG')) { 
    die("No se puede encontrar el archivo config.php"); 
}

define('__SMP_CONFIGLOAD', TRUE);