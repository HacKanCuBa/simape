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
    private $db;
    
    // Metodos
    // __ SPECIALS
    function __construct($ModoRW = FALSE) {
        /**
         * Genera un objeto mysqli y conecta en el modo indicado.
         * 
         * @param boolean $ModoRW Si es TRUE, conecta en modo Rw; si es FALSE,
         * en modo RO (por defecto).
         * @return DB Devuelve un objeto DB.
         */
       
        if ($ModoRW) {
            $db = new mysqli(DB_HOST, DB_USUARIO_RW, DB_PASS_RW, DB_NOMBRE);
        } else {
            $db = new mysqli(DB_HOST, DB_USUARIO_RO, DB_PASS_RO, DB_NOMBRE);
        }
        
        if ($this->setDB($db)) {
            $this->setCharset();
            
        }
    }
    
    // __ PRIV
    private function setCharset() 
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
    public function setDB(mysqli $db) {
        if (isset($db)) {
            $this->db = $db;
            return TRUE;
        }
        
        return FALSE;
    }
    
    // Get
    public function getDB() {
        return $this->db;
    }
    
    // DB
    function query(&$db, $query_prepared, $bind_param = NULL, $params = NULL) 
    {
        /**
         * Realiza una query (select, insert, update, delete) con o sin parámetros 
         * usando transacciones.
         * Devuelve TRUE si todo salio bien (para insert, update y delete), el 
         * resultado de la query como array para el caso de select 
         * (devuelve TRUE si no se obtuvieron datos) o FALSE en caso de error 
         * (automaticamente hace rollback).
         * Para el select, el array devuelto es asociativo cuando la consulta
         * devuelve una fila con multiples columnas; es numerado cuando la 
         * consulta devuelve una o varias filas c/u con solo una columna.
         * Es mixto cuando la consulta devuelve multiples filas con multiples 
         * columnas (las filas seran indice numerado, y dentro un array asociativo
         * con las columnas como indides).
         * 
         * @param mysqli $db Objeto de conexion mysqli.
         * @param string $query_prepared Query a ser ejecutada.  Debe ser  
         * Prepared Statement, a menos que no contenga parámetros.  NOTA: los
         * statements (?) ¡no van encomillados!.
         * @param string $bind_param [Opcional] Parametros para indicar el tipo de
         * binding: i: integer; s: string; d: double; b: blob
         * @param array $params [Opcional] Valores que serán insertados en la query.
         * @return boolean Para INSERT, UPDATE y DELETE: TRUE si se ejecuto 
         * exitosamente, FALSE si no (con auto-rollback).  Para el SELECT: si se 
         * produjeron resultados los devuelve como array o TRUE si no hubieron 
         * resultados pero la consulta fue exitosa.  FALSE en caso de error.
         */

        $result = FALSE;

        if(isset($db) && !empty($query_prepared)) {
            $db->autocommit(FALSE);

            $stmt = $db->prepare($query_prepared);
            if ($stmt) {
                if(!empty($bind_param) && !empty($params)) {
                    $bind_names[] = $bind_param;
                    for ($i = 0; $i < count($params); $i++) {
                        $bind_name = 'bind' . $i;
                        $$bind_name = $params[$i];
                        $bind_names[] = &$$bind_name;
                    }
                    $result = call_user_func_array(array($stmt, 'bind_param'), $bind_names);
                } else {
                    $result = TRUE;
                }
                // call_user_func_array(array($stmt, "bind_param"), array_merge(array($bind_param), $params))
                if($result) {
                    $result = $stmt->execute();
                    if ($result) {
                        $db->commit();
                        // Verificar si produjo resultados (si era select)

                        /*
                         * Esto es para el driver php5-mysql.
                         * Pero de todas formas funciona mal cuando la consulta
                         * devuelve más de una fila...
                         * 
                        $data = $stmt->result_metadata();
                        if ($data) {
                            while ($field = $data->fetch_field()) { 
                                $aux = $field->name; 
                                $$aux = NULL; 
                                $parameters[$field->name] = &$$aux; 
                                echo "\n$field->name\n";
                                var_dump($parameters);
                            }

                            call_user_func_array(array($stmt, 'bind_result'), $parameters); 

                            while($stmt->fetch()) { 
                                $query_result[] = $parameters; 
                            }
                            var_dump($parameters);
                            if (isset($query_result) 
                                    && count($query_result, COUNT_NORMAL) == 1) {
                                $query_result = $query_result[0];
                            }
                            var_dump($query_result);
                            $data->close();
                        }*/

                        // !! Requiere driver php5-mysqlnd !!
                        $data = $stmt->get_result();
                        if ($data) {
                            //$keys = array();
                            while ($row = $data->fetch_assoc()) {
    //                            var_dump($row);
                                if (count($row, COUNT_NORMAL) == 1) {
                                    $value = current($row);
                                } else {
                                    $value = $row;
                                }
    //                            var_dump($value);
                                $query_result[] = $value;
    //                            var_dump($query_result);
                            }
                            $data->free();

                            if (isset($query_result) 
                                    && count($query_result, COUNT_NORMAL) == 1) {
                                $query_result = $query_result[0];
                            }
    //                        var_dump($query_result);
    //                        die("query");
                        } elseif ($stmt->errno != 0) {
                            // Era select y hubo error
                            $result = FALSE;
                        }
                    } else {
                        $db->rollback();
                    }
                }
                $stmt->close();
            }      
            $db->autocommit(TRUE);
        }

        if (isset($query_result)) {
            return $query_result;
        } else {
            return $result;
        }
    }
    
}