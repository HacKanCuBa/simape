<?php

/*****************************************************************************
 *  SiMaPe
 *  Sistema Integrado de Manejo de Personal
 *  Copyright (C) <2013>  <Ivan Ariel Barrera Oro>
 *  ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *****************************************************************************/

/**
 * Página para ver el fichaje de un agente (empleando saper).
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.89
 */

require_once 'load.php';

$session = new Session;

/**
 * Constantes que manejan lo que será mostrado al usuario.
 */
const SAPER_DISPLAY_SEARCH = 0;
const SAPER_DISPLAY_CALC = 1;
const SAPER_DISPLAY_SELECT = 2;
const SAPER_DISPLAY_NORESULT = 3;
const SAPER_DISPLAY_CALC_RESULT = 4;

const SAPER_SESSINDEX_FICHA = 'saper_ficha';
const SAPER_SESSINDEX_HINI = 'hora_inicio';

/**
 * Indica qué se mostrará al usuario en la página.
 * @var string
 */
$display = SAPER_DISPLAY_SEARCH;

/*--*/

$session->useSystemPassword();
$db = new DB(SMP_DB_CHARSET);
$fingp = new Fingerprint();

$usuario = new Usuario($db, $session->retrieveEnc(SMP_SESSINDEX_USERNAME));
$usuario->setFingerprint($fingp);
$usuario->setSession($session);

$page = new Page(SMP_LOC_USR . 'saper.php', 
                 Session::retrieve(SMP_SESSINDEX_PAGE_RANDOMTOKEN), 
                 Session::retrieve(SMP_SESSINDEX_PAGE_TIMESTAMP), 
                 Sanitizar::glGET(SMP_SESSINDEX_PAGE_TOKEN));

if ($page->authenticateToken() 
        && $usuario->sesionAutenticar()
) {
    $formToken = new FormToken;
    $formToken->prepare_to_auth(
                        Sanitizar::glPOST(SMP_SESSINDEX_FORM_TOKEN), 
                        Session::retrieve(SMP_SESSINDEX_FORM_RANDOMTOKEN), 
                        Session::retrieve(SMP_SESSINDEX_FORM_TIMESTAMP)
    );
    
    if ($formToken->authenticateToken()) {
        // Procesar POST
        if (!empty(Sanitizar::glPOST('frm_btnBuscar'))) {
            $saper = new Saper;
            if($saper->login()) {
                if($saper->buscarAgentes(constant('Saper::' . Sanitizar::glPOST('tipoBusqueda')), 
                                            Sanitizar::glPOST('frm_txtValor'))
                ) {
                    $display = SAPER_DISPLAY_SELECT;
                    $agentes = $saper->getAgentes();
                    Session::remove(SAPER_SESSINDEX_FICHA);
                } else {
                    $display = SAPER_DISPLAY_NORESULT;
                }
            } else {
                Session::store(SMP_SESSINDEX_NOTIF_ERR, 'Error grave en SAPER login: ' . $saper->getError() . '. Contacte a un ' . contactar_administrador() . '.');
            }
        } elseif (!empty(Sanitizar::glPOST ('frm_btnVerFicha'))) {
            Session::store(SMP_SESSINDEX_NOTIF_ERR, 
                            'No se ha podido recuperar la ficha del '
                            . 'agente seleccionado: al menos un '
                            . 'par&aacute;metro inv&aacute;lido o bien '
                            . 'no hay resultados para la b&uacute;squeda');
            $saper = new Saper;
            if ($saper->login()) {
                if($saper->retrieveFicha(Sanitizar::glPOST('frm_radAgente'), 
                                            (Sanitizar::glPOST('frm_txtYear') ?: date("Y")), 
                                                Sanitizar::glPOST('frm_optMes'))
                ) {
                    $ficha = $saper->getFicha();
                    if (is_array($ficha)) {
                        // debo guardarla sin procesar
                        Session::store(SAPER_SESSINDEX_FICHA, $ficha);
                        foreach ($ficha as $anio => $year) {
                            foreach ($year as $mes => $f) {
                                // cálculo incial con hora de entrada 7:30
                                $entrada[$anio][$mes] = ["07:30", "07:30", "07:30", "07:30", "07:30"];
                                $f->procesarFicha($entrada[$anio][$mes]);
                                $f->add_column_tardes();
                                $f->add_column_faltantes();
                                $f->add_column_compensadas();
                                $f->add_column_extras();
                            }
                        }
                        Session::store(SAPER_SESSINDEX_HINI, $entrada);
                        Session::remove(SMP_SESSINDEX_NOTIF_ERR);
                        $display = SAPER_DISPLAY_CALC;
                    }
                }
            }
        } elseif (!empty(Sanitizar::glPOST('frm_btnCalcular'))) {
            Session::store(SMP_SESSINDEX_NOTIF_ERR, 
                                'No se ha podido procesar correctamente la '
                                . 'ficha del agente seleccionado: reintente '
                                . 'o repita la b&uacute;squeda');
            $ficha = Session::retrieve(SAPER_SESSINDEX_FICHA);
            if (is_array($ficha)) {
                $entrada = Sanitizar::glPOST('frm_txtHoraIni');
                Session::store(SAPER_SESSINDEX_HINI, $entrada);
                
                foreach ($ficha as $anio => $year) {
                    foreach ($year as $mes => $f) {
                        if (is_a($f, 'SaperFicha')) {
                            $f->procesarFicha(isset($entrada[$anio][$mes]) ? $entrada[$anio][$mes] : NULL);
                            $f->add_column_tardes();
                            $f->add_column_faltantes();
                            $f->add_column_compensadas();
                            $f->add_column_extras();
                        } else {
                            $err = TRUE;
                            break;
                        }
                    }
                }
                
                if (!isset($err)) {
                    $display = SAPER_DISPLAY_CALC;
                    Session::remove(SMP_SESSINDEX_NOTIF_ERR); 
                }
            }
        } elseif (!empty(Sanitizar::glPOST('frm_btnDescargar'))
                    || !empty(Sanitizar::glPOST('frm_btnImprimir'))
        ) {
            Session::store(SMP_SESSINDEX_NOTIF_ERR, 
                            'No se ha podido recuperar la ficha del agente '
                            . 'seleccionado.  Por favor, repita la '
                            . 'b&uacute;squeda.');
            $ficha = Session::retrieve(SAPER_SESSINDEX_FICHA);
            $entrada = Sanitizar::glPOST('frm_txtHoraIni');
            $btn = Sanitizar::glPOST('frm_btnDescargar');
            foreach ($btn as $anio => $year) {
                foreach ($year as $mes => $value) {
                    if ($value && is_a($ficha[$anio][$mes], 'SaperFicha')) {
                        $ficha[$anio][$mes]->procesarFicha(isset($entrada[$anio][$mes]) ? $entrada[$anio][$mes] : NULL);
                        $ficha[$anio][$mes]->add_column_tardes();
                        $ficha[$anio][$mes]->add_column_faltantes();
                        $ficha[$anio][$mes]->add_column_compensadas();
                        $ficha[$anio][$mes]->add_column_extras();

                        require_once SMP_FS_ROOT . SMP_LOC_EXT . 'mpdf/mpdf.php';
                        //ob_start(); // necesario pq la libreria mpdf es una cagada...
                        $mpdf = new mPDF('utf-8', 'A4', '','' , 0 , 0 , 0 , 0 , 0 , 0);
                        $mpdf->SetDisplayMode('fullpage');
                        $css = file_get_contents(SMP_FS_ROOT . SMP_LOC_CSS . 'pdf.css');
                        $mpdf->shrink_tables_to_fit = 1;
                        $mpdf->keep_table_proportions = TRUE;
        //                $mpdf->showImageErrors = true;
                        $mpdf->SetJS('this.print();');
                        $mpdf->WriteHTML($css, 1);

                        $html = Page::getHeader(SMP_FS_ROOT)
                                    . Page::getHeaderClose()
                                    . Page::getMain()
                                    . $ficha[$anio][$mes]->imprimir(5, 'ficha', FALSE)
                                    . Page::_e("<br />", 2, TRUE, FALSE)
                                    . SaperFicha::getDescripcionFicha()
                                    . Page::getMainClose();

                        $mpdf->WriteHTML($html, 2);
                        if (Sanitizar::glPOST('frm_btnDescargar')) {
                            $mpdf->Output('SiMaPe Ficha.pdf', 'D');
                        } /*elseif (Sanitizar::glPOST('frm_btnImprimir')) {
                            $pdf_fname = Crypto::getRandomFilename('Fichaje') . '.pdf';
                            $mpdf->Output(SMP_FS_ROOT . SMP_LOC_TMPS . $pdf_fname, 'F');
                            $pdf = file_get_contents($pdf_fname);
                            //send_to_browser($pdf, TRUE);
                            header('Content-Type: application/pdf');
                            header('Content-disposition: attachment; filename="' . $pdf_fname . '"');
                            Page::_e('<script type="text/javascript">window.open("data:application/pdf;base64, ' . base64_encode($pdf) . '");</script>');// . SMP_WEB_ROOT . SMP_LOC_TMPS . $pdf . '");</script>');
                            unset($css, $html, $ficha);
                            //ob_end_flush();
                        }*/
                        Session::remove(SMP_SESSINDEX_NOTIF_ERR); 
                        exit();
                    }
                }
            }
        }
    }
} else {
    $usuario->sesionFinalizar();
    $nav = SMP_HTTP_ERROR;
    $params = 403;
}

isset($nav) ? $page->nav($nav, isset($params) ? $params : NULL) : NULL;

// Token de pagina
$page->setLocation(SMP_LOC_USR . 'saper.php');
$page->generate();
Session::store(SMP_SESSINDEX_PAGE_RANDOMTOKEN, 
                    $page->getRandomToken());
Session::store(SMP_SESSINDEX_PAGE_TIMESTAMP, 
                    $page->getTimestamp());

// Token de formulario
$formToken->generate();
Session::store(SMP_SESSINDEX_FORM_RANDOMTOKEN, $formToken->getRandomToken());
Session::store(SMP_SESSINDEX_FORM_TIMESTAMP, $formToken->getTimestamp());
// -- --
//
// Mostrar página
Page::printHead('SiMaPe | Fichas de los empleados', 
                    ['main', 'msg', 'navbar', 'tabla', 'input']);
Page::printBody();
Page::printHeader();
Page::printHeaderClose();
Page::printDefaultNavbarVertical($usuario->getNombre());
Page::printMain();

Page::_e("<h2 style='text-align: center;'>Fichaje mensual de los agentes</h2>", 2);
Page::_e(Page::getForm(Page::FORM_OPEN, 
                        'frm_saper', 
                        'text-align: center; margin: 0 auto; width: 100%;', 
                        Page::FORM_METHOD_POST, 
                        Page::FORM_ENCTYPE_DEFAULT, 
                        NULL, 
                        '?' . SMP_SESSINDEX_PAGE_TOKEN . '=' . 
                        $page->getToken()), 
        2);

if (!empty(Session::retrieve(SMP_SESSINDEX_NOTIF_ERR))) {
    Page::_e("<p class='fadeout' "
                . "style='color:red; text-align: center;' >" 
                . Session::retrieve(SMP_SESSINDEX_NOTIF_ERR) . "</p>", 3);
    Session::remove(SMP_SESSINDEX_NOTIF_ERR);
}
Page::_e("<table style='text-align: center; margin: auto; width: auto; border-collapse:separate; border-spacing:0 1em;' >", 3);
Page::_e("<tbody>", 4);
Page::_e("<tr>", 5);

switch($display) {
    case SAPER_DISPLAY_SELECT:
        Page::_e("<td colspan='2'>", 6);
        Page::_e("<h3>Seleccione el agente buscado</h3>", 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        
        foreach ($agentes as $ord => $agente) {
            Page::_e("<tr>", 5);
            Page::_e("<td colspan='2'>", 6);
            end($agente);
            $doc_key = key($agente) - 1;
            Page::_e(Page::getInput('radio', 
                                    'frm_radAgente', 
                                    $agente[$doc_key] . $agente[$doc_key + 1], 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    NULL, 
                                    empty($ord) ? 'checked' : NULL), 
                    7);
            foreach ($agente as $key => $valor) {
                if ($key < $doc_key) {
                    echo $valor . " ";
                } else {
                    break;
                }
            }
            Page::_e("</td>", 6);
            Page::_e("</tr>", 5);
        }
        
        Page::_e("<tr>", 5);
        Page::_e("<td colspan='2'>", 6);
        Page::_e("<h3>Seleccione el per&iacute;odo deseado</h3>", 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
            
        Page::_e("<tr>", 5);
        Page::_e("<td>", 6);
        Page::_e("<select name='frm_optMes[0]'>", 7);
        for ($i = 1; $i < 13; $i ++) {  
            $mes = ucfirst(month_name_from_number($i));
            Page::_e("<option value='" . $i . "'" 
                    . (($i == date("m")) ? " selected" : '') . ">" 
                    . $mes . "</option>", 8);
        }
        Page::_e("</select>", 7);
        Page::_e("</td>", 6);
        Page::_e("<td>", 6);
        Page::_e(Page::getInput('number', 
                                'frm_txtYear[0]', 
                                date("Y"), 
                                NULL, 
                                'txt_fixed', 
                                NULL, 
                                NULL, 
                                "placeholder='A&ntilde;o'"), 
                7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        
        Page::_e("<tr>", 5);
        Page::_e("<td>", 6);
        Page::_e("<select name='frm_optMes[1]'>", 7);
        for ($i = 1; $i < 13; $i ++) {  
            $mes = ucfirst(month_name_from_number($i));
            Page::_e("<option value='" . $i . "'" 
                    . (($i == date("m")) ? " selected" : '') . ">" 
                    . $mes . "</option>", 8);
        }
        Page::_e("</select>", 7);
        Page::_e("</td>", 6);
        Page::_e("<td>", 6);
        Page::_e(Page::getInput('number', 
                                'frm_txtYear[1]', 
                                date("Y"), 
                                NULL, 
                                'txt_fixed', 
                                NULL, 
                                NULL, 
                                "placeholder='A&ntilde;o'"), 
                7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        
        Page::_e("<tr>", 5);
        Page::_e("<td>", 6);
        Page::_e(Page::getInput('submit', 
                                'frm_btnVerFicha', 
                                'Ver datos del agente seleccionado', 
                                NULL, 
                                'btn_blue'), 
                7);
        Page::_e("</td>", 6);
        Page::_e("<td>", 6);
        Page::_e(Page::getInput('submit', 
                                'frm_btnReiniciar', 
                                'Volver a buscar', 
                                NULL, 
                                'btn_red'), 
                7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        
        Page::_e("</tbody>", 4);
        Page::_e("</table>", 3);
        break;
        
    case SAPER_DISPLAY_CALC:
        Page::_e("<td colspan='2'>", 6);
        Page::_e("<h2>C&aacute;lculo de horas extras</h2>", 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        
        $horaInicio = Session::retrieve(SAPER_SESSINDEX_HINI);
        
        Page::_e("<tr>", 5);
        Page::_e("<td colspan='2'>", 6);
        Page::_e("<table style='border-spacing: 0px; width: 100%'>", 7);
        foreach ($ficha as $anio => $year) {
            foreach ($year as $mes => $f) {
                Page::_e("<tr>", 8);
                Page::_e("<td colspan='6'>", 9);
                Page::_e("<h4>Hora de inicio de tareas en " . $f->getMes() 
                        . " del " . $f->getAnio() . "</h4>", 10);
                Page::_e("</td>", 9);
                Page::_e("</tr>", 8);

                Page::_e("<tr>", 8);
                $dias = array('Lunes', 
                                'Martes', 
                                'Mi&eacute;rcoles', 
                                'Jueves', 
                                'Viernes');
                foreach ($dias as $dia) {
                    Page::_e("<td><em>" . $dia . "</em></td>", 9);
                }
                Page::_e("<td></td>", 9);
                Page::_e("</tr>", 8);

                Page::_e("<tr>", 8);
                for ($i = 0; $i < 5; $i++) {
                    Page::_e("<td>", 9);
                    Page::_e(Page::getInput('time', 
                                            "frm_txtHoraIni[" . $anio . "][" . $mes . "][" . $i . "]", 
                                            (isset($horaInicio[$anio][$mes][$i]) ? $horaInicio[$anio][$mes][$i] : "07:30"), 
                                            NULL, 
                                            NULL,
                                            5), 
                            10);
                    Page::_e("</td>", 9);
                }
                Page::_e("<td>", 9);
                Page::_e(Page::getInput('submit', 
                                            'frm_btnDescargar[' . $anio . '][' . $mes . ']', 
                                            'Descargar ficha en PDF', 
                                            NULL, 
                                            'btn_green'), 
                                        10);
                Page::_e("</td>", 9);
                Page::_e("</tr>", 8);
            }
        }
        Page::_e("</table>", 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
            
        Page::_e("<tr>", 5);
        Page::_e("<td>", 6);
        Page::_e(Page::getInput('submit', 
                                    'frm_btnReiniciar', 
                                    'Volver a buscar', 
                                    NULL, 
                                    'btn_red'), 
                    7);
        Page::_e("</td>", 6);

        Page::_e("<td>", 6);
        Page::_e(Page::getInput('submit', 
                                    'frm_btnCalcular', 
                                    'Calcular horas extras', 
                                    NULL, 
                                    'btn_blue'), 
                    7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
            
        $tardes_total = 0;
        foreach ($ficha as $year) {
            $tardes_total_anio = 0;
            foreach ($year as $f) {
                Page::_e("<tr>", 5);
                Page::_e("<td colspan='2'>", 6);
                $f->imprimir(7, 'ficha');
                Page::_e("</td>", 6);
                Page::_e("</tr>", 5);

                $tardes_total_anio += $f->getTardesTotal();
            }
            if (count($year) > 1) {
                Page::_e("<tr>", 5);
                Page::_e("<td colspan='2'>", 6);
                Page::_e("<h3 style='font-size: medium; font-weight: bold; "
                        . "color: #EF9D09; border: 2px solid black; margin: 0px; "
                        . "padding: 10px 0px;'>El total de llegadas tarde, "
                        . "sumando los meses mostrados para el a&ntilde;o " 
                        . $year[0]->getAnio() . " es: " 
                        . $tardes_total_anio, 7);
                Page::_e("</h3>", 7);
                Page::_e("</td>", 6);
                Page::_e("</tr>", 5);
            }
            $tardes_total += $tardes_total_anio;
        }
        
        if (count($ficha) > 1) {
            Page::_e("<tr>", 5);
            Page::_e("<td colspan='2'>", 6);
            Page::_e("<h3 style='font-size: medium; font-weight: bold; "
                    . "color: #EF9D09; border: 2px solid black; margin: 0px; "
                    . "padding: 10px 0px;'>El total de llegadas tarde, "
                    . "sumando todos los meses de todos los a&ntilde;os "
                    . "mostrados, es: " . $tardes_total, 7);
            Page::_e("</h3>", 7);
            Page::_e("</td>", 6);
            Page::_e("</tr>", 5);
        }
        
        Page::_e("</tbody>", 4);
        Page::_e("</table>", 3);
                
        Page::_e("<br />", 3);
        Page::_e(SaperFicha::getDescripcionFicha(), 3);
        break;
    
    case SAPER_DISPLAY_NORESULT:
        Page::_e("<td colspan='2' style='text-align: center;'>", 6);
        Page::_e("<em>La b&uacute;squeda no produjo resultados</em>", 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("<tr>", 5);
        /* OMITO BREAK */
    case SAPER_DISPLAY_SEARCH:  /* caso por defecto */
        /* OMITO BREAK */
    default :
        Page::_e("<td colspan='2' style='text-align: center;'>", 6);
        Page::_e("<h3>Seleccione par&aacute;metro a buscar e ingrese el valor correspondiente</h3>", 7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("<tr>", 5);
        Page::_e("<td style='text-align: center;'>", 6);
        Page::_e("<select name='tipoBusqueda'>", 7);
        Page::_e("<option value='BUSCAR_AUTO'>Autom&aacute;tico</option>", 8);
        Page::_e("<option value='BUSCAR_APELLIDO'>Apellido</option>", 8);
        Page::_e("<option value='BUSCAR_NOMBRE'>Nombre</option>", 8);
        Page::_e("<option value='BUSCAR_DNI'>DNI</option>", 8);
        Page::_e("<option value='BUSCAR_LEGAJO'>Legajo</option>", 8);
        Page::_e("</select>", 7);
        Page::_e("</td>", 6);
        Page::_e("<td style='text-align: center;'>", 6);
        Page::_e(Page::getInput('text', 
                                'frm_txtValor', 
                                NULL, 
                                NULL, 
                                'txt_resizable', 
                                NULL, 
                                NULL, 
                                'placeholder="Apellido/Nombre/DNI/Legajo" '
                                    . 'required'), 
                7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("<tr>", 5);
        Page::_e("<td colspan='2' style='text-align: center;'>", 6);
        Page::_e(Page::getInput('submit', 
                                'frm_btnBuscar', 
                                'Buscar', 
                                NULL, 
                                'btn_blue'), 
                7);
        Page::_e("</td>", 6);
        Page::_e("</tr>", 5);
        Page::_e("</tbody>", 4);
        Page::_e("</table>", 3);
        break;
}

Page::_e(Page::getInput('hidden', 'formToken', $formToken->getToken()), 7);
Page::_e(Page::getForm(Page::FORM_CLOSE));

Page::printMainClose();
Page::printFooter();
Page::printBodyClose();
