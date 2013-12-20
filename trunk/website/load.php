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
 * - Busca y carga el archivo de configuración (config.php, loadconfig.php).
 * - Verifica la configuración (verifyconfig.php).
 * - Carga las dependencias de clases automáticamente.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.3
 */

require 'configload.php';
include 'configverify.php';

// Autocarga de dependencias
set_include_path(get_include_path() 
                 . PATH_SEPARATOR . SMP_INC_ROOT . SMP_LOC_INCS);
spl_autoload_extensions('_class.php,_trait.php,_interface.php');
spl_autoload_register();
// --

// --