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
 * errors.php
 * Esta página maneja los códigos de estado HTTP.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.21
 */

require_once 'load.php';

Session::initiate();

// 418 no está asignado, por eso lo uso como custom
$status = intval(Sanitizar::glGET(SMP_HTTP_ERROR)) ?: 418;

Page::printHead('SiMaPe | Error ' . $status, ['input', 'main', 'msg']);
Page::printBody();
Page::printHeader();
Page::printHeaderClose();
Page::printMain();

switch ($status) {
    case 403:
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
                    . "Si considera que &eacute;sto no es correcto, contacte "
                    . "con un " . contactar_administrador() . " del sistema."
                    . "</p>", 2);
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
        break;
    
    case 404:
        Page::_e("<h2 style='text-align: center;font-weight: bold'>Error 404: "
                    . "P&aacute;gina no encontrada</h2>", 2);
        Page::_e("<br />", 2);
        Page::_e("<p style='text-align: center;'>"
                . "La p&aacute;gina que est&aacute; "
                . "buscando no se encuentra en esta direcci&oacute;n.</p>", 2);
        Page::_e("<p style='text-align: center;'>"
                . "Si lleg&oacute; aqu&iacute; por medio de un enlace, "
                . "contacte a un " . contactar_administrador() 
                . " del sistema.</p>", 2);

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
        Page::_e('</p>', 2);
        break;
    
    case 500:
        Page::_e("<h2 style='text-align: center;font-weight: bold'>Error 500: "
                    . "Error interno del servidor</h2>", 2);
        Page::_e("<br />", 2);
        Page::_e("<p style='text-align: center;'>"
                . "El servidor no ha logrado procesar correctamente la "
                . "petici&oacute;n y ha ocurrido un error interno.</p>", 2);
        Page::_e("<p style='text-align: center;'>"
                . "Repita la operaci&oacute;n y si el error persiste, "
                . "contacte a un " . contactar_administrador() 
                . " del sistema.</p>", 2);

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
        Page::_e('</p>', 2);
        break;

    default:
        Page::_e("<h2 style='text-align: center;font-weight: bold'>Error " 
                    . $status . "</h2>", 2);
        Page::_e("<br />", 2);
        Page::_e("<p style='text-align: center;'>Se ha producido un error "
                . "desconocido.</p>", 2);
        Page::_e("<p style='text-align: center;'>"
                . "Repita la operaci&oacute;n y si el error persiste, "
                . "contacte a un " . contactar_administrador() 
                . " del sistema.</p>", 2);

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
        Page::_e('</p>', 2);
        break;
}

Page::printMainClose();
Page::printFooter();
Page::printBodyClose();