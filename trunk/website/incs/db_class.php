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
 * Maneja la conexión con la DB y la ejecución de consultas.
 * 
 * Ejemplo de uso:
 * <pre><code>
 * $db = new DB(); // modo RO
 * $db->setQuery('SELECT * FROM mitabla WHERE micolumna = ?');
 * $db->setBindParam('s');
 * $db->setQueryParams('valor');
 * $db->queryExecute();
 * if ($db->getAffectedRows()) {
 *  misDatos = $db->getQueryData();
 *  echo "Se obtuvieron " . $db->getAffectedRows() . " filas.";
 * }
 * $db->cambiarModo(TRUE);  //modo RW
 * $db->setQuery('INSERT INTO mitabla (micolumna) VALUES (?)');
 * $db->setBindParam('s');
 * $db->setQueryParams('NuevoValor');
 * if ($db->queryExecute()) {
 *  echo "Se obtuvieron " . $db->getAffectedRows() . " filas.";
 * }
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.21
 */
class DB extends mysqli
{     
    protected $queryStmt, $bindParam, $queryParams;
    protected $queryData;
    protected $affectedRows;

    // Metodos
    // __ SPECIALS
    
    /**
     * Conecta con la DB según el modo indicado e inicializa la conexión.
     * 
     * @param boolean $ModoRW Establece el modo RW si es TRUE, 
     * RO si es FALSE (por defecto).
     */
    function __construct($ModoRW = FALSE) 
    {   
        if (!$this->conexionEstablecer($ModoRW) 
            || !$this->conexionInicializar()) {
            trigger_error('Error de conexion', E_USER_ERROR);
        }
        
    }
    
//    function __destruct() 
//    {
//        if (!empty($this->queryStmt)) {
//            $this->queryStmt->close();
//        }
//        parent::close();
//    }

    // __ PRIV
    
    // __ PROT
    /**
     * Conecta en el modo indicado.
     * 
     * @param boolean $ModoRW Si es TRUE, conecta en modo RW; si es FALSE,
     * en modo RO (por defecto).
     * @return boolean TRUE si se estableció la conexión exitosamente, 
     * FALSE si no.
     */
    protected function conexionEstablecer($ModoRW = FALSE)
    {  
        if ($ModoRW) {
            parent::__construct(SMP_DB_HOST, SMP_DB_USER_RW, SMP_DB_PASS_RW, SMP_DB_NAME);
        } else {
            parent::__construct(SMP_DB_HOST, SMP_DB_USER_RO, SMP_DB_PASS_RO, SMP_DB_NAME);
        }
        
        if ($this->connect_error) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    /**
     * Configura el charset de la DB.
     * 
     * @return boolean TRUE si se configuró correctamente el charset, 
     * FALSE si no.
     */
    protected function setCharset() 
    {
        if ($this->character_set_name() != constant('SMP_DB_CHARSET')) {
            if ($this->set_charset(constant('SMP_DB_CHARSET'))) {
                return $this->real_query('SET NAMES ' . constant('SMP_DB_CHARSET'));
            } else {
                return FALSE;
            }
        } else {
            return TRUE;
        }
    }
    
    /**
     * Ejecuta las tareas que deben realizarse luego de establecer una 
     * conexión.
     */
    protected function conexionInicializar()
    {        
        unset($this->queryStmt, $this->bindParam, $this->queryParams, 
              $this->queryData, $this->affectedRows);
        return $this->setCharset();
    }
    
    /**
     * <p>Guarda los datos obtenidos de la query (cuando la misma se trató 
     * de un SELECT) de la siguiente manera:
     * <ul>
     * <li>Si la consulta devolvió un único dato, lo guarda sin más.  
     * Si devolvió más de un dato, como array.</li>
     * <li>Si no se obtuvieron datos, como TRUE.</li>
     * <li>Si la consulta devolvió error, como FALSE.</li>
     * </ul>
     * </p>
     * <p>El array almacenado es:
     * <ul>
     * <li>Asociativo cuando la consulta devuelve una fila con multiples 
     * columnas;</li>
     * <li>Numerado cuando la consulta devuelve una o varias filas c/u con 
     * sólo una columna;</li>
     * <li>Mixto cuando la consulta devuelve multiples filas con multiples 
     * columnas (las filas serán índice numerado, y dentro un array 
     * asociativo con las columnas como indices).</li>
     * </ul>
     * </p>
     * 
     * <p>IMPORTANTE: Requiere native driver (<i>php5-mysqlnd</i>)<br />
     * ATENCIÓN: el resultado debería evaluarse con <i>is_array()</i> o 
     * <i>is_string()|is_int()|is_float()....</i> para determinar si se 
     * obtuvieron datos en la consulta,
     * o <i>DB::getAffectedRows()</i> para determinar la cantidad de filas 
     * obtenidas.</p>
     *  
     */
    protected function querySaveData() {        
        $result = FALSE;
        
        if (isset($this->queryStmt) && ($this->queryStmt->errno == 0)) {  
            $result = TRUE;
            
            $data = $this->queryStmt->get_result();
            // no conviene cerrar acá este parámetro...
            //$this->queryStmt->close();
            if ($data) {
                // puede que haya datos
                while ($row = $data->fetch_assoc()) {
                    if (count($row, COUNT_NORMAL) == 1) {
                        $value = current($row);
                    } else {
                        $value = $row;
                    }
                    $query_result[] = $value;
                }
                $data->free();

                if (isset($query_result)) {
                    if (count($query_result, COUNT_NORMAL) == 1) {
                        $result = $query_result[0];
                    } else {
                        $result = $query_result;
                    }
                } 
            }
        }
        
        if ($result) {
            $this->queryData = $result;
        } else {
            unset($this->queryData);
        }
    }
    // __ PUB
//    public function close()
//    {
//        $this->__destruct();
//    }

    /**
     * Cambia el modo de conexión con la DB.
     * 
     * @param boolean $ModoRW Establece el modo RW si es TRUE, 
     * RO si es FALSE.
     * @return boolean TRUE si se cambió el modo exitosamente, FALSE si no.
     */
    public function cambiarModo($ModoRW = FALSE) 
    {
        if ($ModoRW) {
            $this->change_user(SMP_DB_USER_RW, SMP_DB_PASS_RW, SMP_DB_NAME);
        } else {
            $this->change_user(SMP_DB_USER_RO, SMP_DB_PASS_RO, SMP_DB_NAME);
        }
        
        return $this->conexionInicializar();
    }
    
    // Set
    /**
     * Prepara el objeto para ejecutar la query indicada.  Devuelve TRUE
     * si se preparó exitosamente, FALSE en caso contrario.<br />
     * Debe llamarse a setBindParam() y setQueryParams() <i>después</i> de 
     * éste método, y nunca antes.
     * 
     * @param string $queryPrepared Query a ser ejecutada.  Debe ser  
     * Prepared Statement, a menos que no contenga parámetros.  NOTA: los
     * statements (<i>?</i>) ¡no van encomillados!.
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */
    public function setQuery($queryPrepared) 
    {
        $stmt = $this->prepare($queryPrepared);
        if($stmt) {
            $this->queryStmt = $stmt;
            unset($this->bindParam);
            unset($this->queryParams);
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena los parametros de binding para la query.  Devuelve TRUE
     * si tuvo éxito, FALSE si no.
     * 
     * @param string $bindParam Parametros para indicar el tipo de
     * binding: i: integer; s: string; d: double; b: blob
     * @return boolean Devuelve TRUE si tuvo éxito, FALSE si no.
     */
    public function setBindParam($bindParam) 
    {        
        if (!empty($bindParam) 
            && is_string($bindParam)
            && !preg_match('/[^idsb]/', $bindParam)
        ) {
            $this->bindParam = $bindParam;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena los parámetros de la query que reemplazan a los statements 
     * (<i>?</i>).  Devuelve TRUE si tuvo éxito, 
     * FALSE si no.
     * 
     * @param mixed $queryParams Valores que serán insertados 
     * en la query.
     * @return boolean Devuelve TRUE si tuvo éxito, FALSE si no.
     */
    public function setQueryParams($queryParams)
    {    
        if (!empty($queryParams)) {
            if (is_array($queryParams)) {
                $queryP = $queryParams;
            } else {
                $queryP = array($queryParams);
            }
            $this->queryParams = $queryP;
            return TRUE;
        }
        
        return FALSE;
    }
    // --
    // Get
    /**
     * Devuelve la cantidad de filas afectadas por la última consulta, si es 
     * que se trató de INSERT, UPDATE, REPLACE o DELETE.
     * Devolverá la cantidad de filas del resultado si se trató de un SELECT.
     * 
     * @return int La cantidad de filas afectadas por la última consulta, si es 
     * que se trató de INSERT, UPDATE, REPLACE o DELETE.  Devolverá la cantidad 
     * de filas del resultado si se trató de un SELECT.
     */
    public function getAffectedRows()
    {
        if (isset($this->affectedRows)) {
            return (int) $this->affectedRows;
        }
    }
    
    /**
     * Devuelve los datos obtenidos en la última query, asumiendo que
     * haya sido un SELECT.
     *          
     * @return mixed Los datos obtenidos de la query (cuando la misma se trató 
     * de un SELECT) de la siguiente manera:
     * <ul>
     * <li>Si la consulta devolvió un único dato, lo entrega sin más.
     * Si devolvió más de un dato, como array.</li>
     * <li>Si no se obtuvieron datos, como TRUE.</li>
     * <li>Si la consulta devolvió error, como FALSE.</li>
     * </ul>
     * </p>
     * <p>El array almacenado es:
     * <ul>
     * <li>Asociativo cuando la consulta devuelve una fila con multiples 
     * columnas;</li>
     * <li>Numerado cuando la consulta devuelve una o varias filas c/u con 
     * sólo una columna;</li>
     * <li>Mixto cuando la consulta devuelve multiples filas con multiples 
     * columnas (las filas serán índice numerado, y dentro un array 
     * asociativo con las columnas como indices).</li>
     * </ul>
     */
    public function getQueryData() 
    {        
        if (isset($this->queryData)) {
            return $this->queryData;
        }
        
        return FALSE;
    }
    // --
    // Query
    /**
     * Ejecuta una query (SELECT, INSERT, UPDATE, DELETE) con o sin 
     * parámetros usando transacciones.
     * Devuelve TRUE si tuvo éxito, y ejecuta commit, o FALSE si no, y 
     * hace rollback.  Si se produjo un error durante la ejecución de la query,
     * devuelve el nro. de error en lugar de FALSE.
     * Para obtener los datos de un SELECT, llamar a queryGetData().
     * Para obtener la cantidad de filas afectadas, llamar a getAffectedRows().
     * 
     * @see queryGetData()
     * @see getAffectedRows().
     * @return mixed TRUE si tuvo éxito, FALSE si se produjo un error antes
     * de la ejecución de la query.  Si el error se produjo durante, devuelve
     * el nro. de error.
     */
    public function queryExecute() 
    {
        $result = FALSE;

        if (!empty($this->queryStmt)) {
            $this->autocommit(FALSE);

            if(!empty($this->bindParam) && !empty($this->queryParams)) {
                $bind_names[] = $this->bindParam;
                for ($i = 0; $i < count($this->queryParams); $i++) {
                    $bind_name = 'bind' . $i;
                    $$bind_name = $this->queryParams[$i];
                    $bind_names[] = &$$bind_name;
                }
                $result = call_user_func_array(array($this->queryStmt, 
                                                     'bind_param'), 
                                               $bind_names);
            } else {
                // Continuar, no hay que bindear la query
                $result = TRUE;
            }
            
            if($result) {
                $result = $this->queryStmt->execute();
                if ($result) {
                    $this->commit();
                    $this->querySaveData();
                } else {
                    $this->rollback();
                    $result = $this->queryStmt->errno;
                    
                }
            }
            $this->affectedRows = $this->queryStmt->affected_rows;
            $this->queryStmt->close();
            $this->autocommit(TRUE);
        }

        return $result;
    }
    
    /**
     * Devuelve los datos obtenidos en la última query, asumiendo que
     * haya sido un SELECT.
     * Es un alias de getQueryData().
     *          
     * @see getQueryData()
     * @return mixed Si la consulta produjo resultados los devuelve como 
     * array; si no hubieron resultados pero la consulta fue exitosa, 
     * devuelve TRUE; en caso de error, FALSE.
     */
    public function queryGetData()
    {
        return $this->getQueryData();
    }

    // Otras
    /**
     * Sanitiza un valor para ser usado en una consulta.  Puede tratarse de
     * un array o un string.
     * NOTA: No es necesario si se emplea una prepared query.
     *
     * @param mixed $valor Valor que será sanitizado.
     * @return mixed Valor sanitizado para ser usado en una consulta.  Será
     * del mismo tipo que $valor, o bien NULL en caso de error
     */
    public function sanitizar($valor) 
    {
        if ($this->setCharset() && !empty($valor)) {
            if (is_array($valor)) {
                foreach ($valor as $key => $value) {
                    $result[$key] = $this->sanitizar($value);
                }
            } else {
                $result = addcslashes(
                            $this->real_escape_string(stripslashes($valor))
                            , '%_');
            }
            return $result;
        } else {
            return NULL;
        }
    }
    
    /**
     * Determina si el valor pasado es un identificador válido de tabla en DB.
     * 
     * @param int $tblId Identificador de la tabla.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    public static function isValid_TblId($tblId)
    {
        if (!empty($tblId) && is_int($tblId)) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve una tabla de la DB.
     * 
     * @param type $tblName Nombre de la tabla.
     * @param int $tblId Identificador de la tabla.
     * @return boolean|array Un array asociativo conteniendo la tabla, 
     * o FALSE en caso de error.
     */
    public function retrieve_tblToken($tblName, $tblId)
    {
        if (is_string($tblName) && is_int($tblId)) {
            $this->setQuery('SELECT * FROM ' . $tblName 
                            . ' WHERE ' . $tblName . 'Id = ?');
            $this->setBindParam('i');
            $this->setQueryParams($tblId);
            $this->queryExecute();
            return $this->getQueryData();
        }
        return FALSE;
    }
    
    /**
     * Devuelve el identificador de tabla de la tabla indicada, buscando por el 
     * parámetro indicado. P. E.:<br />
     * <pre><code>
     * SELECT TblNameId FROM TblName WHERE Param = Value;
     * </code></pre>
     * 
     * @param string $tblName Nombre de la tabla.
     * @param string $param_name Nombre del parámetro a buscar en la tabla.
     * @param string $param_type Una letra para indicar el tipo de
     * dato del parámetro: i: integer; s: string; d: double; b: blob 
     * @param mixed $param_value Valor del parámetro buscado, que debe 
     * coincidir con el tipo de dato indicado.
     * 
     * @return int|boolean Identificador de tabla o FALSE en caso de error.
     */
    public function retrieve_tableId($tblName, 
                                        $param_name, $param_type, $param_value)
    {
        if ($this->setBindParam($param_type) && is_string($tblName)) {
            $this->setQuery('SELECT ' . $tblName . 'Id FROM ' . $tblName 
                            . ' WHERE ' . $param_name . ' = ?');
            
            $this->setQueryParams($param_value);
            $this->queryExecute();
            $tblId = $this->getQueryData();
            if (is_int($tblId)) {
                return $tblId;
            }
        }
        
        return FALSE;
    }
}