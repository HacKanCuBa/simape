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

/*
 * Este index.php debe estar siempre en la raiz del sitio
 */

$_SESSION['req_o'] = ['class_sanitizar', 'funciones'];
require_once 'load.php';

// Iniciar o continuar sesion
session_do();


if(!empty(Sanitizar::glPOST('do_login')))
{
    page_goto(__SMP_LOC_LOGIN);
    exit();
}
?>

<?php echo page_get_head('SiMaPe'); ?>
<?php echo "\n\t<style type='text/css'>\n\t.data { margin-left: auto; }"
            . "\n\t</style>"; ?>
<?php echo page_get_body(); ?>
<?php echo page_get_header(); ?>
<?php echo page_get_header_close(); ?>
<?php echo page_get_main(); ?>

        <form style="text-align: center;" method="post">
            <h3>Ingresar al sistema</h3>
            <p>Para ingresar al sistema, debe 
                <input name="do_login" type="submit"
                       value="Iniciar sesi&oacute;n" />
            </p>
        </form>
        <h3>Acerca de SiMaPe</h3>
        <p>Este sistema se encuentra siendo desarrollado en exclusivo para el 
            uso interno de la oficina de Recursos Humanos del Cuerpo 
            M&eacute;dico Forense, con miras a expandirse a todo el Cuerpo en 
            el mediano plazo.</p>
        <p>Es importante destacar que el mismo a&uacute;n no est&aacute; 
            completo, por lo que pueden faltar caracter&iacute;siticas y 
            sobrar errores inesperados.</p>
        <p>El proyecto SiMaPe abarcar&aacute;, entre otras, las siguientes 
            caracter&iacute;sticas:</p>
        <ul>
                <li>Legajos del personal, digitales (con foto incluida)</li>
                <li>Mensajer&iacute;a interna</li>
                <li>Control y manejo de asistencias/inasistencias</li>
                <li>Visualizar fichaje por parte del personal</li>
                <li>Solicitar licencias extraordinarias sin necesidad de 
                    papel (se implementar&aacute;n firmas digitales)</li>
                <li>Control de usuarios, manejo de permisos</li>
                <li>Dise&ntilde;o a medida, escalable.</li>
        </ul>
<?php echo page_get_main_close(); ?>
<?php echo page_get_footer(); ?>

