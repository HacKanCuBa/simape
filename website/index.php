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
 * index.php
 * Página de inicio y presentación
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.5
 */

require_once 'load.php';

Session::terminate();

if(!empty(Sanitizar::glPOST('frm_btnLogin'))) {
    Page::nav(SMP_LOGIN);
    exit();
} elseif(!empty(Sanitizar::glPOST('frm_btnCert'))) {
    $p = new Page(SMP_LOC_MEDIA . 'cert/index.php');
    $p->go();
    exit();
}

echo Page::getHead('SiMaPe');
echo Page::getBody();
echo Page::getHeader();
echo Page::getHeaderClose();
echo Page::getMain();
echo "\n\t\t<form style='text-align: center;' method='post'>";
echo "\n\t\t\t<p><input name='frm_btnLogin' type='submit' "
     . "style='font-style:italic;' "
     . "value='Ingresar al sistema - Iniciar sesi&oacute;n' /></p>";
echo "\n\t\t\t<p><input name='frm_btnCert' type='submit' "
     . "style='font-style:italic;' "
     . "value='Instalar certificado' /></p>";
echo "\n\t\t</form>";
echo "\n\t\t<h3>Acerca de SiMaPe</h3>";
echo "\n\t\t\t<p>Este sistema se encuentra siendo desarrollado en exclusivo " 
     . "para el uso interno de la oficina de Recursos Humanos del Cuerpo "
     . "M&eacute;dico Forense, con miras a expandirse a todo el Cuerpo en "
     . "el mediano plazo.</p>";
echo "\n\t\t\t<p>Es importante destacar que el mismo a&uacute;n no est&aacute; " 
     . "completo, por lo que pueden faltar caracter&iacute;sticas y "
     . "sobrar errores inesperados.</p>";
echo "\n\t\t\t<p>El proyecto SiMaPe abarcar&aacute;, entre otras, las "
     . "siguientes caracter&iacute;sticas:</p>";
echo "\n\t\t\t<ul>";
echo "\n\t\t\t\t<li>Ser seguro: la aplicación se diseña teniendo en cuenta la "
     . "seguridad de los datos</li>";
echo "\n\t\t\t\t<li>Legajos digitales: toda la información pertinente a todos "
     . "los empleados (datos, calificaciones, licencias, horarios, etc.)</li>";
echo "\n\t\t\t\t<li>Manejo de asistencias, licencias, etc.: muestra toda la "
     . "información referida a una persona o a una fecha particular, permite "
     . "cargar licencias ordinarias y extraordinarias, y toda tarea "
     . "relacionada</li>";
echo "\n\t\t\t\t<li>Manejo de reemplazos: designar empleados administrativos a "
     . "oficinas, y designar reemplazos en caso de inasistencias</li>";
echo "\n\t\t\t\t<li>Manejo y creación de guardias: permite diseñar guardias de "
     . "médicos (psiquiatría, generalistas, psicología, C. Gesell, etc.), "
     . "enviar para ser autorizada por Coordinador y finalmente a la oficina de "
     . "RRHH, con distribución instantánea en todo el Cuerpo</li>";
echo "\n\t\t\t\t<li>Mensajería interna: sistema de mensajes cortos, y envío de "
     . "notas y circulares</li>";
echo "\n\t\t\t\t<li>Multiusuario: todos los empleados pueden tener un usuario, "
     . "que les permite ver p.e. su planilla de horas, solicitar días de "
     . "licencia ordinaria o extraordinaria (con firma digital), etc.  No "
     . "todos los empleados tienen el mismo tipo de acceso</li>";
echo "\n\t\t\t\t<li>Acceso restringido por permisos: cada usuario tendrá "
     . "acceso limitado a la información que le corresponda</li>";
echo "\n\t\t\t\t<li>Acceso únicamente en la red CSJN: solo local, no hay "
     . "acceso remoto fuera de la red CSJN</li>";
echo "\n\t\t\t\t<li>Registro de acciones: toda acción realizada queda "
     . "registrada en el sistema</li>";
echo "\n\t\t\t\t<li>Manejo de biblioteca: permite administrar completamente una "
     . "biblioteca</li>";
echo "\n\t\t\t\t<li>Multiplataforma: desarrollado como aplicación web, "
     . "funciona en todas las plataformas que dispongan de un navegador web "
     . "(Windows, Linux, Mac OS, iOS, Android, FirefoxOS, etc)</li>";
echo "\n\t\t\t\t<li>Licencia de software libre: usando una licencia GNU GPL "
     . "v3, permite el desarrollo continuado, colaborativo y sin costos</li>";
echo "\n\t\t\t\t<li>Fácil manejo: se diseña tal que no requiera conocimientos "
     . "previos específicos, es decir, que la curva de aprendizaje sea suave</li>";
echo "\n\t\t\t\t<li>Dise&ntilde;o a medida, escalable</li>";
echo "\n\t\t\t</ul>";
                        
echo Page::getMainClose();
echo Page::getFooter();
echo Page::getBodyClose();