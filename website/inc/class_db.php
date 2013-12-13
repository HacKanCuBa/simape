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
 * Esta clase maneja todo lo referido a la DB:
 * - Conexion RO
 * - Conexion RW
 * - Querys
 * - etc
 *
 * @version 0.7 untested
 */

class DB {
    protected $db;
    protected $queryStmt, $bindParam, $queryParams;
    
    // Metodos
    // __ SPECIALS
    function __construct($ModoRW = FALSE) 
    {
        /**
         * Genera un objeto mysqli y conecta en el modo indicado.
         * 
         * @param boolean $ModoRW Si es TRUE, conecta en modo RW; si es FALSE,
         * en modo RO (por defecto).
         */
       
        if ($ModoRW) {
            $db = new mysqli(DB_HOST, DB_USUARIO_RW, DB_PASS_RW, DB_NOMBRE);
        } else {
            $db = new mysqli(DB_HOST, DB_USUARIO_RO, DB_PASS_RO, DB_NOMBRE);
        }
        
        $this->db = $db;
        $this->setCharset();
    }
    
    // __ PRIV
    
    // __ PROT
    protected function setCharset() 
    {
        if ($this->db->character_set_name() != constant('DB_CHARSET')) {
            if ($this->db->set_charset(constant('DB_CHARSET'))) {
                return $this->db->real_query('SET NAMES ' . constant('DB_CHARSET'));
            } else {
                return FALSE;
            }
        } else {
            return TRUE;
        }
    }
    
    // __ PUB
    // Set
    public function setQuery(string $queryPrepared) 
    {
        /**
         * Prepara el objeto para ejecutar la query indicada.  Devuelve TRUE
         * si se preparo exitosamente, FALSE en caso contrario.
         * 
         * @param string $queryPrepared Query a ser ejecutada.  Debe ser  
         * Prepared Statement, a menos que no contenga parámetros.  NOTA: los
         * statements (<?>) ¡no van encomillados!.
         * @return boolean TRUE si tuvo éxito, FALSE si no.
         */
        
        if (!empty($queryPrepared) && is_string($queryPrepared)) {
            $stmt = $this->db->prepare($queryPrepared);
            if($stmt) {
                $this->queryStmt = $stmt;
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    public function setBindParam(string $bindParam) 
    {
        /**
         * Almacena los parametros de binding para la query.  Devuelve TRUE
         * si tuvo éxito, FALSE si no.
         * 
         * @param string $bindParam Parametros para indicar el tipo de
         * binding: i: integer; s: string; d: double; b: blob
         * @return boolean Devuelve TRUE si tuvo éxito, FALSE si no.
         */
        
        if (!empty($bindParam) && is_string($bindParam)) {
            $this->bindParam = $bindParam;
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function setQueryParams(array $queryParams)
    {
        /**
         * Almacena los parámetros de la query que reemplazan a los statements 
         * (<?>).  ¡Debe ser un array!.  Devuelve TRUE si tuvo éxito, 
         * FALSE si no.
         * 
         * @param array $queryParams Valores que serán insertados 
         * en la query.
         * @return boolean Devuelve TRUE si tuvo éxito, FALSE si no.
         */
        
        if (!empty($queryParams) && is_array($queryParams)) {
            $this->queryParams = $queryParams;
            return TRUE;
        }
        
        return FALSE;
    }
    // --
    // Get
    
    // --
    // Query
    public function queryExecute() 
    {
        /**
         * Ejecuta una query (SELECT, INSERT, UPDATE, DELETE) con o sin 
         * parámetros usando transacciones.
         * Devuelve TRUE si tuvo éxito, y ejecuta commit, o FALSE si no, y 
         * hace rollback.
         * Para obtener los datos de un SELECT, llamar a queryGetData().
         * 
         * @see DB::queryGetData()
         * 
         * @return boolean TRUE si tuvo éxito, FALSE si no.
         */

        $result = FALSE;

        if(isset($this->db) && isset($this->queryStmt)) {
            $this->db->autocommit(FALSE);

            if(!empty($this->bindParam) && !empty($this->queryParams)) {
                $bind_names[] = $this->bindParam;
                for ($i = 0; $i < count($this->queryParams); $i++) {
                    $bind_name = 'bind' . $i;
                    $$bind_name = $this->queryParams[$i];
                    $bind_names[] = &$$bind_name;
                }
                $result = call_user_func_array(array($this->queryStmt, 'bind_param'), $bind_names);
            } else {
                // Continuar, no hay que bindear la query
                $result = TRUE;
            }
            
            if($result) {
                $result = $this->queryStmt->execute();
                if ($result) {
                    $this->db->commit();
                } else {
                    $this->db->rollback();
                }
            }

            $this->db->autocommit(TRUE);
        }

        return $result;
    }
    
    public function queryGetData() 
    {
        /**
         * Devuelve los datos obtenidos de la query (cuando la misma se trató 
         * de un SELECT) como array.  Si no se obtuvieron datos, devuelve TRUE.
         * Si la consulta devolvió error, devuelve FALSE.
         * El array devuelto es:
         *  - Asociativo cuando la consulta devuelve una fila con multiples 
         * columnas;
         *  - Numerado cuando la consulta devuelve una o varias filas c/u con 
         * sólo una columna; 
         *  - Mixto cuando la consulta devuelve multiples filas con multiples 
         * columnas (las filas serán índice numerado, y dentro un array 
         * asociativo con las columnas como indices).
         * 
         * IMPORTANTE: Requiere native driver (php5-mysqlnd)
         * ATENCIÓN: el resultado debería evaluarse con is_array() para 
         * determinar si se obtuvieron datos en la consulta.
         *
         * @return mixed Si la consulta produjo resultados los devuelve como 
         * array; si no hubieron resultados pero la consulta fue exitosa, 
         * devuele TRUE; en caso de error, FALSE.
         *  
         */
        
        $result = FALSE;
        
        if (isset($this->queryStmt) && ($this->queryStmt->errno == 0)) {  
            $result = TRUE;
            
            $data = $this->queryStmt->get_result();
            $this->queryStmt->close();
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
        
        return $result;
    }
    // --
    // Otras
    public function sanitizar($valor) 
    {
        /**
         * Sanitiza un valor para ser usado en una consulta.  Puede tratarse de
         * un array o un string.
         * NOTA: No es necesario si se emplea una prepared query.
         *
         * @param mixed $valor Valor que será sanitizado.
         * @return mixed Valor sanitizado para ser usado en una consulta.  Será
         * del mismo tipo que $valor, o bien NULL en caso de error
         */
        if ($this->setCharset() && !empty($valor)) {
            if (is_array($valor)) {
                foreach ($valor as $key => $value) {
                    $result[$key] = $this->sanitizar($value);
                }
            } else {
                $result = addcslashes(
                            $this->db->real_escape_string(stripslashes($valor))
                            , '%_');
            }
            return $result;
        } else {
            return NULL;
        }
    }
    // --
}