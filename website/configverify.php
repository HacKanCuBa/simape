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
 * configverify.php
 * Verifica que el archivo de configuración haya sido cargado y que la 
 * configuración sea correcta.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.3
 */
//
function shorthand_to_bytes($val) 
{
    // http://us1.php.net/ini_get
    $mod = strtolower(substr(trim($val), -1));
    $value = intval(substr(trim($val), 0, -1));
    switch($mod) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $value *= 1024 * 1024 * 1024;
        case 'm':
            $value *= 1024 * 1024;
        case 'k':
            $value *= 1024;
    }

    return $value;
}

// Verificar configuración
if (!defined('SMP_CONFIG')) { 
    die("No se pudo cargar el archivo config.php"); 
}

setlocale(LC_TIME, SMP_LOCALE);
date_default_timezone_set(SMP_TIMEZONE);

$upload_max_filesize = shorthand_to_bytes(ini_get('upload_max_filesize'));
$post_max_size = shorthand_to_bytes(ini_get('post_max_size'));
$memory_limit = shorthand_to_bytes(ini_get('memory_limit'));

if (($upload_max_filesize > $post_max_size) 
     || ($post_max_size > $memory_limit)
) {
    die('ERROR GRAVE: Hay un problema con la configuracion.  Verificar que '
        . 'upload_max_filesize <= post_max_size <= memory_limit');
}

if ((constant('SMP_FILE_MAXUPLOADSIZE') > $upload_max_filesize) 
     || (constant('SMP_FILE_MAXIMGSIZE') > $upload_max_filesize)
) {
    die('ERROR GRAVE: SMP_FILE_MAXUPLOADSIZE y SMP_FILE_MAXIMGSIZE no pueden ser mayores '
        . 'que upload_max_filesize');
}

if ((constant('SMP_FILE_MAXUPLOADSIZE') > constant('SMP_FILE_MAXSTORESIZE'))
     || (constant('SMP_FILE_MAXIMGSIZE') > constant('SMP_FILE_MAXSTORESIZE'))
) {
    die('ERROR GRAVE: SMP_FILE_MAXUPLOADSIZE y SMP_FILE_MAXIMGSIZE no pueden ser mayores'
        . ' que SMP_FILE_MAXSTORESIZE!!');
}

define('SMP_CONFIGVERIFY', TRUE);