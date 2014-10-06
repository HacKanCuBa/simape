<?php

/*****************************************************************************
 *  Este archivo forma parte de SiMaPe
 *  Sistema Integrado de Manejo de Personal
 *  Copyright (C) <2013, 2014>  <Ivan Ariel Barrera Oro>
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
 * Página de inicio para el usuario loggeado.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.1
 */

require_once '../../load.php';

if(!empty(Sanitizar::glPOST('frm_button'))) {
    Page::nav(SMP_WEB_ROOT);
    exit();
}

// -- PAGE
Page::_e(Page::getHead('SiMaPe - Certificado'));
Page::_e(Page::getBody());
Page::_e(Page::getHeader());
Page::_e(Page::getHeaderClose());
Page::_e(Page::getMain());

Page::_e('<h1>Certificado de SiMaPe</h1>', 2);
Page::_e('Para instalar el certificado en su equipo, siga los siguientes pasos:', 2);
Page::_e('<ul>', 2);
Page::_e('<li>Para Firefox:</li>', 3);
Page::_e('<ol>', 3);
Page::_e('<li><a href="simape-ca.crt">Descargar el certificado</a></li>', 4);
Page::_e('<li>De la ventana que aparece, tildar las primeras 2 casillas y luego click en <i>Aceptar</i>.</li>', 4);
Page::_e('</ol>', 3);
Page::_e('<li>Para otros navegadores:</li>', 3);
Page::_e('<ol>', 3);
Page::_e('<li><a href="simape-ca.crt">Descargar el certificado</a></li>', 4);
Page::_e('<li>Ir a la carpeta donde se lo descargo, click derecho sobre el archivo y seleccionar <i>Instalar</i>.</li>', 4);
Page::_e('<li>De la ventana que aparece, click en el bot&oacute;n <i>Buscar</i>.</li>', 4);
Page::_e('<li>De la lista, seleccionar <i>Entidad de confianza de certificados ra&iacute;</i>z y luego click en <i>Aceptar</i>.</li>', 4);
Page::_e('<li><i>Siguiente</i> y <i>Finalizar</i>.  Aparecerá un cartel solicitando confirmaci&oacute;n, click en <i>S&iacute;</i>.</li>', 4);
Page::_e('</ol>', 3);
Page::_e('</ul>', 2);

Page::_e('<br />', 2);

Page::_e("<form style='text-align: center;' method='post'>", 2);
Page::_e("<p><input name='frm_button' type='submit' "
     . "style='font-style:italic;' "
     . "value='Volver a la p&aacute;gina de inicio' /></p>", 3);
Page::_e("</form>", 2);

Page::_e(Page::getMainClose());
Page::_e(Page::getFooter());
Page::_e(Page::getBodyClose());