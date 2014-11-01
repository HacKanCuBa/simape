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
 * Página de instalación de certificado.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.14
 */

require_once 'load.php';
Page::forceConnect(Page::FORCE_CONNECT_PLAIN);

// -- PAGE
Page::printHead('SiMaPe | Certificado', ['main', 'input']);
Page::printBody();
Page::printHeader();
Page::printHeaderClose();
Page::printMain();

Page::_e('<h2>Certificado de SiMaPe</h2>', 2);
Page::_e('Para instalar el certificado en su equipo, siga los siguientes pasos:', 2);
Page::_e('<p><em>Para Firefox:</em></p>', 3);
Page::_e('<ol>', 3);
Page::_e('<li><a href="simape-ca.crt">Descargar el certificado</a></li>', 4);
Page::_e('<li>De la ventana que aparece, tildar las primeras 2 casillas y luego click en <i>Aceptar</i>.</li>', 4);
Page::_e('</ol>', 3);
Page::_e('<p><em>Para otros navegadores:</em></p>', 3);
Page::_e('<ol>', 3);
Page::_e('<li><a href="simape-ca.crt">Descargar el certificado</a></li>', 4);
Page::_e('<li>Ir a la carpeta donde se lo descargo, click derecho sobre el archivo y seleccionar <i>Instalar</i>.</li>', 4);
Page::_e('<li>De la ventana que aparece, click en el bot&oacute;n <i>Buscar</i>.</li>', 4);
Page::_e('<li>De la lista, seleccionar <i>Entidad de confianza de certificados ra&iacute;</i>z y luego click en <i>Aceptar</i>.</li>', 4);
Page::_e('<li><i>Siguiente</i> y <i>Finalizar</i>.  Aparecerá un cartel solicitando confirmaci&oacute;n, click en <i>S&iacute;</i>.</li>', 4);
Page::_e('</ol>', 3);

Page::_e('<p>Los detalles del certificado son:</p>', 2);
Page::_e('<ul>', 2);
Page::_e('<li><b>Nombre:</b> SiMaPe CA</li>', 3);
Page::_e('<li><b>Organizaci&oacute;n:</b> SiMaPe</li>', 3);
Page::_e('<li><b>Unidad Organizacional:</b> Certificate Authority</li>', 3);
Page::_e('<li><b>Localidad:</b> CABA</li>', 3);
Page::_e('<li><b>Provincia:</b> Buenos Aires</li>', 3);
Page::_e('<li><b>Pa&iacute;s:</b> AR</li>', 3);
Page::_e('<li><b>V&aacute;lido desde:</b> October 2, 2014</li>', 3);
Page::_e('<li><b>V&aacute;lido hasta:</b> September 29, 2024</li>', 3);
Page::_e('<li><b>Emisor:</b> SiMaPe CA, SiMaPe</li>', 3);
Page::_e('<li><b>Algoritmo de llave:</b> RSA</li>', 3);
Page::_e('<li><b>Tama&ntilde;o de llave:</b> 4096 bit</li>', 3);
Page::_e('<li><b>N&uacute;mero de serie:</b> 00 DE CC 44 86 70 51 08 69</li>', 3);
Page::_e('<li><b>Huella SHA1:</b> A8 C0 EE 29 C6 CA E2 D5 AB 4C D0 40 EE A3 C5 74 0A 94 3F 7D</li>', 3);
Page::_e('</ul>', 2);

Page::_e("<p style='text-align:center;'>" 
            . Page::getInput('button', 
                                NULL, 
                                'Volver a la p&aacute;gina de inicio', 
                                NULL, 
                                'btn_blue', 
                                NULL, 
                                NULL, 
                                "onClick='location.href=\"" . SMP_WEB_ROOT 
                                    . "index.php\";'") 
            . "</p>", 2);

Page::printMainClose();
Page::printFooter();
Page::printBodyClose();