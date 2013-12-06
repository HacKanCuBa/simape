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
 * Esta página muestra el perfil del usuario
 * ToDo
 * - Separar errores y mostrar mensajes apropiados
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
        // Volver
        $redirect = LOC_NAV;
        // Limpiar los datos
        session_unset_data();
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
            $usrdata = session_get_data();
            
            $usuario_new = username_format(post_get_frmText('Usuario'));
            if (($usuario_new != $usuario)
                    && isValid_username($usuario_new)) {               
                $usrdata['Usuario'] = $usuario_new;
                // Guardar el nuevo nombre de usuario
                session_set_username($usuario_new);
            }
            else {
                unset($usrdata['Usuario']);
            }            
            
            $password_new = post_get_frmPwd('New');
            if (!empty($password_new)) {
                if (isValid_password($password_new) &&
                        ($password_new == post_get_frmPwd('NewV')) &&
                        (!password_validate($password_new, $password_db))) {

                    $usrdata['PasswordSalted'] = password_get($password_new);
                }
                else {
                    session_set_errt("La contrase&ntilde;a nueva no cumple las especificaciones");
                }
            }
            
            if (!empty(post_get_frmSelect('Perfil'))) {
                // ToDO habria que matchear ahora con la DB y ver si es valido
                // o bien matchear despues, dara error la query
                if ($usrdata['Perfil'] != post_get_frmSelect('Perfil')) {
                    $usrdata['Perfil'] = post_get_frmSelect('Perfil');
                }
                else {
                    unset ($usrdata['Perfil']);
                }
            }
            
            // Nota: el checkbox devuelve NULL si no esta tildado
            if ($usrdata['Activo'] == post_get_frmCheckbox('Activo')) {
                unset($usrdata['Activo']);
            }
            elseif ($usrdata['Activo'] == "1") {
                $usrdata['Activo'] = "0";
            }
            else {
                $usrdata['Activo'] = "1";
            }
            
            // Importante: No modificar CreacionTimestamp
            unset($usrdata['CreacionTimestamp']); 
            unset($usrdata['ModificacionTimestamp']);
            
            // Escribir DB si hubieron cambios
            if (!empty($usrdata)) {
                $db = mysqli_init();
                if ($db) {
                    if (db_connect_rw($db)) {
                        $usrdata['ModificacionTimestamp'] = time();
                        
                        // Uso el UID para hacer las querys, pq
                        // el nombre de usuario puede cambiar
                        $uid = db_get_user_uid($db, $usuario);
                        $querys = array( 
                            'Perfil' => "UPDATE Usuario SET "
                            . "Usuario.UsuarioPerfilId = (SELECT "
                            . "UsuarioPerfilId FROM UsuarioPerfil WHERE "
                            . "Nombre = '" 
                            . db_sanitizar($db, $usrdata['Perfil']) . "') "
                            . "WHERE UID = '" . $uid . "'", 
                            'Usuario' => "UPDATE Usuario SET Nombre = '" 
                            . db_sanitizar($db, $usrdata['Usuario']) 
                            . "' WHERE UID = '" . $uid . "'",
                            'PasswordSalted' => "UPDATE Usuario SET PasswordSalted = '"
                            . db_sanitizar($db, $usrdata['PasswordSalted']) 
                            . "' WHERE UID = '" . $uid . "'",
                            'Activo' => "UPDATE Usuario SET Activo = '"
                            . db_sanitizar($db, $usrdata['Activo']) 
                            . "' WHERE UID = '" . $uid . "'",
                            'ModificacionTimestamp' => "UPDATE Usuario SET "
                            . "ModificacionTimestamp = '"
                            . db_sanitizar($db, $usrdata['ModificacionTimestamp']) 
                            . "' WHERE UID = '" . $uid . "'");
                        
                        session_set_msg("Datos ingresados correctamente");
                        reset($usrdata);
                        do {
                            $campo = key($usrdata);
                            if (!($db->real_query($querys[$campo]))) {
                                // Error en la query
                                // Mostrar mensaje
                                $db_conn_ok = FALSE;
                                session_unset_msg();
                                break;
                            }
                        } while (next($usrdata));
                    }
                    $db->close();
                }
            }
            else {
                // No hay cambios!
                // Mostrar mensaje
                session_set_msg("No hay cambios para guardar");
            }
        }
        else {
            // Clave incorrecta, pero no cerrar sesion, se pudo haber
            // equivocado...
            // No leer ninguna otra entrada del usuario
            unset($_POST);
            session_set_errt($err_wrongpass);
        }
        
        // Recargar pagina 
        $redirect = LOC_NAV;
        $params = "accion=perfilusr";
        // Limpiar los datos para recargarlos
        session_unset_data();
    }
    else {
        // No toca boton... cargar datos
        $usrdata = session_get_data();
        if(empty($usrdata)) {
            $db_conn_ok = FALSE;    // Se pondra a TRUE si esta todo ok
            $db = new mysqli;
            if ($db) {
                if (db_connect_ro($db)) {
                    // Perfil de usuario
                    // Estado
                    // Fecha de creacion
                    // Fecha de modificacio
                    $queryp = "SELECT UsuarioPerfil.Nombre AS 'Perfil', Usuario.Activo, "
                            . "Usuario.CreacionTimestamp, Usuario.ModificacionTimestamp "
                            . "FROM Usuario INNER JOIN UsuarioPerfil "
                            . "USING (UsuarioPerfilId) WHERE Usuario.Nombre = ?";
                    $bindp = 's';
                    $result = db_query_prepared_transaction($db, $queryp, 
                                                    $bindp, array($usuario));
                    //var_dump($result);
                    if (is_array($result)) {
                        $usrdata = $result;
                        unset($result);
                        $usrdata['Usuario'] = $usuario;

                        // Los demas perfiles
                        $queryp = "SELECT UsuarioPerfil.Nombre FROM UsuarioPerfil "
                                . "WHERE UsuarioPerfil.Nombre NOT IN (SELECT "
                                . "UsuarioPerfil.Nombre FROM UsuarioPerfil "
                                . "INNER JOIN Usuario USING (UsuarioPerfilId) "
                                . "WHERE Usuario.Nombre = ?)";
                        $bindp = 's';
                        $result = db_query_prepared_transaction($db, $queryp, $bindp, array($usuario));
                        //var_dump($result);
                        if(is_array($result)) {
                            $usrdata['Profiles'] = $result;
                            unset($result);
                            // Guardo los datos para comparar 
                            // cuando el usuario acepte cambios
                            // (y no buscar en la DB nuevamente)
                            session_set_data($usrdata);
                            
                            $db_conn_ok = TRUE;
                        }
                    }            
                }
                $db->close();
            }
        }
    }
    // ToDo
    // Manejo de errores...
    //
    if (!$db_conn_ok) {
        session_set_errt($err_dbconn);
        unset($usrdata);
    }
    
    // Guardar el form token para validar
    session_set_frmtkn(form_token_get_formtkn($form_token));
}
else {
    // Error de autenticacion
    //
    session_terminate();
    session_do();
    session_set_errt($err_authfail);
    $redirect = LOC_NAV;  
    $params = 'accion=logout';
}

if (isset($redirect)) {
    page_goto($redirect, $params);
    exit();
}
?>

<?php echo page_get_head('SiMaPe - Mi perfil de usuario'); ?>
<?php echo page_get_body(); ?>
<?php echo page_get_header(); ?>
<?php echo page_get_header_close(); ?>
<?php echo page_get_navbarV(); ?>
<?php echo page_get_main(); ?>
     <form style="text-align: center; margin-top: 200px; margin: 0 auto; width: auto;" 
           method="POST" >
        <fieldset style="text-align: center; width: 30%; margin:0px auto;">
            <table border="0" cellpadding="1" cellspacing="1" 
                   style="width: 500px; margin: auto; text-align: center;">
                  <tbody>
                          <tr>
                                <td style="text-align: right;">Nombre de usuario</td>
                                <td style="text-align: left;"><input name="frm_txtUsuario" 
                                          title="M&iacute;nimo <?php 
                                          echo constant('USRNAME_MINLEN'); 
                                          ?> caracteres y m&aacute;ximo <?php 
                                          echo constant('USRNAME_MAXLEN'); ?>"
                                          pattern=".{<?php 
                                          echo constant('USRNAME_MINLEN') . ',' 
                                                  . constant('USRNAME_MAXLEN'); ?>}"
                                                  type="text" required value="<?php if (isset($usrdata['Usuario'])) { echo $usrdata['Usuario']; } ?>"
                                          placeholder="Nombre de usuario"/>
                                </td>
                          </tr>
                          <tr>
                                <td style="text-align: right;">Contraseña nueva</td>
                                <td style="text-align: left;"><input name="frm_pwdNew" 
                                          title="M&iacute;nimo <?php 
                                          echo constant('PWD_MINLEN'); 
                                          ?> caracteres y m&aacute;ximo <?php 
                                          echo constant('PWD_MAXLEN'); ?>"
                                          pattern=".{<?php 
                                          echo constant('PWD_MINLEN') . ',' 
                                                  . constant('PWD_MAXLEN'); ?>}"
                                          type="password" 
                                          placeholder="(si desea cambiar la actual)"/>
                                </td>
                          </tr>
                          <tr>
                                <td style="text-align: right;">Verificar contraseña nueva</td>
                                <td style="text-align: left;"><input name="frm_pwdNewV" 
                                          title="M&iacute;nimo <?php 
                                          echo constant('PWD_MINLEN'); 
                                          ?> caracteres y m&aacute;ximo <?php 
                                          echo constant('PWD_MAXLEN'); ?>"
                                          pattern=".{<?php 
                                          echo constant('PWD_MINLEN') . ',' 
                                                  . constant('PWD_MAXLEN'); ?>}"
                                          type="password" 
                                          />
                                </td>
                          </tr>
                          <tr>
                                  <td style="text-align: right;">Perfil de usuario
                                  </td>
                                  <td style="text-align: left;">
                                      <select name="frm_selectPerfil">
                                          <option selected="selected" 
                                                  value="<?php if (isset($usrdata['Perfil'])) { echo $usrdata['Perfil']; } ?>">   
                                                <?php if (isset($usrdata['Perfil'])) { echo $usrdata['Perfil']; } ?>
                                          </option>
                                          <?php
                                          if (isset($usrdata['Profiles'])) { 
                                            foreach ($usrdata['Profiles'] as $profile) {
                                                echo "<option value='". $profile . "'>$profile</option>";
                                            }
                                            unset($profile);  // Prolijidad despues de foreach
                                          }
                                          ?>
                                      </select>
                                  </td>
                          </tr>
                          <tr>
                                  <td style="text-align: right;">&iquest;Activo?
                                  </td>
                                  <td style="text-align: left;">
                                      <input name="frm_checkboxActivo" <?php 
                                            if (isset($usrdata['Activo']) 
                                                    && ($usrdata['Activo'] == 1)) { 
                                                echo ' checked '; 
                                            }
                                            ?> 
                                             value="<?php if (isset($usrdata['Activo'])) { echo $usrdata['Activo']; } ?>" 
                                             type="checkbox" />
                                  </td>
                          </tr>
                          <tr>
                                  <td style="text-align: right; font-style: italic;">
                                      Fecha de creaci&oacute;n:
                                  </td>
                                  <td style="text-align: left;">
                                      <?php
                                      if (isset($usrdata['CreacionTimestamp'])) {
                                        echo strftime('%d de %B de %G, %H:%M:%S' , 
                                              $usrdata['CreacionTimestamp']);
                                      }
                                      ?>
                                  </td>
                          </tr>
                          <tr>
                                  <td style="text-align: right; font-style: italic;">
                                      Fecha de modificaci&oacute;n:
                                  </td>
                                  <td style="text-align: left;">
                                      <?php
                                      if (isset($usrdata['ModificacionTimestamp'])) {
                                        echo strftime('%d de %B de %G, %H:%M:%S' , 
                                              $usrdata['ModificacionTimestamp']);
                                      }
                                      ?>
                                  </td>
                          </tr>
                          <tr>
                            <td style="text-align: right;"><br />
                                Contraseña actual</td>
                            <td style="text-align: left;"><br />
                                <input name="frm_pwdLogin" 
                                       title="Ingrese su contraseña actual para autenticarse"
                                       maxlength="<?php echo constant('PWD_MAXLEN'); ?>" 
                                       type="password" 
                                       placeholder="(para aceptar los cambios)"/>
                            </td>
                          </tr>
                          <tr>
                              <td colspan="2" style="text-align: center;">
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
                            <td colspan="2" style="text-align: center;">
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