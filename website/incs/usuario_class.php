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
class Usuario extends Empleado 
{    
    use UsuarioPerfil {
        getId as getUsuarioPerfilId;
        getNombre as getUsuarioPerfilNombre;
        getTimestamp as getUsuarioPerfilTimestamp;
    }
    
    protected $Usuario = array ('UsuarioId' => '',
                                'EmpleadoId' => '',
                                'UsuarioPerfilId' => '',
                                'Nombre' => '',
                                'UID' => '',
                                'PasswordSalted' => '',
                                'Activo' => '',
                                'CreacionTimestamp' => '',
                                'ModificacionTimestamp' => ''
                                );
    protected $password, $uid, $EsNuevoUsuario;
    
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
     * @param mixed $UID UID del usuario, como objeto o string.
     * @param mixed $Password Nueva clave (como objeto) o bien se considerará 
     * nueva clave en texto plano como string.
     * @param boolean $Activo TRUE para indicar que el nuevo usario estará 
     * activo (por defecto), FALSE para desactivarlo.
     */
    function __construct($Nombre = NULL, $UID = NULL, 
                          $Password = NULL, $Activo = TRUE
    ) {      
        
        if (!$this->setUID($UID)) {
            $this->uid = new UID;
        }
        
        if (!$this->setPassword($Password)) {
            $this->password = new Password;
        }       
        
        /* !!! */
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
            $this->setUID($this->crypto->get());
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
    /**
     * Valida un string y determina si cumple las restricciones impuestas 
     * en la configuración sobre los nombres de usuario.
     * 
     * @param string $username Nombre de usuario.
     * @return boolean TRUE si el string es un nombre de usuario válido, 
     * FALSE si no lo es.
     */
    protected static function isValid_username($username) 
    {
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
     * Valida y determina si se trata de un objeto UID.
     * 
     * @param mixed $uid
     * @return boolean TRUE si se trata de un objeto UID, FALSE si no.
     */
    protected static function isValid_UID($uid)
    {
        if (!empty($uid) && is_a($uid, 'UID')) {
            return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Valida y determina si se trata de un objeto Empleado.
     * 
     * @param mixed $empleado
     * @return boolean TRUE si se trata de un objeto Empleado, FALSE si no.
     */
    protected static function isValid_Empleado($empleado)
    {
        if (!empty($empleado) && is_a($empleado, 'Empleado')) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Valida y determina si se trata de un objeto DB.
     * 
     * @param mixed $db
     * @return boolean TRUE si se trata de un objeto DB, FALSE si no.
     */
    protected static function isValid_DB($db)
    {
        if (!empty($db) && is_a($db, 'DB')) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Valida y determina si se trata de un objeto Password.
     * 
     * @param mixed $password
     * @return boolean TRUE si se trata de un objeto Password, FALSE si no.
     */
    protected static function isValid_Password($password)
    {
        if (!empty($password) && is_a($password, 'Password')) {
            return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Verifica si todos los datos están en orden para guardar en la DB.
     * 
     * @return boolean TRUE si los datos están en orden, FALSE si no.
     */
    protected function isDataReady() 
    {        
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

    /**
     * Busca en la DB todos los datos del usuario, usando como parámetro
     * UsuarioId, UID o Nombre.
     * 
     * @param mixed (int) UsuarioId, (UID) UID o (string) Nombre.
     * @return mixed Todos los valores en un array, FALSE si se produjo
     * un error.
     */
    protected static function getTblFromDB($searchParam) {
        if (!empty($searchParam)) {
            $db = new DB;
            if (is_int($searchParam)) {
                $db->setQuery('SELECT * FROM Usuario WHERE UsuarioId = ?');
                $db->setBindParam('i');
            } elseif (self::isValid_UID($searchParam)) {
                $db->setQuery('SELECT * FROM Usuario WHERE UID = ?');
                $db->setBindParam('s');
                $searchParam = $searchParam->get();
            } elseif (self::isValid_username($searchParam)) {
                $db->setQuery('SELECT * FROM Usuario WHERE Nombre = ?');
                $db->setBindParam('s');
            } else {
                return FALSE;
            }

            $db->setQueryParams($searchParam);
            $db->queryExecute();
            $result = $db->getQueryData();
            unset($db);
            return $result;
        }
        
        return FALSE;
    }
        
    protected function setUsuarioCreacionTimestamp($NuevoTimestamp)
    {
        $this->Usuario['CreacionTimestamp'] = $NuevoTimestamp;
    }

    // --
    // __ PUB
    // Set
    public function setNombre($NuevoNombreUsuario) {
        if ($this->isValid_username($NuevoNombreUsuario)) {
            $this->Usuario['Nombre'] = strtolower($NuevoNombreUsuario);
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Almacena una nueva contraseña, como objeto o string.  El objeto debe 
     * contener una contraseña en texto plano o encriptada.
     * @param type $NuevoPassword
     * @return boolean
     */
    public function setPassword($NuevoPassword) 
    {
        $retval = FALSE;
        
        if (self::isValid_Password($NuevoPassword) 
            && (!empty($NuevoPassword->getPlaintext()) 
                || !empty($NuevoPassword->getEncrypted()))
        ) {
            $this->password = $NuevoPassword;
            $retval = TRUE;
        } elseif (Password::isStrong($NuevoPassword)) {
            $this->password = new Password;
            $this->password->setPlaintext($NuevoPassword);
            $retval = TRUE;
        }
        
        return $retval;
    }
  
    public function setActivo($NuevoActivo) {
        if (!empty($NuevoActivo) 
            && is_bool($NuevoActivo)
        ) {
            $this->Usuario['Activo'] = $NuevoActivo;
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Almacena el UID indicado como nuevo UID del usuario.  Acepta objeto UID 
     * o string.<br />
     * El objeto debe contener un UID válido para ser aceptado.<br />
     * Al crear un usuario nuevo, el sistema creará un nuevo UID si no se 
     * almacenó uno previamente, por lo que no es necesario llamar a éste 
     * método a tal fin.
     * 
     * @param mixed $NuevoUID (UID) o (string) Nuevo UID.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setUID($NuevoUID) 
    {
        $retval = FALSE;
    
        if (self::isValid_UID($NuevoUID) && !empty($NuevoUID->get())) {
            $this->uid = $NuevoUID;
            $retval = TRUE;
        } elseif (UID::isValid($NuevoUID)) {
            $this->uid = new UID;
            $this->uid->set($NuevoUID);
            $retval = TRUE;
        }
        
        return $retval;
    }
    // --
    // Get
    public function getUsuarioNombre() {
        return (string) $this->Usuario['Nombre'];
    }
    
    public function get() {
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