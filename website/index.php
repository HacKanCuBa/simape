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
 * @version 0.53
 */

require_once 'load.php';

Session::terminate();

// Forzar siempre sin SSL
Page::force_connect(Page::FORCE_CONNECT_PLAIN);

Page::printHead('SiMaPe', ['main' , 'input']);
Page::printBody();
Page::printHeader();
Page::printHeaderClose();
Page::printMain();

Page::_e("<p style='text-align: center;'>", 2);
Page::_e(Page::getInput('button', 
                                NULL, 
                                'Ingresar al sistema - Iniciar sesi&oacute;n', 
                                NULL,
                                'btn_blue', 
                                NULL, 
                                NULL, 
                                "onClick='location.href=\"" 
                                    . SMP_WEB_ROOT . "login.php\";'"), 
                            3);
Page::_e('</p>', 2);
Page::_e("<h2>Acerca de SiMaPe</h2>", 2);
Page::_e("<p>Este sistema se encuentra siendo desarrollado en exclusivo " 
     . "para el uso interno de la oficina de Recursos Humanos del Cuerpo "
     . "M&eacute;dico Forense, con miras a expandirse a todo el Cuerpo en "
     . "el mediano plazo.</p>", 2);
Page::_e("<p style='text-align:center;'>" . 
            Page::getInput('button', 
                            NULL, 
                            'Presentaci&oacute;n del proyecto', 
                            NULL, 
                            'btn_green', 
                            NULL, 
                            NULL, 
                            "onClick='location.href=\"" 
                                    . SMP_WEB_ROOT . "presentacion.php\";'") .
        "</p>", 2);
Page::_e("<p>Es importante destacar que el mismo a&uacute;n no est&aacute; " 
     . "completo, por lo que pueden faltar caracter&iacute;sticas y "
     . "sobrar errores inesperados.</p>", 2);
Page::_e("<p>El proyecto SiMaPe abarcar&aacute;, entre otras, las "
     . "siguientes caracter&iacute;sticas:</p>", 2);
Page::_e("<ul>", 2);
Page::_e("<li>Ser seguro: la aplicación se diseña teniendo en cuenta la "
     . "seguridad de los datos.</li>", 3);
Page::_e("<li>Legajos digitales: toda la información pertinente a todos "
     . "los empleados (datos, calificaciones, licencias, horarios, etc.).</li>", 3);
Page::_e("<li>Manejo de asistencias, licencias, etc.: muestra toda la "
     . "información referida a una persona o a una fecha particular, permite "
     . "cargar licencias ordinarias y extraordinarias, y toda tarea "
     . "relacionada.</li>", 3);
Page::_e("<li>Manejo de reemplazos: designar empleados administrativos a "
     . "oficinas, y designar reemplazos en caso de inasistencias.</li>", 3);
Page::_e("<li>Manejo y creación de guardias: permite diseñar guardias de "
     . "médicos (psiquiatría, generalistas, psicología, C. Gesell, etc.), "
     . "enviar para ser autorizada por Coordinador y finalmente a la oficina de "
     . "RRHH, con distribución instantánea en todo el Cuerpo.</li>", 3);
Page::_e("<li>Mensajería interna: sistema de mensajes cortos, y envío de "
     . "notas y circulares.</li>", 3);
Page::_e("<li>Multiusuario: todos los empleados pueden tener un usuario, "
     . "que les permite ver p.e. su planilla de horas, solicitar días de "
     . "licencia ordinaria o extraordinaria (con firma digital), etc.  No "
     . "todos los empleados tienen el mismo tipo de acceso.</li>", 3);
Page::_e("<li>Acceso restringido por permisos: cada usuario tendrá "
     . "acceso limitado a la información que le corresponda.</li>", 3);
Page::_e("<li>Acceso únicamente en la red CSJN: solo local, no hay "
     . "acceso remoto fuera de la red CSJN.</li>", 3);
Page::_e("<li>Registro de acciones: toda acción realizada queda "
     . "registrada en el sistema.</li>", 3);
Page::_e("<li>Manejo de biblioteca: permite administrar completamente una "
     . "biblioteca.</li>", 3);
Page::_e("<li>Multiplataforma: desarrollado como aplicación web, "
     . "funciona en todas las plataformas que dispongan de un navegador web "
     . "(Windows, Linux, Mac OS, iOS, Android, FirefoxOS, etc).</li>", 3);
Page::_e("<li>Licencia de software libre: usando una licencia "
     . "<a target='_blank' "
     . "href='http://www.spanish-translator-services.com/espanol/t/gnu/gpl-ar.html'>"
     . "GNU GPL v3</a>, permite el desarrollo continuado, colaborativo "
     . "y sin costos adicionales.</li>", 3);
Page::_e("<li>C&oacute;digo abierto: todo el c&oacute;digo fuente se "
     . "encuentra disponible a fin de ser observado, supervisado, auditado, "
     . "copiado, modificado, redistribuido, etc., <i>bajo los t&eacute;rminos "
     . "de la licencia GNU GPL v3</i>, en el "
     . "<a target='_blank' href='https://code.google.com/p/simape/'>"
     . "directorio de Google Code</a>.</li>", 3);
Page::_e("<li>La <a target='_blank' href='http://man.simape.cf'>"
        . "documentaci&oacute;n del c&oacute;digo</a> se encuentra disponible online.</li>", 3);
Page::_e("<li>Fácil manejo: se diseña tal que no requiera conocimientos "
     . "previos específicos, es decir, que la curva de aprendizaje sea suave.</li>", 3);
Page::_e("<li>Dise&ntilde;o a medida, escalable.</li>", 3);
Page::_e("</ul>", 2);
Page::_e("<br />", 2);
Page::_e("<h3>Detalle respecto del paradigma de la seguridad</h3>", 2);
Page::_e("<p>La primer premisa, como se ha indicado, es que la aplicación "
     . "maneje los datos de manera responsable y segura.  Para ello, "
     . "se emplean distintas "
     . "<a target='_blank' href='https://code.google.com/p/simape/wiki/ModeloSeguridad'>"
     . "t&eacute;cnicas y modelos de seguridad</a>, pero resulta infaltable a "
     . "este fin trabajar bajo una conexi&oacute;n segura.  Esto se logra "
     . "empleando <a "
     . "href='http://revista.seguridad.unam.mx/numero-10/el-cifrado-web-ssltls' "
     . "target='_blank'>SSL/TLS</a> (el <i>candadito</i> que aparece en la "
     . "barra de navegaci&oacute;n, arriba a la izquierda), mecanismo "
     . "autom&aacute;tico "
     . "manejado entre el navegador del usuario y el servidor.  Sin embargo, "
     . "se requiere en este caso una &uacute;nica intervenci&oacute;n del "
     . "usuario la primera vez, y "
     . "&eacute;sto para instalar en el navegador el certificado de "
     . "<i>Autoridad de Certificaci&oacute;n (CA)</i>.</p>", 2);
Page::_e("<p>", 2);
Page::_e(Page::getInput('button', 
                                NULL, 
                                'Instalar certificado', 
                                NULL,
                                'btn_blue', 
                                NULL, 
                                NULL, 
                                "onClick='location.href=\"" . SMP_WEB_ROOT 
                                    . SMP_LOC_MEDIA . "cert/index.php\";'"), 
                            3);
Page::_e('</p>', 2);
                        
Page::printMainClose();
Page::printFooter();
Page::printBodyClose();