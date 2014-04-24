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

include 'load.php';

echo Page::getHead('SiMaPe - 404 No encontrado');
echo Page::getBody();
echo Page::getHeader();
echo Page::getHeaderClose();
echo Page::getMain();

echo "\n\t\t<h2 style='text-align: center;font-weight: bold'>Error 404: P&aacute;gina no encontrada</h2>";
echo "\n\t\t<br /><br />";
echo "\n\t\t<p style='text-align: center;font-style: italic'>La p&aacute;gina que est&aacute; "
     . "buscando no se encuentra en esta direcci&oacute;n.</p>";
echo "\n\t\t<p style='text-align: center; font-style: italic'>Si lleg&oacute; aqu&iacute; "
     . "por medio de un enlace, contacte con un administrador del sistema.</p>";

echo Page::getMainClose();
echo Page::getFooter();
