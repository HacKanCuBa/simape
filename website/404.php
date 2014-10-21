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

Page::printHead('SiMaPe - 404 No encontrado', ['input', 'main']);
Page::printBody();
Page::printHeader();
Page::printHeaderClose();
Page::printMain();

Page::_e("<h2 style='text-align: center;font-weight: bold'>Error 404: "
            . "P&aacute;gina no encontrada</h2>", 2);
Page::_e("<br />", 2);
Page::_e("<p style='text-align: center;'>"
        . "La p&aacute;gina que est&aacute; "
        . "buscando no se encuentra en esta direcci&oacute;n.</p>", 2);
Page::_e("<p style='text-align: center;'>"
        . "Si lleg&oacute; aqu&iacute; por medio de un enlace, "
        . "contacte con un administrador del sistema.</p>", 2);

Page::_e("<p style='text-align: center;'>", 2);
Page::_e(Page::getInput('button', 
                                '', 
                                'Ir a la p&aacute;gina principal', 
                                '',
                                'btn_blue', 
                                '', 
                                '', 
                                "onClick='location.href=\"" 
                                    . SMP_WEB_ROOT . "\";'"), 
                            3);

Page::printMainClose();
Page::printFooter();
Page::printBodyClose();