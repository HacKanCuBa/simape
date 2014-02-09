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
echo "\n\t\t\t</ul>";
                        
echo Page::getMainClose();
echo Page::getFooter();