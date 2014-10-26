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
 * Esta clase maneja todo lo referido al empelado:
 * - Listado
 * - Creacion nuevo
 * - Cambio de datos
 * - etc
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.13
 */
class Empleado 
{
    /**
     * Tabla Empleado.
     * @var array
     */
    protected $Empleado = array(
        'EmpleadoId' => 0,
        'Nombre' => '',
        'Apellido' => '',
        'FicheroId' => 0,
        'Titulo' => 0,
        'Sexo' => '',
        'FechaNac' => 0,
        'FechaIngresoDependencia' => 0,
        'FechaIngresoJusticia' => 0,
        'ResolIngreso_Nro' => 0,
        'ResolIngreso_Año' => 0,
        'DocumentoNro' => 0,
        'CUIL' => 0,
        'Legajo' => 0,
        'TelNro' => '',
        'TelCodArea' => '',
        'CelNro' => '',
        'CelCodArea' => '',
        'Email' => '',
        'NivelEstudioId' => 0,
        'ProfesionTitulo' => '',
        'EstadoCivil' => '',
        'EstadoId' => 0,
        'Comentario' => '',
        'CreacionTimestamp' => 0,
        'ModificacionTimestamp' => 0
    );
    
//    protected $EmpleadoId = 0;
//    protected $EmpleadoNombre = '';
//    protected $EmpleadoApellido = '';
//    protected $Email = '';
//    protected $Legajo = 0;
//    protected $FicheroId = 0;
//    protected $Titulo = 0;
//    protected $Sexo = '';
//    protected $FechaNac = 0;
//    protected $FechaIngresoDependencia = 0;

    /**
     * Determina si al grabar en la DB se escribirá el ID de la tabla (TRUE)
     * o no (FALSE, por defecto) al crear un nuevo Empleado, dado que la DB
     * maneja este valor automáticamente.
     * @var boolean
     */
    protected $write_id = FALSE;
    
    /**
     * Indica si el empleado es nuevo o ya existe.  Importante para determinar
     * si se debe cambiar CreacionTimestamp.
     * @var boolean
     */
    protected $esNuevoEmpleado;

    // __ SPECIALS
    /**
     * Busca en la DB si ya existe un Empleado con los datos pasados:
     * EmpleadoId, Legajo o Email (en ese orden de prioridad)<br />
     * Si lo encuentra, recupera todos los datos desde la DB.  No almacena los 
     * datos pasados.<br />
     * Si no lo encuentra, considera que se está creando un nuevo Empleado y 
     * almacena los datos pasados.<br />
     * <i>No es recomendable crear un nuevo usuario con EmpleadoId manual,
     * dado que la DB genera uno automáticamente.</i>
     * 
     * @param type $Legajo
     * @param type $Email
     * @param type $EmpleadoId
     */
    function __construct($Legajo = NULL, 
                            $Email = NULL, 
                            $EmpleadoId = NULL) 
    {
        $this->setEmpleadoId($EmpleadoId);
        $this->setLegajo($Legajo);
        $this->setEmail($Email);
        $this->esNuevoEmpleado = !$this->retrieve_fromDB();
    }
    
    /**
     * 
     * @param type $name
     * @param type $arguments
     * @return boolean
     */
    function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->{$name}($arguments);
        }
        
        $method = substr($name, 0, 3);
        $var = substr($name, 3);
        
        switch ($method) {
            case 'get':
                $retval = $this->Empleado[$var] ?: NULL;
                break;
            
            case 'set':
                $retval = TRUE;
                if(method_exists($this, 'isValid_' . $var)) {
                    $this->{'isValid_' . $var}($arguments[0]) ? 
                        $this->Empleado[$var] = $arguments[0] : $retval = FALSE;
                } else {
                    isset($this->Empleado[$var]) ? $this->Empleado[$var] = $arguments[0] : $retval = FALSE;
                }
                break;
            
            default:
                $retval = FALSE;
                break;
        }
        
        return $retval;
    }

    // __ PRIV
    
    // __ PROT
    /**
     * Determina si el valor pasado es un identificador válido de la tabla 
     * Empleado.
     * 
     * @param int $EmpleadoId Identificador de la tabla Empleado.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    protected static function isValid_EmpleadoId($EmpleadoId)
    {
        if (!empty($EmpleadoId) && is_int($EmpleadoId)) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Determina si el valor pasado es un legajo de Empleado válido.
     * 
     * @param int $Legajo Legajo del Empleado.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    protected static function isValid_Legajo($Legajo)
    {
        if (!empty($Legajo) && is_int($Legajo)) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Determina si el valor pasado es un Email válido.
     * 
     * @param string $Email Email.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    protected static function isValid_Email($Email)
    {
        if (!empty($Email)) {
            return boolval(filter_var($Email, FILTER_VALIDATE_EMAIL));
        }
        
        return FALSE;
    }
    
    /**
     * Busca en la DB todos los datos del empleado, usando como parámetro
     * EmpeladoId, Legajo o Email.
     * 
     * @param mixed $searchParam (int) EmpleadoId, (int) Legajo o (string) Email.
     * @return mixed Todos los valores en un array o FALSE si se produjo
     * un error.
     */
    protected static function retrieve_tblEmpleado($searchParam)
    {
        if (!empty($searchParam)) {
            $db = new DB(SMP_DB_CHARSET);
            if (self::isValid_EmpleadoId($searchParam)) {
                $db->setQuery('SELECT * FROM Empleado WHERE EmpleadoId = ?');
                $db->setBindParam('i');
            } elseif (self::isValid_Legajo($searchParam)) {
                $db->setQuery('SELECT * FROM Empleado WHERE Legajo = ?');
                $db->setBindParam('i');
            } elseif (self::isValid_Email($searchParam)) {
                $db->setQuery('SELECT * FROM Empleado WHERE Email = ?');
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
    // __ PUB
//    /**
//     * Recibe un número de Legajo y lo almacena si es válido.
//     * 
//     * @param string $Legajo Legajo a almacenar.
//     * @return boolean TRUE si el Legajo recibido es válido y se almacenó 
//     * correctamente, FALSE si no.
//     */
//    public function setLegajo($Legajo)
//    {
//        if (self::isValid_Legajo($Legajo)) {
//            $this->Empleado['Legajo'] = $Legajo;
//            return TRUE;
//        }
//        
//        return FALSE;
//    }
//    
//    /**
//     * Almacena en el objeto el ID de la tabla Empleado.<br />
//     * <i>No es recomendable crear un nuevo usuario con EmpleadoId manual, 
//     * dado que la DB genera uno automáticamente.</i>
//     * 
//     * @param int $EmpleadoId Identificador de la tabla Empleado.
//     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
//     */
//    public function setEmpleadoId($EmpleadoId)
//    {
//        if (self::isValid_EmpleadoId($EmpleadoId)) {
//            $this->Empleado['EmpleadoId']= $EmpleadoId;
//            return TRUE;
//        }
//        
//        return FALSE;
//    }
//    
//    /**
//     * Recibe una dirección de Email y la almacena si es válida.
//     * 
//     * @param string $Email Email a almacenar.
//     * @return boolean TRUE si el Email recibido es válido y se almacenó 
//     * correctamente, FALSE si no.
//     */
//    public function setEmail($Email)
//    {
//        if (self::isValid_Email($Email)) {
//            $this->Empleado['Email'] = $Email;
//            return TRUE;
//        }
//        
//        return FALSE;
//    }
//    
//    /**
//     * Devuelve el identificador de la tabla Empleado, si hay.
//     * 
//     * @return int Identificador de la tabla Empleado o 0.
//     */
//    public function getEmpleadoId()
//    {
//        return $this->Empleado['EmpleadoId'];
//    }
    
    /**
     * Devuelve el Email del Empleado.  Puede solicitarse devolverlo oculto.<br />
     * P.E: simape@simape.com.ar | s****e@simape.com.ar
     * @param boolean $obscured [opcional]<br />
     * TRUE para devolver el email oculto, FALSE para devolverlo normal 
     * (por defecto).
     * @return string Email del Empleado.
     */
    public function getEmail($obscured = FALSE)
    {
        $email = '';
        if (!empty($this->Empleado['Email'])) {
            if ($obscured) {
                list($local, $domain) = explode('@', $this->Empleado['Email']);
                
                $email = (strlen($local) > 3) ? substr($local, 0, 1) 
                                    . str_repeat('*', strlen($local) - 2) 
                                    . substr($local, -1, 1) : 
                                    $local;
                
                list($hostname, $com) = explode('.', $domain, 2);
                $email .= '@';
                $email .= (strlen($hostname) > 2) ? substr($hostname, 0, 2) 
                                    . str_repeat('*', strlen($hostname) - 2) :
                                    $hostname;
                $email .= '.' . $com;
            } else {
                $email = $this->Empleado['Email'];
            }
        }
        
        return $email;
    }

    /**
     * Recupera de la DB todos los datos del Empleado, siempre y cuando se haya
     * establecido previamente el ID, Legajo o Email del mismo (la búsqueda se 
     * realiza en ese orden de prioridad).<br />
     * <b>ATENCIÓN:</b> ¡se sobreescribirán los datos almacenados respecto del 
     * empleado!
     * 
     * @return boolean TRUE si se recuperó correctamente, FALSE si no.
     */
    public function retrieve_fromDB()
    {
        return TRUE;
//        $searchParams = array($this->Empleado['EmpleadoId'], 
//                            $this->Empleado['Legajo'], 
//                            $this->Empleado['Email']);
//        foreach ($searchParams as $searchP) {
//            $empleado = static::retrieve_tblEmpleado($searchP);
//            if (is_array($empleado) && !empty($empleado)) {
//                $this->Empleado = $empleado;
//                return TRUE;
//            }
//        }
//        return FALSE;
    }
}