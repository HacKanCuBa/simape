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

// ESTA PAGINA NO DEBE SER ACCEDIDA DIRECTAMENTE POR EL USUARIO

/*
 * Esta página muestra los mensajes del usuario
 */

if (!defined('CONFIG')) { require_once 'loadconfig.php'; }

session_do();

// Instanciar un token del formulario
$form_token = form_token_get_new();

// Recuperar datos de login
$sessionkey = session_get_sessionkey();
$usuario = session_get_username();
$form_token_post = post_get_frmtkn();

if (page_token_validate(get_get_pagetkn()) && 
        (form_token_validate(post_get_frmtkn()) !== FALSE) &&
        fingerprint_token_validate() &&
        sessionkey_validate($usuario, $sessionkey)) {
    // Login OK 
    if (!empty(post_get_frmBtn('Enviar')) 
        && !empty(form_token_validate($form_token_post))) {
        // Enviar mensaje
        // ToDo
        // - Poder enviar un mensaje a multiples usuarios al mismo tiempo
        // - Enviar mensajes usando nombre, apellido, dni, etc.... 
        // no solo nombre de usuario
        $receptor = post_get_frmText('Receptor');
        $mensaje = post_get_frmText('Mensaje');
        if (isValid_username($receptor) 
                && isValid_msg($mensaje)) {
            $db_conn_ok = FALSE;
            $db = new mysqli;
            if ($db) {
                if (db_connect_rw($db)) {
                    $query = "INSERT INTO Mensaje "
                            . "(UsuarioId_Emisor, UsuarioId_Receptor, Mensaje, Timestamp) VALUES "
                            . "((SELECT UsuarioId FROM Usuario WHERE Nombre = '" . $usuario 
                            . "'), (SELECT UsuarioId FROM Usuario WHERE Nombre = '" 
                            . db_sanitizar($db, $receptor) . "'), '" 
                            . db_sanitizar($db, $mensaje) . "', "
                            . time() . ")";

                    if ($db->real_query($query)) {
                        session_set_msg('&iexcl;Mensaje enviado exitosamente!');
                        // recargar para mostrar mensaje
                        $redirect = SMP_LOC_MSGS;
                        $params = "#tabR&pagetkn=" . page_token_get_new();
                    }
                    else {
                        session_set_errt('No se pudo enviar el mensaje');
                    }
                    $db_conn_ok = TRUE;
                }
                $db->close();
            }
        }
        else {
            session_set_errt('&iexcl;Usuario o mensaje no cumplen con las especificaciones!');
        }
    }
    
    // Mostar mensajes
    $db_conn_ok = FALSE;
    $db = new mysqli;
    if($db) {
        if(db_connect_ro($db)) {
            $querys = array( 'Recibidos' => "SELECT Emisor.Nombre "
                . "AS 'Emisor', Mensaje.Mensaje, Mensaje.Visto, "
                . "Mensaje.Timestamp FROM Mensaje INNER JOIN Usuario AS "
                . "Emisor ON (Emisor.UsuarioId = Mensaje.UsuarioId_Emisor) "
                . "INNER JOIN Usuario AS Receptor ON "
                . "(Receptor.UsuarioId = Mensaje.UsuarioId_Receptor) WHERE "
                . "Receptor.Nombre = '" . $usuario . "' "
                . "ORDER BY Mensaje.Timestamp",
                             'Enviados' => "SELECT Receptor.Nombre "
                . "AS 'Receptor', Mensaje.Mensaje, Mensaje.Visto, "
                . "Mensaje.Timestamp FROM Mensaje INNER JOIN Usuario AS "
                . "Emisor ON (Emisor.UsuarioId = Mensaje.UsuarioId_Emisor) "
                . "INNER JOIN Usuario AS Receptor ON "
                . "(Receptor.UsuarioId = Mensaje.UsuarioId_Receptor) WHERE "
                . "Emisor.Nombre = '" . $usuario . "' "
                . "ORDER BY Mensaje.Timestamp");

            // TODO
            // - Verificar el flag Visto y mostrar mensaje adecuado
            // (mensaje NUEVO recibido, no hay mensajes NUEVOS, 
            // no hay ningun mensaje, etc....
            // - Errores....
            reset($querys);
            do {
                $query = current($querys);
                if($db->real_query($query)) {
                    $db_conn_ok = TRUE;
                    $result = $db->store_result();
                    while ($mensaje = $result->fetch_assoc()) {
                        //var_dump($mensaje);
                        $mensajes[key($querys)][] = $mensaje;
                        //session_set_msg("&iexcl;Mensajes recibidos!");
                    } 
                }
                $result->close();  
            } while (next($querys));
            // Limpio un poco...
            unset($mensaje, $query, $querys);
            //var_dump($mensajes);
        }
        $db->close();
    }

    if (!$db_conn_ok) {
        session_set_errt(SMP_ERR_DBCONN);
    }
} else {
    // Error de autenticacion
    session_terminate();
    session_do();
    session_set_errt(SMP__ERR_AUTHFAIL);
    $redirect = SMP_LOC_LOGIN;  
    $params = NULL;
}

// Guardar el form token para validar si se usa POST
session_set_frmtkn(form_token_get_formtkn($form_token));

if (isset($redirect)) {
    page_goto($redirect, $params);
    exit();
}
?>

<?php echo page_get_head('SiMaPe - Principal', 'main.css'); ?>
<?php echo page_get_body(); ?>
<?php echo page_get_header(); ?>
<?php echo page_get_header_close(); ?>
<?php echo page_get_navbarV(); ?>
<?php echo page_get_main(); ?>

    <form id="frm_msgs" method="POST" style="height: auto; text-align: center; margin-left: auto; margin-right: auto; margin-top: auto;">
        <div style="text-align: center; margin-left: auto; margin-right: auto; margin-top: auto; height: auto; width:60%">
        <div class="w3cTabs">
            <div id="tabR">
                <a href="#tabR">Mensajes recibidos</a>
                <div>
                    <table border="0" style="width: 100%; margin: auto; text-align: center;">
                        <?php
                        if (!empty($mensajes['Recibidos'])) {
                            echo "<thead style='font-weight:bold;'><tr><td>Fecha</td><td>Enviado por</td><td>Mensaje</td></tr></thead>";
                            echo "<tbody>";
                            foreach ($mensajes['Recibidos'] as $recibido) {
                                echo "<tr><td>" . strftime('%d de %B de %G, %H:%M:%S', $recibido['Timestamp'])
                                        . "</td><td>" . $recibido['Emisor'] 
                                        . "</td><td>" . $recibido['Mensaje'] 
                                        . "</td></tr>";
                            }
                            echo "</tbody>";
                        }
                        else {
                            echo "<tr><td colspan='3'>No se recibieron mensajes</td></tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
            <div id="tabE">
                <a href="#tabE">Mensajes enviados</a>
                <div>
                    <table border="0" style="width: 100%; margin: auto; text-align: center;">
                        <?php
                        if (!empty($mensajes['Enviados'])) {
                            echo "<thead style='font-weight:bold;'><tr><td>Fecha</td><td>Enviado a</td><td>Mensaje</td></tr></thead>";
                            echo "<tbody>";
                            foreach ($mensajes['Enviados'] as $enviado) {
                                echo "<tr><td>" . strftime('%d de %B de %G, %H:%M:%S', $enviado['Timestamp']) 
                                        . "</td><td>" . $enviado['Receptor'] 
                                        . "</td><td>" . $enviado['Mensaje'] 
                                        . "</td></tr>";
                            }
                            echo "</tbody>";
                        }
                        else {
                            echo "<tr><td colspan='3'>No se enviaron mensajes</td></tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
        </div>
        <div style="text-align: center; position: relative; margin-top: auto; top: auto; height: auto; width: 60%; margin-left: auto; margin-right: auto;">
             <table border="0" style="position: relative; width: 100%; text-align: center;">
                 <tr><td>Enviar un mensaje nuevo a </td><td><input 
                                           name="frm_txtReceptor" 
                                           placeholder="Usuario receptor"
                                           pattern=".{<?php 
                                           echo constant('SMP_USRNAME_MINLEN') . ',' 
                                                   . constant('SMP_USRNAME_MAXLEN'); ?>}"
                                                   type="text" /></td>
                 </tr>
                 <tr><td colspan="2">
                 <textarea name="frm_txtMensaje" placeholder="Escribir nuevo mensaje"
                           style="overflow-y:scroll; width:100%; resize: none;"
                           maxlength="<?php echo constant('SMP_MGS_MAXLEN'); ?>"
                           wrap="soft" form="frm_msgs" 
                           title="<?php echo "M&aacute;ximo " 
                                             . constant('SMP_MGS_MAXLEN') 
                                             . " caracteres"; 
                                  ?>"></textarea>
                 </td></tr>
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
                 <tr><td colspan="2"><input type="submit" name="frm_btnEnviar" 
                                            value="&iexcl;Enviar mensaje!" />
                     </td>
                 </tr>
             </table>
         </div> 
      <input type="hidden" name="form_token" 
             value="<?php echo form_token_get_randtkn($form_token) ?>" />
    </form>
<?php echo page_get_main_close(); ?>
<?php echo page_get_footer(); ?>
