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
 * Busca 'etc' y carga config.php
 * Sirve para poder rediseñar el sitio con facilidad, y mover el directorio
 * de configuraciones 'etc' a otro directorio (superior) fuera del alcance del 
 * usuario de apache, por seguridad.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.61
 */

// Para forzar la ruta de config.php, borrar '//' de las siguientes 2 lineas:
//const SMP_LOC_ETC = 'etc/';
///* Buscar config.php
// Primero probar el valor por defecto, para mayor velocidad
if (file_exists('etc/config.php')) {
    define('SMP_LOC_ETC', 'etc/');
} else {
    $location = dirname(__FILE__);
    do {
        $dirs = scandir($location);
        foreach ($dirs as $dir) {
            if ($dir == 'etc' && file_exists($location . '/etc/config.php')) {
                define('SMP_LOC_ETC', $location . '/etc/');
                break;
            }
        }
        $location = dirname($location);    
    } while (!defined('SMP_LOC_ETC') && $location != '/');  
}// -- */

require_once SMP_LOC_ETC . 'config.php';