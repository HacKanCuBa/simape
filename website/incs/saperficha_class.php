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
 * Manejo de la ficha de SAPER como objeto
 * 
 * Usa PHPExcel
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @uses PHPExcel Clase lectora de archivos XLS
 * @version 0.3
 */

class SaperFicha
{
    /**
     * Ficha.
     * Fila: días
     * Columna: informacion
     * @var array
     */
    protected $ficha = array();
        
    /**
     * Titulos de las columnas.
     * @var array
     */
    protected $titulos = array();
    
    protected $apellido, $nombre, $dni, $cargo, $dependencia;

    public function __construct($fname = NULL) 
    {
        $this->read_xls($fname);
    }
    
    // __PRIV
    
    // __PROT
    
    // __PUB
    /**
     * Carga una ficha desde un archivo de Excel.
     * @param string $fname Ruta y nombre de archivo.
     * @return boolean TRUE si tuvo éxito, FALSE si no.  
     * En caso de error, genera un mensaje de advertencia a nivel de usuario.
     */
    public function read_xls($fname)
    {
        if (empty($fname)) {
            return FALSE;
        }
        
        try {
            $csv = PHPExcel_IOFactory::load($fname);
            //var_dump($csv->getActiveSheet()->toArray(NULL, TRUE, TRUE, FALSE));
            $xls = $csv->getActiveSheet()->toArray(NULL, TRUE, TRUE, FALSE);
        } catch (PHPExcel_Exception $e) {
            trigger_error("Error en PHPExcel, desde " . __CLASS__ . "::" 
                            . __METHOD__ . "(), producido por archivo "
                            . $e->getFile() . " linea " . $e->getLine() 
                            . ": " . $e->getMessage(), E_WARNING);
            return FALSE;
        }
        
        $ficha = array();
        foreach ($xls as $i => $row) {
            foreach ($row as $col) {
                $col = trim($col);
                $time = round(24 * $col * 60 * 60);
                $ficha[$i][] = empty($col) ? "-" : (is_numeric($col) ? sprintf('%02d:%02d:%02d', ($time/3600), ($time/60%60), $time%60) : $col);
            }
        }
        // reacomodo
        // [0][0]: ape, nom
        // [0][5]: DNI <num>
        // [2][0]: CARGO: <cargo>
        // [2][5]: DEPENDENCIA: <dep>
        // [4][j]: titulos
        // [i>4][j]: datos
        list($this->apellido, $this->nombre) = explode(',', $ficha[0][0]);
        $this->dni = str_ireplace('DNI ', '', $ficha[0][5]);
        $this->cargo = str_ireplace('CARGO: ', '', $ficha[2][0]);
        $this->dependencia = str_ireplace('Dependencia: ', '', $ficha[2][5]);
        $this->titulos = $ficha[4];
        $this->ficha = array();
        $i = 0;
        while(isset($ficha[$i + 5])) {
            $this->ficha[$i] = $ficha[$i + 5];
            $i++;
        }

        return TRUE;
    }
    
    /**
     * Devuelve la ficha del agente como array.
     * @return array La ficha del agente como array.
     */
    public function get_asArray()
    {
        return (empty($this->ficha) ? array() : $this->ficha);
    }
    
    /**
     * Agrega una columna a la ficha, que debe haber sido creada previamente.
     * @param string $title Titulo de la columna
     * @param array $value Valor de la columna como array.
     * 
     */
    public function add_column($title, $value)
    {
        is_array($value) ?: $value = array($value);
        $title = strval($title);
        
        if (empty($this->ficha)) {
            $this->titulos = array($title);
            $this->ficha = $value;
        } else {
            $this->titulos[] = $title;
            $last = (count($this->ficha) > count($value)) ? count($this->ficha) : count($value);
            for ($i = 0; $i < $last; $i++) {
                $this->ficha[$i][] = isset($value[$i]) ? $value[$i] : NULL;
            }
        }
    }
    
    /**
     * Imprime la ficha del agente, que debe haber sido previamente cargada.
     * @param int $indent Indentado del código.
     */
    public function imprimir($indent = 0)
    {
        Page::_e("<table class='ficha'>", $indent);
        Page::_e("<thead>", $indent + 1);

        Page::_e("<tr>", $indent + 2);
        Page::_e("<td colspan='" . count($this->titulos) . "'><h2>Fichaje mensual</h2>", $indent + 3);
        Page::_e("</td>", $indent + 3);
        Page::_e("</tr>", $indent + 2);
        
        Page::_e("<tr>", $indent + 2);
        $cols = 0;
        if (isset($this->nombre) || isset($this->apellido)) {
            Page::_e("<td style='text-align: center;'>", $indent + 3);
            Page::_e("<h3>" . (isset($this->apellido) ? $this->apellido : '') 
                        . ", " . (isset($this->nombre) ? $this->nombre : '') 
                        . "</h3>", $indent + 4);
            Page::_e("</td>", $indent + 3);
            $cols++;
        }

        if (isset($this->dni)) {
            Page::_e("<td style='text-align: center;'>", $indent + 3);
            Page::_e("<h4>DNI " . $this->dni . "</h4>", $indent + 4);
            Page::_e("</td>", $indent + 3);
            $cols++;
        }

        if (isset($this->cargo)) {
            Page::_e("<td style='text-align: center;'>", $indent + 3);
            Page::_e("<h4>" . $this->cargo . "</h4>", $indent + 4);
            Page::_e("</td>", $indent + 3);
            $cols++;
        }

        if (isset($this->dependencia)) {
            Page::_e("<td colspan='" . (count($this->titulos) - $cols) . "' style='text-align: center;'>", $indent + 3);
            Page::_e("<h4>" . $this->dependencia . "</h4>", $indent + 4);
            Page::_e("</td>", $indent + 3);
        }
        unset($cols);
        Page::_e("</tr>", $indent + 2);
            
        if (isset($this->titulos)) {
            Page::_e("<tr>", $indent + 2);
            foreach ($this->titulos as $col) {
                Page::_e("<td><b>", $indent + 3);
                Page::_e($col, 0, FALSE);
                Page::_e("</b></td>", 0, FALSE);
            }
            Page::_e("</tr>", $indent + 2);
        }

        Page::_e("</thead>", $indent + 1);
        Page::_e("<tbody>", $indent + 1);
        
        if (isset($this->ficha) && is_array($this->ficha)) {
            foreach ($this->ficha as $fila) {
                Page::_e("<tr>", $indent + 2);
                foreach ($fila as $n => $col) {
                    $html_opn = "<td>";
                    $html_cls = "</td>";
                    switch ($n) {
                        case 0:
                            $html_opn .= "<b>";
                            $html_cls = "</b>" . $html_cls;
                            break;

                        case 8:
                            $html_opn .= "<i>";
                            $html_cls = "</i>" . $html_cls;
                            break;

                        default:
                            break;
                    }
                    Page::_e($html_opn, $indent + 3);
                    Page::_e($col, 0, FALSE);
                    Page::_e($html_cls, 0, FALSE);
                }
                Page::_e("</tr>", $indent + 2);
            }
        }
        
        Page::_e("</tbody>", $indent + 1);
        Page::_e("</table>", $indent);
    }
}