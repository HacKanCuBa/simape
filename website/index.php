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

require_once 'load.php';

Session::terminate();
Session::initiate();

$fingerprint = new Fingerprint();
Session::store(SMP_FINGERPRINT_RANDOMTOKEN, $fingerprint->getRandomToken());
Session::store(SMP_FINGERPRINT_TOKEN, $fingerprint->getToken());

if(!empty(Sanitizar::glPOST('frm_buttonLogin')))
{
    Page::nav(SMP_LOC_LOGIN);
    exit();
}

echo Page::getHead('SiMaPe');
echo Page::getBody();
echo Page::getHeader();
echo Page::getHeaderClose();
echo Page::getMain();

echo "\n\t\t<form style='text-align: center;' method='post'>";
echo "\n\t\t\t<h3>Ingresar al sistema</h3>";
echo "\n\t\t\t<p>Para ingresar al sistema, debe <input name='frm_buttonLogin' "
     . "type='submit' value='Iniciar sesi&oacute;n' /></p>";
echo "\n\t\t</form>";
echo "\n\t\t<h3>Acerca de SiMaPe</h3>";
echo "\n\t\t\t<p>Este sistema se encuentra siendo desarrollado en exclusivo " 
     . "para el uso interno de la oficina de Recursos Humanos del Cuerpo "
     . "M&eacute;dico Forense, con miras a expandirse a todo el Cuerpo en "
     . "el mediano plazo.</p>";
echo "\n\t\t\t<p>Es importante destacar que el mismo a&uacute;n no est&aacute; " 
     . "completo, por lo que pueden faltar caracter&iacute;siticas y "
     . "sobrar errores inesperados.</p>";
echo "\n\t\t\t<p>El proyecto SiMaPe abarcar&aacute;, entre otras, las "
     . "siguientes caracter&iacute;sticas:</p>";
echo "\n\t\t\t<ul>";
echo "\n\t\t\t\t<li>Legajos del personal, digitales (con foto incluida)</li>";
echo "\n\t\t\t\t<li>Mensajer&iacute;a interna</li>";
echo "\n\t\t\t\t<li>Control y manejo de asistencias/inasistencias</li>";
echo "\n\t\t\t\t<li>Visualizar fichaje por parte del personal</li>";
echo "\n\t\t\t\t<li>Solicitar licencias extraordinarias sin necesidad de "
     . "papel (se implementar&aacute;n firmas digitales)</li>";
echo "\n\t\t\t\t<li>Control de usuarios, manejo de permisos</li>";
echo "\n\t\t\t\t<li>Dise&ntilde;o a medida, escalable.</li>";
//Ser seguro: la aplicación se diseña teniendo en cuenta la seguridad de los datos.
//Legajos digitales: toda la información pertinente a todos los empleados (datos, calificaciones, licencias, horarios, etc.).
//Manejo de asistencias, licencias, etc.: muestra toda la información referida a una persona o a una fecha particular, permite cargar licencias ordinarias y extraordinarias, y toda tarea relacionada.
//Manejo de reemplazos: designar empleados administrativos a oficinas, y designar reemplazos en caso de inasistencias.
//Manejo y creación de guardias: permite diseñar guardias de médicos (psiquiatría, generalistas, psicología, C. Gesell, etc.), enviar para ser autorizada por Coordinador y finalmente a la oficina de RRHH, con distribución instantánea en todo el Cuerpo.
//Mensajería interna: sistema de mensajes cortos, y envío de notas y circulares.
//Multiusuario: todos los empleados pueden tener un usuario, que les permite ver p.e. su planilla de horas, solicitar días de licencia ordinaria o extraordinaria (con firma digital), etc.  No todos los empleados tienen el mismo tipo de acceso.
//Acceso restringido por permisos: cada usuario tendrá acceso limitado a la información que le corresponda.
//Acceso únicamente en la red CSJN: solo local, no hay acceso remoto fuera de la red CSJN.
//Registro de acciones: toda acción realizada queda registrada en el sistema.
//Multiplataforma: desarrollado como aplicación web, funciona en todas las plataformas que dispongan de un navegador web (Windows, Linux, Mac OS, iOS, Android, FirefoxOS, etc).
//Licencia de software libre: usando una licencia GNU GPL v3, permite el desarrollo continuado, colaborativo y sin costos.
//Fácil manejo: se diseña tal que no requiera conocimientos previos específicos, es decir, que la curva de aprendizaje sea suave.
echo "\n\t\t\t</ul>";
                        
echo Page::getMainClose();
echo Page::getFooter();