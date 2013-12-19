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

// DB
function db_set_charset(&$db) 
{
    if ($db->character_set_name() != constant('SMP_DB_CHARSET')) {
        return mysqli_set_charset($db, constant('SMP_DB_CHARSET'));
    } else {
        return TRUE;
    }
}

function db_sanitizar(&$db, $str) 
{
    /**
     * Sanitiza un string para ser usado en una consulta.
     * 
     * @param mysqli $db Objeto mysqli (¡por referencia!).
     * @param string $str String que será sanitizado.
     * @return string String sanitizado para ser usado en una consulta.
     */
    if (db_set_charset($db)) {
        return addcslashes(mysqli_real_escape_string($db, stripslashes($str)), '%_');
    } else {
        return NULL;
    }
}

function db_connect_ro(&$db) 
{
    /**
     * Realiza una conexión con la base de datos en modo solo lectura.
     * Devuelve el resultado de la conexión.
     * 
     * @param mysqli_object $db Objeto de conexión
     * @return bool TRUE si fue exitosa, FALSE en caso contario
     */
    
    if (mysqli_real_connect($db, 
                            constant('SMP_DB_HOST'), 
                            constant('SMP_DB_USER_RO'), 
                            constant('SMP_DB_PASS_RO'), 
                            constant('SMP_DB_NAME'))
    ) {
        if (mysqli_real_query($db, 'SET NAMES ' . constant('SMP_DB_CHARSET'))) {
            if (db_set_charset($db)) {
                return TRUE;
            }
        }
    }

    return FALSE;
}

function db_connect_rw(&$db) 
{
    /**
     * Realiza una conexión con la base de datos en modo lectura/escritura.
     * Devuelve el resultado de la conexión.
     * 
     * @param mysqli_object $db Objeto de conexión
     * @return bool TRUE si fue exitosa, FALSE en caso contario
     */
    
    if (mysqli_real_connect($db, 
                            constant('SMP_DB_HOST'), 
                            constant('SMP_DB_USER_RW'), 
                            constant('SMP_DB_PASS_RW'), 
                            constant('SMP_DB_NAME'))
    ){
        if(mysqli_real_query($db, 'SET NAMES ' . constant('SMP_DB_CHARSET'))) {
            if (db_set_charset($db)) {
                return TRUE;
            }
        }
    }

    return FALSE;
}

function db_query_prepared_transaction(&$db, 
                                       $query_prepared, 
                                       $bind_param = NULL, 
                                       $params = NULL) 
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

function db_get_password_using_username(&$db, $usuario) 
{
    return mysqli_real_query($db, 
                             "SELECT PasswordSalted FROM Usuario WHERE Nombre='" 
                             . db_sanitizar($db, $usuario) . "'");
}

function db_get_password_using_uid(&$db, $uid) 
{
    return mysqli_real_query($db, 
                             "SELECT PasswordSalted FROM Usuario WHERE UID='"
                             . db_sanitizar($db, $uid) . "'");
}

function db_get_password(&$db, $param) 
{
    
    if (!empty($param)) {
        $value = db_sanitizar($db, $param);
        if (isValid_uuid($value)) {
            $query = db_get_password_using_uid($db, $value);
        } else {
            $query = db_get_password_using_username($db, $value);
        }

        $query = mysqli_store_result($db);
        $array = $query->fetch_row();
        $query->close();

        if (count($array) == 1) {
            return $array[0];
        }
    }
    
    return NULL;
}

function db_get_user_uid(&$db, $usuario) 
{
    // Devuelve el UID del correspondiente usuario
    if (!empty($usuario)) {
        if (mysqli_real_query($db, 
                              "SELECT UID FROM Usuario WHERE Nombre='"
                              . db_sanitizar($db, $usuario) . "'")
        ) {
            $query = mysqli_store_result($db);
            $array = $query->fetch_row();
            $query->close();

            if (count($array) == 1) {
                return $array[0];
            }
        }
    }   
        
    return NULL;
}
// --
// 
// DB auto
// Estas funciones se conectan por si solas a la db
function db_auto_get_user_uid($usuario) 
{
    // Devuelve el UID del correspondiente usuario
    
    if (!empty($usuario)) {
        $db = mysqli_init();
        if ($db) {
            if(db_connect_ro($db)) {
                $uid = db_get_user_uid($db, db_sanitizar($db, $usuario));
                $db->close();

                return $uid;
            }
            $db->close();
        }
    }
    
    return NULL;
}

function db_auto_get_password($usrname_uid) 
{
    // Devuelve password del usuario
    // $usrname_uid puede ser UID o nombre de usuario
    
    if (!empty($usrname_uid)) {
        $db = mysqli_init();
        if ($db) {
            if(db_connect_ro($db)) {
                $passwd = db_get_password($db, $usrname_uid);
                $db->close();

                return $passwd;                
            }
            $db->close();
        }
    }
    
    return NULL;
}
// --

define('FUNC_DB', TRUE);