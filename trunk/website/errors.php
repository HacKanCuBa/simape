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
 * @version 0.30
 */

require_once 'load.php';

Session::initiate();

// 418 no está asignado, por eso lo uso como custom
$status = intval(Sanitizar::glGET(SMP_HTTP_ERROR));

Page::printHead('SiMaPe | Error ' . $status, ['input', 'main', 'msg']);
Page::printBody();
Page::printHeader();
Page::printHeaderClose();
Page::printMain();

switch ($status) {
    case 403:
        $title = "Acceso denegado";
        $desc = [ 
                "No tiene permiso para acceder a la p&aacute;gina "
                . "requerida.",
                "Si lleg&oacute; aqu&iacute; por medio de un enlace, "
                . "<i>es probable que su sesi&oacute;n haya caducado "
                . "por inactividad</i>.  Si considera que &eacute;sto no "
                . "es correcto, contacte con un " 
                . contactar_administrador() . " del sistema."
        ];
        break;
    
    case 404:
        $title = "P&aacute;gina no encontrada";
        $desc = [
                "La p&aacute;gina que est&aacute; "
                . "buscando no se encuentra en esta direcci&oacute;n.",
                "Si lleg&oacute; aqu&iacute; por medio de un enlace, "
                . "contacte a un " . contactar_administrador() 
                . " del sistema."
        ];
        break;
    
    case 500:
        $title = "Error interno del servidor";
        $desc = [
                "El servidor no ha logrado procesar correctamente la "
                . "petici&oacute;n y ha ocurrido un error interno.",
                "Repita la operaci&oacute;n y si el error persiste, "
                . "contacte a un " . contactar_administrador() 
                . " del sistema."
        ];
        break;
    
    case 1337:
        $title = "Intento de hacking detectado";
        $desc = [
                "Se ha detectado un intento de hacking, el cual ha sido "
                . "interceptado y detenido.",
                "Su IP, las acciones realizadas y otros datos han quedado "
                . "registrados a fin de ser analizados por el equipo de "
                . "seguridad, que ya fue notificado del hecho.",
                "Es probable que no pueda volver a usar la aplicaci&oacute;n "
                . "desde esta computadora y/o desde su cuenta "
                . "de usuario momentaneamente."
        ];
        break;

    default:
        $status = 418;
        $title = "Desconocido";
        $desc = [
                "Se ha producido un error desconocido.",
                "Repita la operaci&oacute;n y si el error persiste, "
                . "contacte a un " . contactar_administrador() 
                . " del sistema e ind&iacute;quele los pasos seguidos a fin "
                . "de reproducir el error."
        ];
        break;
}

Page::_e("<h2 style='text-align: center;font-weight: bold'>"
            . "Error " . $status . ": " . $title . "</h2>", 2);

foreach ($desc as $d) {    
    Page::_e("<p style='text-align: center;'>", 2);
    Page::_e($d, 3);
    Page::_e("</p>", 2);
}

Page::_e("<p style='text-align: center;'>", 2);
Page::_e(Page::getInput('button', 
                            '', 
                            'Ir a la p&aacute;gina principal', 
                            '',
                            'btn_green', 
                            '', 
                            '', 
                            "onClick='location.href=\"" 
                                . SMP_WEB_ROOT . "\";'"), 
                        3);

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