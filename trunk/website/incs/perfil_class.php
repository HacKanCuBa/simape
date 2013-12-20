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
 * Esta clase maneja todo lo referido a los perfiles de permisos:
 * - Crear nuevo
 * - Obtener informacion del actual
 * - Verificar si el usuario tiene o no un permiso determinado
 * - etc
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.1 untested
 */

class UsuarioPerfil extends Empleado
{    
    protected $UsuarioPerfil = array('UsuarioPerfilId' => '',
                                     'Nombre' => '',
                                     'Timestamp' => ''
                                    );
    
    // __ SPECIALS
    
    // __ PRIV
    
    // __ PROT
    
    // __ PUB
    public function getUsuarioPerfilId() 
    {
        return (int) $this->UsuarioPerfil['UsuarioPerfilId'];
    }
    
    public function getPerfilNombre() 
    {
        return (string) $this->UsuarioPerfil['Nombre'];
    }
    
    public function getPerfilTimestamp() 
    {
        return (int) $this->UsuarioPerfil['Timestamp'];
    }
}