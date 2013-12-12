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

/**
 * Esta clase maneja todo lo referido al usuario:
 * - Autenticacion
 * - Permisos
 * - Creacion nuevo
 * - Cambio de datos
 * - etc
 */

class Usuario extends UsuarioPerfil {    
    protected $Nombre, $PasswordPlain, $UsuarioId, $Empleado, $Activo, 
              $db;
    
    private $UID, $PasswordSalted;
    
    // Metodos
    function __construct(DB $db = NULL, $Nombre = NULL, $UID = NULL, 
                         Empleado $Empleado = NULL,
                         $PasswordPlain = NULL, $Activo = NULL
    ) {
        // Busca en la DB si ya existe un usuario con los datos pasados.
        //  - De ser asi, setea todas las variables internas como corresponda.
        // Si difieren parametros, devolvera error (P.E.: si existe el nombre y 
        // el UID pero pertenecen a usuarios distintos).
        // __ Params de busqueda: Nombre, UUID, Empleado
        //  - Si no encuentra en la DB, prepara para crear uno nuevo.
        
    }
    
    // Set
    public function setNombre(string $NuevoNombreUsuario) {
        if (isValid_username($NuevoNombreUsuario)) {
            $this->Nombre = $NuevoNombreUsuario;
        }
    }
    
    public function setPassword(string $NuevoPassword) {
        if (isValid_password($NuevoPassword)) {
            $this->PasswordPlain = $NuevoPassword;
        }
    }
  
    public function setActivo(boolean $NuevoActivo) {
        if (!empty($NuevoActivo) 
            && is_bool($NuevoActivo)) {
            $this->Activo = $NuevoActivo;
        }
    }
    
    public function setUID(string $NuevoUID) {
        if (isValid_uuid($NuevoUID)) {
            $this->UID = $NuevoUID;
        }
    }
    
    // Get
    public function getNombre() {
        return $this->NombreUsuario;
    }
}