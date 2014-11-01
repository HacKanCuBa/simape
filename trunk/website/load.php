<?php

/*****************************************************************************
 *  Este archivo forma parte de SiMaPe
 *  Sistema Integrado de Manejo de Personal
 *  Copyright (C) <2013, 2014>  <Ivan Ariel Barrera Oro>
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
 * Busca y carga init.php, que estará en la raíz.
 * Debe estar en todos los subdirectorios con páginas ejecutables (esto es, 
 * donde haya código a ejecutar, ¡NO en librerias o inclusiones!).
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.22
 */

// Para forzar la ruta de load.php, borrar '//' de las siguientes 2 lineas:
//require_once $location . '../init.php;
///* Buscar load.php
$location = dirname(__FILE__);
do {
    if (file_exists($location . '/init.php')) {
        require_once $location . '/init.php';
        $location = '/';
    } else {
        $location = dirname($location);
    }
} while ($location != '/');
// -- */

if (!defined('SMP_CONFIG')) { 
    die("No se puede encontrar el archivo init.php"); 
}