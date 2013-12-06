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

/*
 * Este archivo busca a config.php
 * Sirve para poder rediseñar el sitio con facilidad
 * 
 * NOTA: hasta 4 subniveles solamente!
 */

// Buscar config.php
if (file_exists('config.php')) {
    require 'config.php';
} elseif (file_exists(dirname(__FILE__) . '/config.php')) {
    require dirname(__FILE__) .'/config.php';
} elseif (file_exists(dirname(dirname(__FILE__)) . '/config.php')) {
    require dirname(dirname(__FILE__)) . '/config.php';
} elseif (file_exists(dirname(dirname(dirname(__FILE__))) . '/config.php')) {
    require dirname(dirname(dirname(__FILE__))) . '/config.php';
} elseif (file_exists (dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php')) {
    require dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
} 

if (!defined('CONFIG')) { 
    die("No se puede encontrar el archivo config.php"); 
}

// Cargar funciones
require_once INC_ROOT . LOC_FUNCIONES;

// Verificar configuración
$upload_max_filesize = shorthand_to_bytes(ini_get('upload_max_filesize'));
$post_max_size = shorthand_to_bytes(ini_get('post_max_size'));
$memory_limit = shorthand_to_bytes(ini_get('memory_limit'));

if (($upload_max_filesize > $post_max_size) 
     || ($post_max_size > $memory_limit)
) {
    die('ERROR GRAVE: Hay un problema con la configuracion.  Verificar que '
        . 'upload_max_filesize <= post_max_size <= memory_limit');
}

if ((constant('FILE_MAXUPLOADSIZE') > $upload_max_filesize) 
     || (constant('FILE_MAXIMGSIZE') > $upload_max_filesize)
) {
    die('ERROR GRAVE: FILE_MAXUPLOADSIZE y FILE_MAXIMGSIZE no pueden ser mayores '
        . 'que upload_max_filesize');
}

if ((constant('FILE_MAXUPLOADSIZE') > constant('FILE_MAXSTORESIZE'))
     || (constant('FILE_MAXIMGSIZE') > constant('FILE_MAXSTORESIZE'))
) {
    die('ERROR GRAVE: FILE_MAXUPLOADSIZE y FILE_MAXIMGSIZE no pueden ser mayores'
        . ' que FILE_MAXSTORESIZE!!');
}

?>