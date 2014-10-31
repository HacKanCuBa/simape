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

Session::initiate();

Page::printHead('SiMaPe - Error 403', ['main','input', 'msg']);
Page::printBody();
Page::printHeader();
Page::printHeaderClose();
Page::printMain();

Page::_e("<h2 style='text-align: center;font-weight: bold'>"
            . "Error 403: Acceso denegado</h2>", 2) ;
if (!empty(Session::retrieve(SMP_SESSINDEX_NOTIF_ERR))) {
    Page::_e("<p class='fadeout' "
                . "style='color:red; text-align: center;' >" 
                . Session::retrieve(SMP_SESSINDEX_NOTIF_ERR) 
                . "</p>", 2);
    Session::remove(SMP_SESSINDEX_NOTIF_ERR);
} else {
    Page::_e("<br />", 2);
}
Page::_e("<p style='text-align: center;'>No tiene permiso "
            . "para acceder a la p&aacute;gina requerida.</p>", 2);
Page::_e("<p style='text-align: center;'>Si lleg&oacute; "
            . "aqu&iacute; por medio de un enlace, <i>es probable que su "
            . "sesi&oacute;n haya caducado por inactividad</i>.  "
            . "Si considera que se trata de un error, contacte con un "
            . "administrador del sistema.</p>", 2);
Page::_e("<p style='text-align: center;'>", 2);
Page::_e(Page::getInput('button', 
                                '', 
                                'Ingresar al sistema - Iniciar sesi&oacute;n', 
                                '',
                                'btn_blue', 
                                '', 
                                '', 
                                "onClick='location.href=\"" 
                                    . SMP_WEB_ROOT . "login.php\";'"), 
                            3);
Page::_e('</p>', 2);

Page::printMainClose();
Page::printFooter();
Page::printBodyClose();