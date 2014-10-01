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
 * @version 0.81
 */

require_once 'autoload.php';

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
$usuario = new Usuario($session->retrieveEnc(SMP_SESSINDEX_USERNAME));

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
                Session::store(SMP_SESSINDEX_NOTIF_ERR, 'Error grave en SAPER login: ' . $saper->getError() . '. Contacte a un administrador.');
            }
        } elseif (!empty(Sanitizar::glPOST('frm_btnCalcular'))) {
            $ficha = Session::retrieve(SAPER_SESSINDEX_FICHA);
            if (empty($ficha)) {
                // procesar agente seleccionado
                // buscar ficha
                $saper = new Saper;
                if ($saper->login()) {
                    if($saper->retrieveFicha(Sanitizar::glPOST('frm_radAgente'), 
                                                (Sanitizar::glPOST('frm_txtYear') ?: date("Y")), 
                                                    Sanitizar::glPOST('frm_optMes'))
                    ) {
                        $ficha = $saper->getFicha();
                        Session::store(SAPER_SESSINDEX_FICHA, $ficha);
                        // calculo incial con hora de entrada 7:30
                        $entrada = Session::retrieve(SAPER_SESSINDEX_HINI) ?: 
                                    ["07:30", "07:30", "07:30", "07:30", "07:30"];
                    }  
                }
            } else {
                $entrada = Sanitizar::glPOST('frm_txtHoraIni');
                foreach ($entrada as $key => $value) {
                    if (strlen($value) <= 2) {
                        $entrada[$key] .= ':00';
                    }
                }
                Session::store(SAPER_SESSINDEX_HINI, $entrada);
            }
            if (is_a($ficha, 'SaperFicha')) {
//                var_dump($ficha);
                $ficha->procesarFicha($entrada);
                $ficha->add_column_faltantes();
                $ficha->add_column_compensadas();
                $ficha->add_column_extras();
                
                $display = SAPER_DISPLAY_CALC;
            } else {
                Session::store(SMP_SESSINDEX_NOTIF_ERR, 'No se ha podido recuperar la ficha del agente seleccionado: al menos un par&aacute;metro inv&aacute;lido o bien no hay resultados para la b&uacute;squeda');
                Session::remove(SAPER_SESSINDEX_FICHA);
            }
        } elseif (!empty(Sanitizar::glPOST('frm_btnImprimir'))) {
            $ficha = Session::retrieve(SAPER_SESSINDEX_FICHA);
            if (is_a($ficha, 'SaperFicha')) {
                $ficha->procesarFicha(Sanitizar::glPOST('frm_txtHoraIni'));
                $ficha->add_column_faltantes();
                $ficha->add_column_compensadas();
                $ficha->add_column_extras();
                // mpdf no interpreta bien el css
                $html = Page::getHeader(SMP_FS_ROOT) .
                        Page::getHeaderClose() .
                        Page::getMain() .
                        $ficha->imprimir(2, 'ficha', FALSE) .
                        "\n\t\t\t<br />" .
                        "\n\t\t\t<b>Los c&aacute;lculos se realizan bajo las siguientes condiciones:</b>" .
                        "\n\t\t\t<ul>" .
                        "\n\t\t\t\t<li>No se consideran los segundos en los fichajes (se truncan a 0).</li>" .
                        "\n\t\t\t\t<li>Si la hora a la que el agente ingres&oacute; es anterior a la hora a la que debe ingresar, se emplear&aacute; esta &uacute;ltima para el c&aacute;lculo.  Esto es, no se toma en cuenta el tiempo anterior a la hora de ingreso.</li>" .
                        "\n\t\t\t\t<li>Se considera Hora Extra a todo tiempo trabajado superior a 1 hora respecto de las horas laborales ordinarias.</li>" .
                        "\n\t\t\t\t<li>Se considera Tiempo Compensado a todo tiempo adicional a las horas laborales ordinarias inferior a 1h.</li>" .
                        "\n\t\t\t\t<li>Se considera Tiempo Faltante o Adeudado cuando no se hayan cumplido las horas laborales ordinarias.</li>" .
                        "\n\t\t\t\t<li>Las columnas de la planilla muestran valores propios, esto es, sin interacción entre sí.</li>" .
                        "\n\t\t\t\t<li>Cuando se presente m&aacute;s de un par de fichajes, a cada período se le aplicarán las reglas anteriores.</li>" .
                        "\n\t\t\t\t<li>La operación matemática realizada para las horas extras reales es: Horas Extra - (Horas Adeudadas - Horas Compensadas), si (Horas Adeudadas - Horas Compensadas) resulta mayor que 0 (esto es, el agente adeuda horas que no compensa y se descuentan de las extras).</li>" .
                        "\n\t\t\t\t<li>El Tiempo Compensado nunca se suma a las Horas Extra.</li>" .
                        "\n\t\t\t</ul>" .
                        Page::getMainClose();                
//                echo $html;
//                die();
//                
                require_once SMP_FS_ROOT . SMP_LOC_EXT . 'mpdf/mpdf.php';
                ob_start(); // necesario pq la libreria mpdf es una cagada...
                $mpdf = new mPDF('utf-8', 'A4', '','' , 0 , 0 , 0 , 0 , 0 , 0);
                $mpdf->SetDisplayMode('fullpage');
                $css = file_get_contents(SMP_FS_ROOT . SMP_LOC_CSS . 'pdf.css');
                $mpdf->shrink_tables_to_fit = 1;
                $mpdf->keep_table_proportions = TRUE;
//                $mpdf->showImageErrors = true;
                $mpdf->WriteHTML($css, 1);
                $mpdf->WriteHTML($html, 2);
                $mpdf->Output('Fichaje SiMaPe.pdf', 'D');
                unset($css, $html, $ficha);
                ob_end_flush();
                exit;
            } else {
                die('No hay ficha para imprimir!');
            }
        }
    }
} else {
    $usuario->sesionFinalizar();
    $nav = '403.php';
}

if (isset($nav)) {
    Page::nav($nav);
    exit();
}

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

echo Page::getHead('SiMaPe - SAPER');
echo Page::getBody();
echo Page::getHeader();
echo Page::getHeaderClose();
//echo Page::getDefaultNavbarVertical();
echo Page::getMain();

echo "\n\t\t<h2 style='text-align: center;'>Fichaje mensual de los agentes</h2>";
echo "\n\t\t<form style='text-align: center; margin: 0 auto; width: 100%;' "
     . "name='frm_saper' method='post' action='?" . SMP_SESSINDEX_PAGE_TOKEN . '=' . $page->getToken() . "' >";

if (!empty(Session::retrieve(SMP_SESSINDEX_NOTIF_ERR))) {
    echo "\n\t\t\t<p><address class='fadeout' "
         . "style='color:red; text-align: center;' >" 
         . Session::retrieve(SMP_SESSINDEX_NOTIF_ERR) . "</address></p>";
    Session::remove(SMP_SESSINDEX_NOTIF_ERR);
}
echo "\n\t\t\t<table style='text-align: left; margin: auto; width: auto;' >";
echo "\n\t\t\t\t<tbody>";
echo "\n\t\t\t\t\t<tr>";

switch($display) {
    case SAPER_DISPLAY_SELECT:
        echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<h3>Seleccione el agente buscado</h3>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        
        foreach ($agentes as $ord => $agente) {
            echo "\n\t\t\t\t\t<tr>";
            echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;'>";
            end($agente);
            $doc_key = key($agente) - 1;
            echo "\n\t\t\t\t\t\t\t<input type='radio' name='frm_radAgente' value='" . $agente[$doc_key] . $agente[$doc_key + 1] . "'" . (empty($ord) ? ' checked' : '') . ">";
            foreach ($agente as $key => $valor) {
                if ($key < $doc_key) {
                    echo $valor . "\t";
                } else {
                    break;
                }
            }
            echo "\n\t\t\t\t\t\t</td>";
            echo "\n\t\t\t\t\t</tr>";
        }
        
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<h3>Seleccione el mes y a&ntilde;o deseado</h3>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
            
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<select name='frm_optMes'>";
        for ($i = 1; $i < 13; $i ++) {  
            $mes = ucfirst(strftime('%B', strtotime($i . '/01/2014')));
            echo "\n\t\t\t\t\t\t\t\t<option value='" . $mes . "'" . (($i == date("m")) ? " selected" : '') . ">" . $mes . "</option>";
        }
        echo "\n\t\t\t\t\t\t\t</select>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t\t<td style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input type='number' name='frm_txtYear' placeholder='A&ntilde;o' value='" . date("Y") . "'>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<br /><input type='submit' name='frm_btnCalcular' value='Ver datos del agente seleccionado'>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t\t<td style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<br /><input type='submit' name='frm_btnReiniciar' value='Volver a buscar'>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        
        echo "\n\t\t\t\t</tbody>";
        echo "\n\t\t\t</table>";
        break;
        
    case SAPER_DISPLAY_CALC:
        echo "\n\t\t\t\t\t\t<td>";
        $ficha->imprimir(7);
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<br />";
        echo "\n\t\t\t\t\t\t\t<input type='submit' name='frm_btnImprimir' value='Imprimir ficha' />";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t</tbody>";
        echo "\n\t\t\t</table>";
                
        echo "\n\t\t\t<br />";
        echo "\n\t\t\t<table style='text-align: center; margin: auto; width: auto;' >";
        
        echo "\n\t\t\t\t<thead>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td colspan='5'>";
        echo "\n\t\t\t\t\t\t\t<h2>C&aacute;lculo de horas extras</h2>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t</thead>";
        
        echo "\n\t\t\t\t<tbody>";
        
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td colspan='5'><h4>Hora de inicio de tareas</h4>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        
        echo "\n\t\t\t\t\t<tr>";
        $dias = array('Lunes', 'Martes', 'Mi&eacute;rcoles', 'Jueves', 'Viernes');
        foreach ($dias as $dia) {
            echo "\n\t\t\t\t\t\t<td><i>" . $dia . "</i>";
            echo "\n\t\t\t\t\t\t</td>";
        }
        echo "\n\t\t\t\t\t</tr>";

        echo "\n\t\t\t\t\t<tr>";
        $horaInicio = Session::retrieve(SAPER_SESSINDEX_HINI) ?: ["07:30", "07:30", "07:30", "07:30", "07:30"];
        for ($i = 0; $i < 5; $i++) {
            echo "\n\t\t\t\t\t\t<td><input type='time' size='5' name='frm_txtHoraIni[" . $i . "]' value='" . $horaInicio[$i] . "'>";
            echo "\n\t\t\t\t\t\t</td>";
        }
        echo "\n\t\t\t\t\t</tr>";
        
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td colspan='5'><br />  "
                . "<input type='submit' value='Calcular horas extras' name='frm_btnCalcular'>"
                . "<input type='submit' name='frm_btnReiniciar' value='Volver a buscar'>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        
        echo "\n\t\t\t\t</tbody>";
        echo "\n\t\t\t</table>";
        
        echo "\n\t\t\t<br />";
        echo "\n\t\t\t<b>Los c&aacute;lculos se realizan bajo las siguientes condiciones:</b>";
        echo "\n\t\t\t<ul>";
        echo "\n\t\t\t\t<li>No se consideran los segundos en los fichajes (se truncan a 0).</li>";
        echo "\n\t\t\t\t<li>Si la hora a la que el agente ingres&oacute; es anterior a la hora a la que debe ingresar, se emplear&aacute; esta &uacute;ltima para el c&aacute;lculo.  Esto es, no se toma en cuenta el tiempo anterior a la hora de ingreso.</li>";
        echo "\n\t\t\t\t<li>Se considera Hora Extra a todo tiempo trabajado superior a 1 hora respecto de las horas laborales ordinarias.</li>";
        echo "\n\t\t\t\t<li>Se considera Tiempo Compensado a todo tiempo adicional a las horas laborales ordinarias inferior a 1h.</li>";
        echo "\n\t\t\t\t<li>Se considera Tiempo Faltante o Adeudado cuando no se hayan cumplido las horas laborales ordinarias.</li>";
        echo "\n\t\t\t\t<li>Las columnas de la planilla muestran valores propios, esto es, sin interacción entre sí.</li>";
        echo "\n\t\t\t\t<li>Cuando se presente m&aacute;s de un par de fichajes, a cada período se le aplicarán las reglas anteriores.</li>";
        echo "\n\t\t\t\t<li>La operación matemática realizada para las horas extras reales es: Horas Extra - (Horas Adeudadas - Horas Compensadas), si (Horas Adeudadas - Horas Compensadas) resulta mayor que 0 (esto es, el agente adeuda horas que no compensa y se descuentan de las extras).</li>";
        echo "\n\t\t\t\t<li>El Tiempo Compensado nunca se suma a las Horas Extra.</li>";
        echo "\n\t\t\t</ul>";
        break;
    
    case SAPER_DISPLAY_NORESULT:
        echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<i>La b&uacute;squeda no produjo resultados</i>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t\t<tr>";
        /* OMITO BREAK */
    case SAPER_DISPLAY_SEARCH:  /* caso por defecto */
        /* OMITO BREAK */
    default :
        echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<h3>Seleccione par&aacute;metro a buscar e ingrese el valor correspondiente</h3>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<select name='tipoBusqueda'>";
        echo "\n\t\t\t\t\t\t\t\t<option value='BUSCAR_AUTO'>Autom&aacute;tico</option>";
        echo "\n\t\t\t\t\t\t\t\t<option value='BUSCAR_APELLIDO'>Apellido</option>";
        echo "\n\t\t\t\t\t\t\t\t<option value='BUSCAR_NOMBRE'>Nombre</option>";
        echo "\n\t\t\t\t\t\t\t\t<option value='BUSCAR_DNI'>DNI</option>";
        echo "\n\t\t\t\t\t\t\t\t<option value='BUSCAR_LEGAJO'>Legajo</option>";
        echo "\n\t\t\t\t\t\t\t</select>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t\t<td style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<input type='text' name='frm_txtValor' "
                . "placeholder='Par&aacute;metro de b&uacute;squeda'>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t\t<tr>";
        echo "\n\t\t\t\t\t\t<td colspan='2' style='text-align: center;'>";
        echo "\n\t\t\t\t\t\t\t<br /><input type='submit' name='frm_btnBuscar' value='Buscar'>";
        echo "\n\t\t\t\t\t\t</td>";
        echo "\n\t\t\t\t\t</tr>";
        echo "\n\t\t\t\t</tbody>";
        echo "\n\t\t\t</table>";
        break;
}

echo "\n\t\t\t<input type='hidden' name='formToken' value='"
     . $formToken->getToken() . "' />";
echo "\n\t\t</form>";

echo Page::getMainClose();
echo Page::getFooter();
echo Page::getBodyClose();
