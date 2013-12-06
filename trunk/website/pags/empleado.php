<?php

/*****************************************************************************
 *  SiMaPe
 *  Sistema Integrado de Manejo de Personal
 *  Copyright (C) <2013>  <Ivan Ariel Barrera Oro>
 *  ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *****************************************************************************/

// ESTA PAGINA NO DEBE SER ACCEDIDA DIRECTAMENTE POR EL USUARIO

/*
 * Esta página muestra el perfil de empleado
 */

if (!defined('CONFIG')) { require_once 'loadconfig.php'; }

session_do();

// Instanciar un token del formulario
$form_token = form_token_get_new();

// Recuperar datos de login
$sessionkey = session_get_sessionkey();
$usuario = session_get_username();

if (page_token_validate(get_get_pagetkn()) && 
        (form_token_validate(post_get_frmtkn()) !== FALSE) &&
        fingerprint_token_validate() &&
        sessionkey_validate($usuario, $sessionkey)) {
    // Login OK 
    $db_conn_ok = TRUE;
    
    if (!empty(post_get_frmBtn('Cancelar'))) {
        // Volver a main
        $redirect = LOC_NAV;
        $params = NULL;
    }
    elseif ((form_token_validate(post_get_frmtkn()) === TRUE) &&
            !empty(post_get_frmBtn('Aceptar'))) {
        // No cambiar ningun dato si el form token no es válido
        // (es decir, no puede ser null en este caso)
        // 
        // Validar y almacenar datos
        $password_db = db_auto_get_password($usuario);
        $password_current = post_get_frmPwd('Login');
        if(password_validate($password_current, $password_db)) {
            // Clave correcta, guardar datos
            // ToDo
            // PENDIENTE: CHEQUEAR PERMISOS DEL USUARIO!
            // PENDIENTE: Advertir en cambio de perfil, estado y nombre!
            
            // Solo guardar los datos que hayan cambiado

            // Datos guardados de la busqueda anterior
            $empdata = session_get_data();
            
            // Elimino datos de post que molestan
            // TODO
            // hacer API
            post_unset_frmtkn();
            unset($_POST['frm_btnAceptar'], $_POST['frm_btnCancelar'], 
                    $_POST['frm_pwdLogin'], $empdata['CreacionTimestamp'], 
                    $empdata['Selects'], $empdata['ModificacionTimestamp']);
                       
            /*
             * FIXME !!
             * Cargar foto!
             */
            unset($empdata['Fotografia'], $empdata['Fotografia_type']);
            /**/
            
            //echo "<br />empdata " . var_dump($empdata);
            /*var_dump($_POST);
            var_dump($empdata);*/
            reset($_POST);
            do {
                $key = key($_POST);
                $post = post_get($key);
                //echo "<br />emp[$key]: ". $empdata[$key] . "-- post: " . $post;
                if (((string) $empdata[$key]) === ((string) $post)) {
                    // no hubo cambio
                    unset($empdata[$key]);
                } else {
                    // guardar cambio
                    $empdata[$key] = $post;
                }
            } while (next($_POST) !== FALSE);
            /*var_dump($empdata);
            die("---");*/
            // TODO
            // Validar datos como telefono, celular, email, cuil, dni...
            // acomodarlos para que sean cargados en la DB
            // *** Recordar: hay valores que se buscan por tablas!
            
            
            ///* DBG */ unset($empdata); /**/
            //die(var_dump($empdata));
            $db_conn_ok = FALSE;
            if (!empty($empdata)) {
                $db = mysqli_init();
                if ($db) {
                    if (db_connect_rw($db)) {
                        $empdata['ModificacionTimestamp'] = time();
                        
                        $query = "UPDATE Empleado SET ";
                        reset($empdata);
                        do {
                            $query .= key($empdata) . " = '" . current($empdata) . "', ";
                        } while (next($empdata));
                        // Borro la ultima coma
                        $query = substr($query, 0,  strlen($query) - 2) 
                                . " WHERE EmpleadoId = (SELECT EmpleadoId FROM "
                                . "Usuario WHERE Nombre = '$usuario')";
                        //die("$query");
                        if ($db->real_query($query)) {
                                $db_conn_ok = TRUE;
                                session_set_msg("Datos ingresados correctamente");
                        }
                    }
                    $db->close();
                }
                /* TODO */
                session_set_msg(session_get_msg() . "\n **A&uacute;n no implementado para todos los campos");
                /**/
            } else {
                // No hay cambios!
                // Mostrar mensaje
                session_set_msg("No hay cambios para guardar");
            }
        } else {
            // Clave incorrecta, pero no cerrar sesion, se pudo haber
            // equivocado...
            // No leer ninguna otra entrada del usuario
            unset($_POST);
            session_set_errt($err_wrongpass);
        }
        
        // Recargar pagina 
        $redirect = LOC_NAV;
        $params = "accion=perfilemp";
        
        // Limpiar los datos para recargarlos
        session_unset_data();
    }
    else {
        // No toca boton... cargar datos   
        $empdata = session_get_data_dirty();
        if (empty($empdata)) {
            $db_conn_ok = FALSE;    // Se pondra a TRUE si esta todo ok

            $db = new mysqli;
            if ($db) {
                if (db_connect_ro($db)) {
                    $query = "SELECT Empleado.Nombre, Apellido, Fotografia, Titulo, "
                            . "Sexo, FechaNac, FechaIngresoDependencia, "
                            . "FechaIngresoJusticia, ResolIngreso_Nro, "
                            . "ResolIngreso_Año, DocumentoNro, CUIL, LegajoNro, "
                            . "TelNro, TelCodArea, CelNro, CelCodArea, Email, "
                            . "NivelEstudio.Descripcion AS 'NivelEstudio', ProfesionTitulo, EstadoCivil, "
                            . "Estado.Descripcion AS 'Estado', Comentario, Empleado.CreacionTimestamp, "
                            . "Empleado.ModificacionTimestamp FROM Empleado INNER "
                            . "JOIN NivelEstudio USING (NivelEstudioId) INNER JOIN "
                            . "Estado USING (EstadoId) INNER JOIN Usuario USING "
                            . "(EmpleadoId) WHERE Usuario.Nombre = '" 
                            . $usuario . "'";

                    if($db->real_query($query)) {
                        $result = $db->store_result();
                        $empdata = $result->fetch_array(MYSQLI_ASSOC);
                        $result->close();

                        // Obtener los valores de los enums y otros:
                        // Titulo enum
                        // Sexo enum
                        // EstadoCivil enum
                        // NivelEstudio tabla
                        // Estado tabla
                        $buscados = array('Enums' => array(0 => 'Titulo', 'Sexo', 'EstadoCivil'),
                                          'Tablas' => array(0 => 'NivelEstudio', 'Estado'));
                        $selects = array();

                        reset($buscados);
                        $key = key($buscados);
                        foreach ($buscados as $buscado) {
                            foreach ($buscado as $col_o_tbl) {
                                $querys = array('Enums' => "SELECT TRIM(TRAILING ')' "
                                . "FROM TRIM(LEADING '(' FROM TRIM(LEADING "
                                . "'enum' FROM column_type))) column_type "
                                . "FROM information_schema.columns WHERE "
                                . "table_schema = '" . constant('DB_NOMBRE') 
                                . "' AND table_name = 'Empleado' AND "
                                . "column_name = '" . $col_o_tbl . "'",
                                        'Tablas' => "SELECT Descripcion AS 'Valores' FROM "
                                . $col_o_tbl . " WHERE Descripcion NOT IN (SELECT Descripcion "
                                . "FROM " . $col_o_tbl . " INNER JOIN Empleado USING ("
                                . $col_o_tbl . "Id) INNER JOIN Usuario USING (EmpleadoId) "
                                . "WHERE Usuario.Nombre = '" . $usuario . "')");

                                if($db->real_query($querys[$key])) {
                                    $result = $db->store_result();
                                    while($row = $result->fetch_assoc()) {
                                        $selects[$col_o_tbl][] = $row;
                                    }
                                    $db_conn_ok = TRUE;
                                    $result->close();
                                }
                            }
                            $key = key($buscados);
                        }
                        unset($buscado, $col_o_tbl);
                    }               
                }
                $db->close();

                if (!empty($selects)) {
                    // Convertir los enums en un array
                    foreach ($buscados['Enums'] as $enum) {
                        $selects[$enum] = str_getcsv($selects[$enum][0]['column_type'], ',', "'");
                        // Eliminar el valor que ya existe
                        foreach ($selects[$enum] as $valor) {
                            if ($valor != $empdata[$enum]) {
                                $aux[] = $valor;
                            }
                        }
                        $selects[$enum] = $aux;
                        unset($aux);
                    }
                    unset($enum);

                    // Acomodar el array selects de tablas
                    foreach ($buscados['Tablas'] as $tbl) 
                    {
                        foreach ($selects[$tbl] as $array) 
                        {
                            $aux[$tbl][] = $array['Valores'];
                        }
                        $selects[$tbl] = $aux[$tbl];
                        unset($aux);
                    }
                    unset($tbl, $array);
                    
                    // Guardar en $empdata
                    $empdata['Selects'] = $selects;
                    
                    // TODO
                    // Verificar tambien si se trata de una ruta a un archivo
                    if (isset($empdata['Fotografia']) 
                        && !isValid_b64(substr($empdata['Fotografia'], 0, 100))) {
                        // Foto binaria, convertirla antes a b64
                        // no hay otra forma de mostrarla... que yo sepa
                        $empdata['Fotografia'] = base64_encode($empdata['Fotografia']);
                    }
                    // Guardo los datos para comparar 
                    // cuando el usuario acepte cambios
                    // (y no buscar en la DB nuevamente)
                    //die(var_dump($empdata));
                    session_set_data($empdata);
                    unset($selects);
                }
            }
        }
    }
    
    // ToDo
    // Manejo de errores...
    //
    if (!$db_conn_ok) {
        session_set_errt($err_dbconn);
    }
    
    // Guardar el form token para validar
    session_set_frmtkn(form_token_get_formtkn($form_token));
} else {
    // Error de autenticacion
    //
    session_terminate();
    session_do();
    session_set_errt($err_t_authfail);
    $redirect = LOC_NAV;  
    $params = 'accion=logout';
}

if (isset($redirect)) {
    page_goto($redirect, $params);
    exit();
}
?>

<?php echo page_get_head('SiMaPe - Mi perfil de empleado'); ?>
<?php echo page_get_body(); ?>
<?php echo page_get_header(); ?>
<?php echo page_get_header_close(); ?>
<?php echo page_get_navbarV(); ?>
<?php echo page_get_main(); ?>

    <form style="text-align: center; margin: 0px auto; width: auto;" 
          method="POST" enctype="multipart/form-data" action="<?php echo page_get_url(LOC_EMPLEADO, 'pagetkn=' . get_get_pagetkn()); ?>" >
        <fieldset style="text-align: center; width: auto; margin:0 auto;">
            <table border="0" cellpadding="1" cellspacing="1" 
                    style="width: 500px; margin: auto; text-align: center;">
                <tbody>
                    <tr>
                        <td>
                            <select name="Titulo" 
                                    title="T&iacute;tulo">
                                <?php
                                if (!empty($empdata['Titulo'])) {
                                    echo "<option selected value='" 
                                         . $empdata['Titulo'] . "'>" 
                                         . $empdata['Titulo'] 
                                         . "</option>";
                                } else {
                                    echo "<option selected "
                                         . "value=''> </option>";
                                    $empty_set = TRUE;
                                }
                                foreach ($empdata['Selects']['Titulo'] as $valor) {
                                    if (!isset($empty_set) && empty($valor)) {
                                        echo "<option value=''> </option>";
                                        $empty_set = TRUE;
                                    }
                                    else {
                                        echo "<option value='". $valor 
                                             . "'>$valor</option>";
                                    }
                                }
                                unset($array);  
                                unset($valor); // Prolijidad despues de foreach

                                if (!isset($empty_set)) {
                                    // Agregar uno vacio
                                    echo "<option value=''> </option>";
                                } else {
                                    unset($empty_set);
                                }
                                ?>
                            </select>
                        </td>
                        <td style="text-align: left;"><input name="Nombre" 
                                  title="Nombre"
                                  type="text" required value="<?php echo $empdata['Nombre']; ?>"
                                  placeholder="Nombre(s)"
                                  size="20"/>
                        </td>
                        <td style="text-align: left;"><input name="Apellido" 
                                  title="Apellido"
                                  type="text" required value="<?php echo $empdata['Apellido']; ?>"
                                  placeholder="Apellido(s)"
                                  size="20"/>
                        </td>
                        <td style="text-align: left;"><input name="ProfesionTitulo" 
                                  title="T&iacute;tulo profesional"
                                  type="text" value="<?php echo $empdata['ProfesionTitulo']; ?>"
                                  placeholder="T&iacute;tulo profesional"
                                  size="20"/>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" rowspan="7" style="text-align: center; vertical-align: middle;">
                            <?php
                            // La foto debe ser b64 o una ruta si o si,
                            // procesar todo antes de esta rutina
                            if(isset($empdata['Fotografia'])) {
                                if (isValid_b64(substr($empdata['Fotografia'], 0, 100))) {
                                    // Foto base64
                                    echo "<img src='data:image/jpeg;base64," 
                                    . $empdata['Fotografia'] 
                                    . "' alt='No es posible mostrar la foto'"
                                    . " title='Fotograf&iacute;a del perfil'"
                                    . " style='max-height: 180px; max-width: 280px;' />";
                                } else {
                                    // Foto es una ruta a un archivo en el server
                                    echo "<img src='" . $empdata['Fotografia'] 
                                            . "' alt='No es posible mostrar la foto'"
                                            . " title='Fotograf&iacute;a del perfil'"
                                            . " style='max-height: 180px; max-width: 280px;' />";
                                }
                            } else {
                                echo "No hay foto disponible";
                            }
                            ?>
                        </td>   
                        <td>
                            <select name="Sexo" title="Sexo">
                                <?php
                                // NOT NULL
                                    echo "<option selected value='" 
                                         . $empdata['Sexo'] . "'>" 
                                         . $empdata['Sexo'] 
                                         . "</option>";

                                foreach ($empdata['Selects']['Sexo'] as $valor) {
                                    if (!empty($valor)) {
                                        echo "<option value='". $valor 
                                             . "'>$valor</option>";
                                    }
                                }
                                unset($array);  
                                unset($valor); // Prolijidad despues de foreach
                                ?>
                            </select>
                        </td> 
                        <td>
                            <select name="NivelEstudio" 
                                    title="Nivel de estudio">
                                <?php
                                // No puede ser NULL
                                echo "<option selected value='" 
                                     . $empdata['NivelEstudio'] . "'>" 
                                     . $empdata['NivelEstudio'] 
                                     . "</option>";

                                foreach ($empdata['Selects']['NivelEstudio'] as $valor) {
                                    if (!empty($valor)) {
                                        echo "<option value='". $valor 
                                             . "'>$valor</option>";
                                    }
                                }
                                unset($array);  
                                unset($valor); // Prolijidad despues de foreach
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <select name="EstadoCivil" 
                                    title="Estado Civil">
                                <?php
                                // Not NULL
                                    echo "<option selected value='" 
                                         . $empdata['EstadoCivil'] . "'>" 
                                         . $empdata['EstadoCivil'] 
                                         . "</option>";

                                foreach ($empdata['Selects']['EstadoCivil'] as $valor) {
                                    if (!empty($valor)) {
                                        echo "<option value='". $valor 
                                             . "'>$valor</option>";
                                    }
                                }
                                unset($array);  
                                unset($valor); // Prolijidad despues de foreach
                                ?>
                            </select>
                        </td>
                        <td>
                            <select name="Estado" 
                                    title="Estado actual">
                                <?php
                                // No puede ser NULL
                                echo "<option selected value='" 
                                     . $empdata['Estado'] . "'>" 
                                     . $empdata['Estado'] 
                                     . "</option>";

                                foreach ($empdata['Selects']['Estado'] as $valor) {
                                    if (!empty($valor)) {
                                        echo "<option value='". $valor 
                                             . "'>$valor</option>";
                                    }
                                }
                                unset($array);  
                                unset($valor); // Prolijidad despues de foreach
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><input name="FechaNac" 
                                  title="Fecha de nacimiento"
                                  type="text" value="<?php echo $empdata['FechaNac']; ?>"
                                  placeholder="Nacimiento"
                                  size="20"
                                  required />
                        </td>
                        <td><input name="DocumentoNro" 
                                   title="N&uacute;mero de documento"
                                   type="text" value="<?php echo $empdata['DocumentoNro']; ?>"
                                   placeholder="DNI"
                                   size="20" required />
                        </td>
                    </tr>
                    <tr>
                        <td><input name="FechaIngresoJusticia" 
                                  title="Fecha de ingreso a la justicia"
                                  type="text" value="<?php echo $empdata['FechaIngresoJusticia']; ?>"
                                  placeholder="Ingreso a la justicia"
                                  size="20" required />
                        </td>
                        <td><input name="FechaIngresoDependencia" 
                                  title="Fecha de ingreso a esta Dependencia"
                                  type="text" value="<?php echo $empdata['FechaIngresoDependencia']; ?>"
                                  placeholder="Ingreso a esta Dependencia"
                                  size="20" required />
                        </td>
                    </tr>

                    <tr>
                        <td><input name="CUIL" 
                                   title="N&uacute;mero de CUIL"
                                   type="text" value="<?php echo $empdata['CUIL']; ?>"
                                   placeholder="CUIL"
                                   size="20" required />
                        </td>
                        <td><input name="Email" 
                                   title="Direcci&oacute;n de email"
                                   type="text" value="<?php echo $empdata['Email']; ?>"
                                   placeholder="Email"
                                   size="20"/>
                        </td>
                    </tr>
                    <tr>
                        <td><input name="LegajoNro" 
                                   title="N&uacute;mero de Legajo"
                                   type="text" value="<?php echo $empdata['LegajoNro']; ?>"
                                   placeholder="Legajo"
                                   size="20" required />
                        </td>
                        <td style="text-align: center;"><input name="ResolIngreso_Nro" 
                                   title="N&uacute;mero de Resoluci&oacute;n de ingreso"
                                   type="text" value="<?php echo $empdata['ResolIngreso_Nro']; ?>"
                                   placeholder="Res. de ingreso"
                                   size="7" required /><input name="ResolIngreso_Año" 
                                   title="A&ntilde;o de Resoluci&oacute;n de ingreso"
                                   type="text" value="<?php echo $empdata['ResolIngreso_Año']; ?>"
                                   placeholder="A&ntilde;o Res. de ingreso"
                                   size="6" required />
                        </td>
                    </tr>
                    <tr>
                        <td><input name="TelCodArea" 
                                   title="C&oacute;digo de &aacute;rea"
                                   type="text" value="<?php echo $empdata['TelCodArea']; ?>"
                                   placeholder="C&oacute;d. &aacute;rea"
                                   size="2" required /><input name="TelNro" 
                                   title="N&uacute;mero de tel&eacute;fono"
                                   type="text" value="<?php echo $empdata['TelNro']; ?>"
                                   placeholder="Tel&eacute;fono"
                                   size="11" required />
                        </td>
                        <td><input name="CelCodArea" 
                                   title="C&oacute;digo de &aacute;rea"
                                   type="text" value="<?php echo $empdata['CelCodArea']; ?>"
                                   placeholder="C&oacute;d. &aacute;rea"
                                   size="2" required /><input name="CelNro" 
                                   title="N&uacute;mero de celular"
                                   type="text" value="<?php echo $empdata['CelNro']; ?>"
                                   placeholder="Celular"
                                   size="11" required />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4"><input name="Comentario" 
                                   title="Informaci&oacute;n adicional"
                                   type="text" value="<?php echo $empdata['Comentario']; ?>"
                                   placeholder="Informaci&oacute;n adicional"
                                   size="80"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <br />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" 
                            style="text-align: center; font-weight: normal;">Cambiar la foto del perfil: <input type="hidden" 
                                name="MAX_FILE_SIZE" value="<?php 
                            echo constant('FILE_MAXIMGSIZE'); ?>" /><input name="frm_file" 
                                   type="file" title="Cargar nueva foto" 
                                   accept="image/*" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4"><br /></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <?php
                            echo strftime('<i>Fecha de creaci&oacute;n:</i> %d de %B de %G, %H:%M:%S' , 
                                    $empdata['CreacionTimestamp']);
                            ?></td> 
                        <td colspan="2" style="text-align: center;">
                            <?php
                            echo strftime('<i>Fecha de modificaci&oacute;n:</i> %d de %B de %G, %H:%M:%S' , 
                                    $empdata['ModificacionTimestamp']);
                            ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: center;"><br />
                          Contraseña actual: <input name="frm_pwdLogin" 
                                 title="Ingrese su contraseña actual para autenticarse"
                                 maxlength="<?php echo constant('PWD_MAXLEN'); ?>" 
                                 type="password" 
                                 placeholder="(para aceptar los cambios)"
                                 size="20"/>
                      </td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: center;">
                              <?php 
                                if (!empty(err_get_errt())) 
                                {
                                    echo("<address class='fadeout' "
                                            . "style='color:red; "
                                            . "text-align: center;'>" 
                                            . err_get_errt() 
                                            . "</address>\n"); 
                                    err_unset_errt();

                                    $br = FALSE;
                                }
                                else {
                                    $br = TRUE;
                                }

                                if (!empty(session_get_msg())) 
                                {
                                    echo("<address class='fadeout' "
                                            . "style='color:green; "
                                            . "text-align: center;'>" 
                                            . session_get_msg() 
                                            . "</address>\n"); 
                                    session_unset_msg();

                                    $br = FALSE;
                                }

                                if ($br)
                                {
                                    echo("<br />\n");
                                }
                              ?>
                        </td>
                    </tr>
                    <tr>
                      <td colspan="4" style="text-align: center;">
                          <input name="frm_btnAceptar" 
                                 value="Guardar los cambios"
                                 title="Guardar los cambios"
                                 type="submit" /> 
                          <input name="frm_btnCancelar" value="Volver"
                                 title="Volver a la p&aacute;gina principal" 
                                 type="submit" />
                      </td>
                  </tr>
              </tbody>
           </table>
            <input type="hidden" name="form_token" 
                   value="<?php echo form_token_get_randtkn($form_token); ?>" />
        </fieldset>
    </form>
<?php echo page_get_main_close(); ?>
<?php echo page_get_footer(); ?>