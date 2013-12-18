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
 * @version 0.2
 */
//
function shorthand_to_bytes($val) 
{
    // http://us1.php.net/ini_get
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

// Verificar configuración
if (!defined('__SMP_CONFIG')) { 
    die("No se pudo cargar el archivo config.php"); 
}

setlocale(LC_TIME, __SMP_LOCALE);
date_default_timezone_set(__SMP_TIMEZONE);

$upload_max_filesize = shorthand_to_bytes(ini_get('upload_max_filesize'));
$post_max_size = shorthand_to_bytes(ini_get('post_max_size'));
$memory_limit = shorthand_to_bytes(ini_get('memory_limit'));

if (($upload_max_filesize > $post_max_size) 
     || ($post_max_size > $memory_limit)
) {
    die('ERROR GRAVE: Hay un problema con la configuracion.  Verificar que '
        . 'upload_max_filesize <= post_max_size <= memory_limit');
}

if ((constant('__SMP_FILE_MAXUPLOADSIZE') > $upload_max_filesize) 
     || (constant('__SMP_FILE_MAXIMGSIZE') > $upload_max_filesize)
) {
    die('ERROR GRAVE: __SMP_FILE_MAXUPLOADSIZE y __SMP_FILE_MAXIMGSIZE no pueden ser mayores '
        . 'que upload_max_filesize');
}

if ((constant('__SMP_FILE_MAXUPLOADSIZE') > constant('__SMP_FILE_MAXSTORESIZE'))
     || (constant('__SMP_FILE_MAXIMGSIZE') > constant('__SMP_FILE_MAXSTORESIZE'))
) {
    die('ERROR GRAVE: __SMP_FILE_MAXUPLOADSIZE y __SMP_FILE_MAXIMGSIZE no pueden ser mayores'
        . ' que __SMP_FILE_MAXSTORESIZE!!');
}

define('__SMP_CONFIGVERIFY', TRUE);