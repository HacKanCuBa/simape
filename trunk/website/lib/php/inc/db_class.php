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
 * @version 1.40
 */
class DB extends mysqli
{     
    const CHARSET_DEFAULT = 'utf8';

    protected $queryStmt, $bindParam, $queryParams;
    protected $queryData;
    protected $affectedRows;
    protected $charset;

    // Metodos
    // __ SPECIALS
    
    /**
     * Conecta con la DB según el modo indicado e inicializa la conexión.
     * 
     * @param boolean [opcional]<br />
     * $ModoRW Establece el modo RW si es TRUE, 
     * RO si es FALSE (por defecto).
     * @param string $charset [opcional]<br />
     * Charset de la DB (UTF8 por defecto).
     */
    function __construct($ModoRW = FALSE, $charset = self::CHARSET_DEFAULT) 
    {   
        if (!$this->conexionEstablecer($ModoRW) 
            || !$this->conexionInicializar($charset)) {
            trigger_error('Error de conexion con la base de datos', E_USER_ERROR);
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
     * @param boolean $ModoRW [opcional]<br />
     * Si es TRUE, conecta en modo RW; si es FALSE,
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
        
        return !boolval($this->connect_error);
    }
    
    /**
     * Configura el charset de la DB.
     * 
     * @param string $charset Charset a configurar.
     * @return boolean TRUE si se configuró correctamente el charset, 
     * FALSE si no.
     */
    protected function setCharset($charset)
    {
        if ($this->character_set_name() != $charset) {
            if ($this->set_charset($charset)) {
                $this->charset = $charset;
                return $this->real_query('SET NAMES ' . $charset);
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
     * @param string $charset [opcional]<br />
     * Charset de la DB.  Si no se especifica charset, no lo cambia.
     * @return boolean TRUE si se inicializó correctamente, FALSE si no.
     */
    protected function conexionInicializar($charset = NULL)
    {        
        unset($this->queryStmt, $this->bindParam, $this->queryParams, 
              $this->queryData, $this->affectedRows);
        return ($charset ? $this->setCharset($charset) : TRUE);
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
     * Cambia el modo de conexión con la DB, y el charset.
     * 
     * @param boolean $ModoRW Establece el modo RW si es TRUE, 
     * RO si es FALSE.
     * @param string $charset [opcional]<br />
     * Charset de la DB.  Si no se especifica, no lo cambia.
     * @return boolean TRUE si se cambió el modo exitosamente, FALSE si no.
     */
    public function cambiarModo($ModoRW = FALSE, 
                                $charset = NULL
    ) {
        if ($ModoRW) {
            $this->change_user(SMP_DB_USER_RW, SMP_DB_PASS_RW, SMP_DB_NAME);
        } else {
            $this->change_user(SMP_DB_USER_RO, SMP_DB_PASS_RO, SMP_DB_NAME);
        }
        $this->clearParams();
        return $this->conexionInicializar($charset);
    }
    
    // Set
    /**
     * Prepara el objeto para ejecutar la query indicada.  Devuelve TRUE
     * si se preparó exitosamente, FALSE en caso contrario.<br />
     * Debe llamarse a setBindParam() y setQueryParams() <i>después</i> de 
     * este método, y nunca antes.
     * Los valores serán debidamente sanitizados.
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
            $this->clearParams();
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
            $this->queryParams = is_array($queryParams) ? $queryParams : 
                                                            array($queryParams);
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Limpia los parámetros almacenados: queryParams y bindParams.
     */
    public function clearParams() 
    {
        unset($this->bindParam);
        unset($this->queryParams);
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
            return intval($this->affectedRows);
        }
    }
    
    /**
     * Devuelve los datos obtenidos en la última query, asumiendo que
     * haya sido un SELECT.  Si no se trató de un SELECT, devuelve TRUE o FALSE
     * según si la consulta fue exitosa o no.
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
     * Si no se trató de un SELECT, devuelve TRUE o FALSE
     * según si la consulta fue exitosa o no.
     */
    public function getQueryData() 
    {       
        return (isset($this->queryData) ? $this->queryData : FALSE);
    }
    // --
    // Query
    /**
     * Ejecuta una query (SELECT, INSERT, UPDATE, DELETE) con o sin 
     * parámetros usando transacciones.
     * Devuelve TRUE si tuvo éxito, y ejecuta commit, o FALSE si no, y 
     * hace rollback.  Si se produjo un error durante la ejecución de la query,
     * devuelve el nro. de error en lugar de FALSE.
     * Para obtener los datos de un SELECT, llamar a getQueryData().
     * Para obtener la cantidad de filas afectadas, llamar a getAffectedRows().
     * 
     * @see getQueryData()
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
                $result = boolval(call_user_func_array(array($this->queryStmt, 
                                                     'bind_param'), 
                                                     $bind_names));
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
                    $this->clearParams();
                    $result = $this->queryStmt->errno;
                }
            }
            $this->affectedRows = $this->queryStmt->affected_rows;
            $this->queryStmt->close();
            $this->autocommit(TRUE);
        }

        return $result;
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
        if (!empty($valor)) {
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
    public function retrieve_table($tblName, $tblId)
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
    
    /**
     * Determina si la Tabla indicada existe o no.
     * @param string $tableName Nombre de la Tabla.
     * @param int $tableId Identificador de la tabla buscada.
     * @return boolean TRUE si la tabla existe, FALSE si no.
     */
    public function table_exists($tableName, $tableId)
    {
        $this->setQuery('SELECT ' . $tableName . 'Id FROM ' . $tableName 
                                        . ' WHERE ' . $tableName . 'Id = ?');
        $this->setBindParam('i');
        $this->setQueryParams($tableId);
        if ($this->queryExecute()) {
            if ($this->getQueryData() == $tableId) {
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Identifica una variable a fin de determinar qué identificador de 
     * binding le corresponde.  Puede pasarse un array, y devolverá un string 
     * de binding.<br />
     * Identificadores: 
     * <ul>
     * <li>i (integer): int | bool</li>
     * <li>s (string): string</li>
     * <li>d (double): float</li>
     * <li>b (blob): no es posible determinar</li>
     * </ul>
     * @param mixed $bind Variable a identificar.
     * @return string|FALSE Identificador de binding o FALSE si no se puede 
     * determinar.
     * @access public
     */
    public function bind_id($bind)
    {
        if (is_array($bind)) {
            $id = '';
            foreach ($bind as $value) {
                $id .= $this->bind_id($value);
            }
        } elseif (is_int($bind) || is_bool($bind)) {
            $id = 'i';
        } elseif (is_string($bind)) {
            $id = 's';
        } elseif (is_float($bind)) {
            $id = 'd';
        } elseif (is_numeric($bind)) {
            if (intval($bind) == $bind) {
                $id = 'i';
            } elseif (floatval($bind) == $bind) {
                $id = 'd';
            }
        } else {
            $id = FALSE;
        }
        
        return $id;
    }
    
    /**
     * Inserta nuevos valores en una tabla en la DB.  Si la cantidad de columnas no es igual
     * a la de valores, completa automáticamente con NULL.  Los valores serán
     * debidamente sanitizados.
     * Si se indica NULL a las columnas, se insertará una tabla vacía 
     * (el parámetro values no será tenido en cuenta).  
     * NOTA: algunas tablas tienen restricciones, que podrían devolver error 
     * al hacer esto.
     * 
     * @param string $tableName Nombre de la tabla a insertar.
     * @param string|array $columns [opcional]<br />
     * Nombre de las columnas que recibirán un 
     * valor.  Puede ser array o string:
     * <ul>
     * <li>['col1', 'col2', ..., 'colN']</li>
     * <li>"col1,col2,...,colN"<br />
     * ¡Los espacios serán interpretados como parte del nombre de la columna!</li>
     * </ul>
     * Puede tambien recibir un array asociativo o una lista separada por comas,
     * donde la llave será el nombre de la columna:
     * <ul>
     * <li>['col1' => val1, 'col2' => val2, ..., 'colN' => valN]</li>
     * <li>"col1=val1,col2=val2,...,colN=valN"</li>
     * </ul>
     * <b>En este caso, la lista de valores no será tenia en cuenta</b>.
     * Si no se especifica el valor de la columna, se interpreta que es NULL.
     * 
     * @param string|array $values [opcional]<br />
     * Valores que le corresponden a las columnas. 
     * Puede ser array o string:
     * <ul>
     * <li>['val1', 'val2', ..., 'valN']</li>
     * <li>"val1,val2,...,valN"</li>
     * </ul>
     * Si es asociativo, las llaves NO serán tenidas en cuenta.  Si la cantidad
     * de elementos es mayor a la de columnas, los elementos sobrantes del final
     * serán REMOVIDOS silenciosamente.
     * @return int|boolean ID de la tabla creada, o FALSE en caso de error.
     */
    public function insert($tableName, $columns = NULL, $values = NULL)
    {
        $this->cambiarModo(TRUE);
        $ret = FALSE;
        if (is_string($tableName) && !empty($tableName)) {
            if (empty($columns)) {
                $this->setQuery('INSERT INTO ' . $tableName . ' () VALUES ()');
            } else {
                $c = array_from_string_list($columns);
                if (is_assoc($c)) {
                    $cols = '';
                    $vals = array();
                    foreach ($c as $key => $value) {
                        $cols .= (is_numeric($key) ? $value : $key) . ',';
                        $vals[] = is_numeric($key) ? NULL : $value;
                    }
                    // elimino la ultima ','
                    $cols = substr($cols, 0, -1);
                } else {
                    $cols = string_list_from_array($columns);
                    $vals = array_slice(array_from_string_list($values, 
                                                                ',', 
                                                                TRUE), 
                                        0, 
                                        count($c));
                    // creo que este bucle se puede implementar mas eficientemente
                    // con otras funciones de php
                    while (count($vals) < count($c)) {
                        array_push($vals, NULL);
                    }
                }

                $qmark = substr(str_repeat('?,', count($vals)), 0, -1);
                
                $this->setQuery('INSERT INTO ' . $tableName . ' (' . $cols
                                . ') VALUES (' . $qmark . ')');
                
                $this->setBindParam($this->bind_id($vals));
                $this->setQueryParams($vals);
            }

            $this->queryExecute();
            if ($this->getQueryData()) {
                $this->setQuery('SELECT DISTINCT LAST_INSERT_ID() FROM ' 
                                    . $tableName);
                $this->queryExecute();
                $ret = $this->getQueryData();
            }
        }
        $this->cambiarModo();
        return $ret;
    }
    
    /**
     * Actualiza una tabla de la DB.  Si la cantidad de columnas no es igual
     * a la de valores, completa automáticamente con NULL.  Los valores serán
     * debidamente sanitizados.
     * 
     * @param string $tableName Nombre de la tabla a actualizar.
     * @param string|array $columns Nombre de las columnas que recibirán un 
     * valor.  Puede ser array o string:
     * <ul>
     * <li>['col1', 'col2', ..., 'colN']</li>
     * <li>"col1,col2,...,colN".<br />
     * ¡Los espacios serán interpretados como parte del nombre de la columna!</li>
     * </ul>
     * Puede tambien recibir un array asociativo o una lista separada por comas,
     * donde la llave será el nombre de la columna:
     * <ul>
     * <li>['col1' => val1, 'col2' => val2, ..., 'colN' => valN]</li>
     * <li>"col1=val1,col2=val2,...,colN=valN"</li>
     * </ul>
     * <b>En este caso, la lista de valores no será tenia en cuenta</b>.
     * Si no se especifica el valor de la columna, se interpreta que es NULL.
     * 
     * @param string|array $values Valores que le corresponden a las columnas. 
     * Puede ser array o string:
     * <ul>
     * <li>['val1', 'val2', ..., 'valN']</li>
     * <li>"val1,val2,...,valN"</li>
     * </ul>
     * Si es asociativo, las llaves NO serán tenidas en cuenta.  Si la cantidad
     * de elementos es mayor a la de columnas, los elementos sobrantes del final
     * serán REMOVIDOS silenciosamente.
     * @param string $where_cond Clausula WHERE como string, formada de la
     * siguiente manera: <code>"col1 < ? AND col2 = ?"</code><br/>
     * Esto es, NO deben colocarse valores en este parámetro, sino hacerlo en 
     * $where_values.  De esta manera se asegura la correcta sanitización.
     * @param string|array $where_values Valores que se insertarán en el orden 
     * indicado en la clausula WHERE.  Puede ser array o string:
     * <ul>
     * <li>['val1', 'val2', ..., 'valN']</li>
     * <li>"val1,val2,...,valN"</li>
     * </ul>
     * Si la cantidad supera a los <b>?</b> del parámetro anterior, los
     * excedentes serán descartados silenciosamente.
     * @param string $limit [opcional]<br />
     * Límite de registros a actualizar o todos los registros encontrados.
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     * @access public
     */
    public function update($tableName, $columns, $values, 
                            $where_cond, $where_values, $limit = NULL)
    {
        if (is_string($tableName) && !empty($tableName) 
                && !empty($columns)
        ) {
            $c = array_from_string_list($columns);
            if (is_assoc($c)) {
                $cols = '';
                $vals = array();
                foreach ($c as $key => $value) {
                    $cols .= (is_numeric($key) ? $value : $key) . '=?,';
                    $vals[] = is_numeric($key) ? NULL : $value;
                }
                // elimino la ultima ','
                $cols = substr($cols, 0, -1);
            } else {
                $cols = string_list_from_array($columns, '=?,');
                $vals = array_slice(array_from_string_list($values, 
                                                            ',', 
                                                            TRUE), 
                                    0, 
                                    count($c));
                // creo que este bucle se puede implementar mas eficientemente
                // con otras funciones de php
                while (count($vals) < count($c)) {
                    array_push($vals, NULL);
                }
            }
            
            $limit = is_int($limit) ? ' LIMIT ?' : '';
            $where = is_string($where_cond) ? ' WHERE ' . $where_cond
                                        : '';
            
            $v = array_merge(
                    $vals, 
                    is_string($where_cond)
                        ? array_slice(array_from_string_list($where_values, 
                                                                ',', 
                                                                TRUE), 
                                        0, 
                                        substr_count($where_cond, '?')) 
                        : array(),
                    is_int($limit) ? array($limit) : array()
                );
            
            
            
            $this->setQuery('UPDATE TABLE ' . $tableName 
                                . ' SET ' . $cols
                                . $where
                                . $limit);
            
            $this->setBindParam($this->bind_id($v));
            $this->setQueryParams($v);            
            
            die(var_dump('UPDATE TABLE ' . $tableName 
                                . ' SET ' . $cols
                                . $where
                                . $limit, $v));
            
            $this->queryExecute();
            return $this->getQueryData();
        }
             
        return FALSE;
    }
}