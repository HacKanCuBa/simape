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
 * @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 * 
 *****************************************************************************/

// -- FILE
function files_get_dirty($id) 
{
    return $_FILES[$id];
}

function files_get($id, $key) 
{
    if (!empty($id) && !empty($key)) {
        return sanitizar_str($_FILES[$id][$key]);
    }
    
    return NULL;
}
function files_get_name($id) 
{
    return file_get($id, 'name');
}

function files_get_size($id) 
{
    return file_get($id, 'size');
}

function files_get_type($id) 
{
    return file_get($id, 'type');
}

function files_get_tmpname($id) 
{
    return file_get($id, 'tmp_name');
}

function files_get_error($id) 
{
    return file_get($id, 'error');
}
// --

define('FUNC_FILE', TRUE);