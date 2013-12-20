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
 * Esta clase maneja todo lo referido al usuario:
 * - Autenticacion
 * - Permisos
 * - Creacion nuevo
 * - Cambio de datos
 * - etc
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.7 untested
 */
class Usuario extends UsuarioPerfil 
{    
    protected $Usuario = array ('UsuarioId' => '',
                                'EmpleadoId' => '',
                                'UsuarioPerfilId' => '',
                                'Nombre' => '',
                                'UID' => '',
                                'PasswordSalted' => '',
                                'Activo' => '',
                                'CreacionTimestamp' => '',
                                'ModificacionTimestamp' => ''
                                ),
              $db, $crypto, $password,
              $EsNuevoUsuario;
    
    // Metodos
    // __ SPECIALS
    /**
     * Busca en la DB si ya existe un usuario con los datos pasados:
     * Nombre, UID o Empleado, en ese orden.
     * Si lo encuentra, recupera todos los datos desde la DB.  Al mismo tiempo,
     * considera que se estará actualizando dicho usuario con los nuevos datos
     * pasados.
     * Si no lo encuentra, considera que se esta creando un nuevo usuario.
     * 
     * @param string $Nombre Nombre de usuario.
     * @param string $UID UID del usuario.
     * @param Empleado $Empleado Objeto Empleado.
     * @param string $PasswordPlain Nueva clave en texto plano.
     * @param boolean $Activo TRUE para indicar que el nuevo usario estará 
     * activo, FALSE para desactivarlo.
     */
    function __construct($Nombre = NULL, $UID = NULL, 
                          Empleado $Empleado = NULL,
                          $PasswordPlain = NULL, $Activo = TRUE
    ) {       
        $this->db = new DB;
        $this->crypto = new Crypto();
        
        // Búsqueda
        $UsuarioId = 0;
        if (!empty($Nombre)) {
            $UsuarioId = $this->findUsuarioId_usingNombre($Nombre);
        } elseif (!empty($UID)) {
            $UsuarioId = $this->findUsuarioId_usingUID($UID);
        } elseif (!empty($Empleado) && is_a($Empleado, 'Empleado')) {
            $UsuarioId = $this->findUsuarioId_usingEmpleadoId(
                                        $Empleado->getEmpleadoId);
        }
        
        if ($UsuarioId > 0) {
            // Encontrado
            $this->Usuario['UsuarioId'] = $UsuarioId;
            $data = $this->getUserTblFromDB();
            if (is_array($data)) {
                $this->Usuario = &$data;
                $this->Empleado['EmpleadoId'] = $this->Usuario['EmpleadoId'];
                $this->UsuarioPerfil['UsuarioPerfilId'] = 
                                           $this->Usuario['UsuarioPerfilId'];
                $this->EsNuevoUsuario = FALSE;
            } else {
                // Error critico!
                // Encontró el UsuarioId, pero algo sucedió en la consulta de
                // datos... DB desconectada?
            }
        } else {
            // No encotrado
            $this->EsNuevoUsuario = TRUE;
            $this->setUID($this->crypto->getUID());
            $this->setUsuarioCreacionTimestamp(time());
        }
        
        // Ya sea que se trate de uno nuevo o de actualizar uno existente.
        if (!empty($Activo)) {
            $this->setActivo($Activo);
        }
        if (!empty($Nombre)) {
            $this->setUsuarioNombre($Nombre);
        }
        if (!empty($PasswordPlain)) {
            $this->setPasswordPlain($PasswordPlain);
        }
    }
    // __ PRIV
    
    // __ PROT    
    protected function isValid_username($username) 
    {
        /**
         * Valida un string y determina si cumple las restricciones impuestas 
         * en la configuración sobre los nombres de usuario.
         * 
         * @param string $username Nombre de usuario.
         * @return boolean TRUE si el string es un nombre de usuario válido, 
         * FALSE si no lo es.
         */

        if (!empty($username) 
            && is_string($username)
            && (strlen($username) <= constant('SMP_USRNAME_MAXLEN')) 
            && (strlen($username) >= constant('SMP_USRNAME_MINLEN'))
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Valida un string y determina si se trata de un código UUID4.
     * 
     * @param string $uuid String a validar.
     * @return boolean TRUE si el string cumple los requisitos y es un código 
     * UUID4 válido, FALSE si no lo es..
     */
    protected function isValid_uuid($uuid) 
    {
        if (!empty($uuid) && is_string($uuid)) {
            return (bool) preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-'
                                     . '[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', 
                                     $uuid);
        }

        return FALSE;
    }

    protected function isDataReady() 
    {
        /**
         * Verifica si todos los datos están en orden para guardar en la DB.
         * 
         * @return boolean TRUE si los datos están en orden, FALSE si no.
         */
        
        if (isset($this->db) && is_a($this->db, 'DB')
            && isset($this->Usuario['Nombre'])
            && isset($this->Usuario['PasswordSalted'])
            && isset($this->Usuario['Activo'])
            && isset($this->Usuario['UID'])
            && isset($this->Usuario['CreacionTimestamp'])
            && isset($this->Empleado) && is_a($this->Empleado, 'Empleado')
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    protected function paswordGen($plaintext) 
    {
        /**
         * Devuelve un string que debe ser usado como contraseña para el sistema
         * de login.
         * 
         * @param string $plaintext Contraseña en texto plano que 
         * será encriptada.
         * @return string Contraseña encriptada.
         */
        
        $this->crypto->setValue($plaintext);
        return $this->crypto->getPassword();
    }

    protected function getUserTblFromDB() {
        /**
         * Busca en la DB todos los datos del usuario, usando como parámetro
         * UsuarioId.
         * 
         * @return mixed Todos los valores en un array, o bien TRUE si la
         * consulta no produjo resultados pero fue exitosa.  FALSE si se produjo
         * un error.
         */
        $this->db->setQuery('SELECT * FROM Usuario WHERE UsuarioId = ?');
        $this->db->setBindParam('i');
        $this->db->setQueryParams(array($this->Usuario['UsuarioId']));
        $this->db->queryExecute();
        return $this->db->getQueryData();
    }
    
    protected function findUsuarioId_usingNombre($Nombre)
    {
        $this->db->setQuery('SELECT UsuarioId FROM Usuario WHERE '
                             . 'Nombre = ?');
        $this->db->setBindParam('s');
        $this->db->setQueryParams(array($Nombre));
        $this->db->queryExecute();
        $data = $this->db->queryGetData();
        if (is_array($data)) {
            return (int) current($data);
        }
        return FALSE;
    }
    
    protected function findUsuarioId_usingUID($UID)
    {
        $this->db->setQuery('SELECT UsuarioId FROM Usuario WHERE '
                             . 'UID = ?');
        $this->db->setBindParam('s');
        $this->db->setQueryParams(array($UID));
        $this->db->queryExecute();
        $data = $this->db->queryGetData();
        if (is_array($data)) {
            return (int) current($data);
        }
        return FALSE;
    }
    
    protected function findUsuarioId_usingEmpleadoId($EmpleadoId)
    {
        $this->db->setQuery('SELECT UsuarioId FROM Usuario WHERE '
                             . 'EmpleadoId = ?');
        $this->db->setBindParam('i');
        $this->db->setQueryParams(array($EmpleadoId));
        $this->db->queryExecute();
        $data = $this->db->queryGetData();
        if (is_array($data)) {
            return (int) current($data);
        }
        return FALSE;
    }
    
    protected function findUsuarioId($searchParam)
    {
        /**
         * Busca en la DB el valor de UsuarioId que corresponda con el parámetro
         * de búsqueda.  Éste puede ser Nombre, UID o EmpleadoId.
         * 
         * @param mixed $searchParam Puede ser EmpleadoId, UID o Nombre de 
         * usuario.
         * @return integer UsuarioId, o bien 0 en caso de error o no 
         * encontrarlo.
         */
        
        if (!empty($searchParam)) {
            if (is_int($searchParam)) {
                return $this->findUsuarioId_usingEmpleadoId($searchParam);
            } elseif ($this->isValid_uuid($searchParam)) {
                return $this->findUsuarioId_usingUID($searchParam);
            } elseif ($this->isValid_username($searchParam)) {
                return $this->findUsuarioId_usingNombre($searchParam);
            }
        }
        
        return 0;
    }
    
    protected function setUsuarioCreacionTimestamp($NuevoTimestamp)
    {
        $this->Usuario['CreacionTimestamp'] = $NuevoTimestamp;
    }

    // --
    // __ PUB
    // Set
    public function setUsuarioNombre($NuevoNombreUsuario) {
        if ($this->isValid_username($NuevoNombreUsuario)) {
            $this->Usuario['Nombre'] = strtolower($NuevoNombreUsuario);
            return TRUE;
        }
        return FALSE;
    }
    
    public function setPasswordPlain($NuevoPassword) {
        if ($this->isValid_password($NuevoPassword)) {
            if (isset($this->password)) {
                $this->password->setPlaintextPassword($NuevoPassword);
            } else {
                $this->password = new Password($NuevoPassword);
            }
            return TRUE;
        }
        return FALSE;
    }
  
    public function setActivo($NuevoActivo) {
        if (!empty($NuevoActivo) 
            && is_bool($NuevoActivo)) {
            $this->Usuario['Activo'] = $NuevoActivo;
            return TRUE;
        }
        return FALSE;
    }
    
    public function setUID($NuevoUID) {
        if (isValid_uuid($NuevoUID)) {
            $this->Usuario['UID'] = $NuevoUID;
            return TRUE;
        }
        return FALSE;
    }
    // --
    // Get
    public function getUsuarioNombre() {
        return (string) $this->Usuario['Nombre'];
    }
    
    public function getUID() {
        return (string) $this->Usuario['UID'];
    }

    public function getPasswordSalted() 
    {
        return (string) $this->Usuario['PasswordSalted'];
    }
    
    public function getActivo()
    {
        return (bool) $this->Usuario['Activo'];
    }
    
    public function getUsuarioId()
    {
        return (int) $this->Usuario['UsuarioId'];
    }
    
    public function getUsuarioPerfilId()
    {
        return (int) $this->Usuario['UsuarioPerfilId'];
    }
    
    public function getEmpleadoId()
    {
        return (int) $this->Usuario['EmpleadoId'];
    }

    public function getUsuarioCreacionTimestamp()
    {
        return (int) $this->Usuario['CreacionTimestamp'];
    }
    
    public function getUsuarioModificacionTimestamp()
    {
        return (int) $this->Usuario['ModificacionTimestamp'];
    }
    // --
    // Otras
    public function guardar() {
        /**
         * Guarda el usuario en la DB.  Devuelve TRUE si tuvo éxito, 
         * FALSE si no.
         * 
         * @return boolean TRUE si tuvo éxito, FALSE si no.
         */
        
        if ($this->isDataReady()) {
            $ModificacionTimestamp = time();
            
            if ($this->Nuevo) {
                $this->db->setQuery("INSERT INTO Usuario");
            } else {
                $this->db->setQuery("UPDATE Usuario SET");
            }
        }
        
        return FALSE;
    }
    // --
}