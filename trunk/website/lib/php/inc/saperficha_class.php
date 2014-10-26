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
 * @version 0.82
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
    protected $hs_extras, $hs_extras_total, $hs_compensadas, $hs_compensadas_total;
    protected $hs_faltantes_total, $hs_extras_real_total, $hs_faltantes;
    protected $tardes;


    /**
     * Diferencia tolerada entre fichajes de salida/entrada, en segundos.
     * P. E.: 13:33:21 y 13:38:10, se toma el primero.
     */
    const DIFF_FICHAJES = 600;
    
    /**
     * Diferencia tolerada para la llegada tarde (15')
     */
    const DIFF_TARDE = 900;

    public function __construct($fname = NULL) 
    {
        require_once SMP_FS_ROOT . SMP_LOC_EXT . 'phpexcel/PHPExcel.php';
        $this->read_xls($fname);
    }
    
    // __PRIV
    
    // __PROT
    /**
    * 
    * @param string $string String conteniendo la hora en la forma de "HH#MM#SS" 
    * o "HH#MM" donde # es un simbolo separador cualquiera.
    * @param boolean $getAsObject [opcional]<br />
    * TRUE para devolver resultado como objeto, 
    * FALSE para hacerlo como Unix Timestamp (por defecto).
    * @param boolean $removeSeconds [opcional]<br />
    * Si es TRUE, elimina los segundos del tiempo recibido (por defecto).
    * @return mixed Hora como objeto DateTime si $getAsObject = TRUE, 
    * si no como int Unix Timestamp (por defecto).  En caso de error, FALSE.
    */
    protected static function readHour($string, 
                                        $getAsObject = FALSE, 
                                        $removeSeconds = TRUE
    ) {
       try {
           $str = preg_replace('[\s]', ':', trim(preg_replace("/[^0-9]/", 
                                                                ' ', 
                                                                $string)));
           $time = (DateTime::createFromFormat("Y-m-d e H#i#s", 
                                               "1970-01-01 -0000 " . $str) ?: 
                   DateTime::createFromFormat("Y-m-d e H#i", 
                                               "1970-01-01 -0000 " . $str));
   
           ($time && $removeSeconds) ? $time->setTime($time->format('H'), 
                                                        $time->format('i'), 
                                                        0) : 
                                        NULL;
           if ($getAsObject) {
               return $time;
           } else {
               return ($time ? $time->getTimestamp() : FALSE);
           }
       } catch (Exception $e) {

       }

       return FALSE;
    }
    
    /**
     * Realiza la diferencia entre las horas de salida y entrada 
     * (Salida - Entrada - 6h),  y calcula las horas extras o compensadas, 
     * según corresponda;
     * o bien determina si el tiempo pasado en el parámetro $entrada 
     * (cuando $salida es nula), corresponde a extra, compensado o nada: 
     *
     * <ul>
     * <li>Si el tiempo o la diferencia es mayor a 1h, se considera extra.</li>
     * <li>Si es menor, pero mayor a 0, se considera compensada.</li>
     * <li>Si es menor a 0, se considera faltante (devuelve valor positivo).</li>
     * </ul>
     * @param int $entrada Hora de entrada como entero, o tiempo a determinar.
     * @param int $salida [opcional]<br />
     * Hora de salida como entero.
     * @return array Hora extra en el primer campo, 
     * Hora compensada en el segundo, y Hora faltante en el tercero.
     */
    protected static function calcExtra($entrada, $salida = 0)
    {
        $tiempo = $salida ? ($salida - $entrada - 21600) : $entrada;
        $extra = ($tiempo >= 3600) ? $tiempo : 0;
        $compensa = ($tiempo > 0 && $tiempo < 3600) ? $tiempo : 0;
        $falta = ($tiempo < 0) ? abs($tiempo) : 0;
        return array($extra, $compensa, $falta);
    }
    
    /**
     * Elimina los numeros similares entre sí, la diferencia definida por 
     * DIFF_FICHAJES.<br />
     * <i>Este método modifica el array recibido.</i>
     * @param array $nums Array numérico a procesar.
     * @return array Array recibido, procesado.
     */
    protected static function removeSimilar(array &$nums)
    {
        if (!empty($nums)) {
            sort($nums, SORT_NUMERIC);
            reset($nums);
//            do {
//                if (abs(current($nums) - next($nums)) <= self::DIFF_FICHAJES) {
//                    $nums[key($nums)] = 0;
//                }
//            } while(next($nums));
            // elimino los primeros
//            var_dump($nums);
            while (abs(current($nums) - next($nums)) < self::DIFF_FICHAJES) {
                $nums[key($nums) - 1] = 0;
                next($nums);
            }
            // elimino los últimos
            end($nums);
            while (abs(current($nums) - prev($nums)) < self::DIFF_FICHAJES) {
                $nums[key($nums) + 1] = 0;
                prev($nums);
            }
            $nums = array_filter($nums);
            sort($nums, SORT_NUMERIC);
//            var_dump($nums);
        }
        return $nums;
    }

    /**
     * Determina si un fichaje corresponde a una llegada tarde o no.
     * @param int $fichaje Fichaje de entrada.
     * @param int $entrada Hora de entrada correspondiente.
     * @return boolean TRUE si es tarde, FALSE si no.
     */
    protected static function es_tarde($fichaje, $entrada)
    {
        return ($fichaje > ($entrada + self::DIFF_TARDE));
    }
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
        
        $csv = PHPExcel_IOFactory::load($fname);
        //var_dump($csv->getActiveSheet()->toArray(NULL, TRUE, TRUE, FALSE));
        //elimino col de hs faltan y extras 
        $csv->getActiveSheet()->removeColumn('G', 2);
        $xls = $csv->getActiveSheet()->toArray(NULL, TRUE, TRUE, FALSE);
        // la ultima columna está vacía, la elimino.
        delete_column_from_matrix($xls, count($xls[0]) - 1);
        
//        var_dump($xls);
//        die();
        
        $ficha = array();
        foreach ($xls as $i => $row) {
            foreach ($row as $col) {
                $col = trim($col);
                $time = round(24 * $col * 60 * 60);
                $ficha[$i][] = empty($col) ? "" : (is_numeric($col) ? sprintf('%02d:%02d:%02d', ($time/3600), ($time/60%60), $time%60) : $col);
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
            // elimino el texto " No se computan las horas." del descargo
            end($this->ficha[$i]);
            $this->ficha[$i][key($this->ficha[$i])] = str_ireplace(' No se computan las horas.', '', $this->ficha[$i][key($this->ficha[$i])]);
            //
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
        $valor = is_array($value) ? $value : array($value);
        $titulo = strval($title);
        
        if (empty($this->ficha)) {
            $this->titulos = array($titulo);
            $this->ficha = $valor;
        } else {
            $this->titulos[] = $titulo;
            $last = (count($this->ficha) > count($valor)) ? count($this->ficha) : count($valor);
            for ($i = 0; $i < $last; $i++) {
                $this->ficha[$i][] = isset($valor[$i]) ? $valor[$i] : NULL;
            }
        }
    }
    
    /**
     * Imprime la ficha del agente, que debe haber sido previamente cargada.
     * @param int $indent [opcional]<br />
     * Indentado del código (0 por defecto).
     * @param boolean $class [opcional]<br />
     * Clase de la tabla.
     * @param boolean $print [opcional]<br />
     * TRUE para imprimir en pantalla (por defecto), FALSE para no hacerlo.
     * @return string Devuelve el string armado de la ficha del agente.
     */
    public function imprimir($indent = 0, $class = 'ficha', $print = TRUE)
    {
        $str = Page::_e("<table class='" . $class . "'>", $indent, TRUE, FALSE);
        $str .= Page::_e("<thead>", $indent + 1, TRUE, FALSE);

//        $str .= Page::_e("<tr>", $indent + 2, TRUE, FALSE);
//        $str .= Page::_e("<td colspan='" . count($this->titulos) . "'><h2>Fichaje mensual</h2>", $indent + 3, TRUE, FALSE);
//        $str .= Page::_e("</td>", $indent + 3, TRUE, FALSE);
//        $str .= Page::_e("</tr>", $indent + 2, TRUE, FALSE);
        
        $str .= Page::_e("<tr>", $indent + 2, TRUE, FALSE);
        $cols = 0;
        if (isset($this->nombre) || isset($this->apellido)) {
            $str .= Page::_e("<td colspan='3'>", $indent + 3, TRUE, FALSE);
            $str .= Page::_e("<h3>" . (isset($this->apellido) ? $this->apellido : '') 
                        . ", " . (isset($this->nombre) ? $this->nombre : '') 
                        . "</h3>", $indent + 4, TRUE, FALSE);
            $str .= Page::_e("</td>", $indent + 3, TRUE, FALSE);
            $cols+=3;
        }

        if (isset($this->dni)) {
            $str .= Page::_e("<td colspan='2'>", $indent + 3, TRUE, FALSE);
            $str .= Page::_e("<h4>DNI " . $this->dni . "</h4>", $indent + 4, TRUE, FALSE);
            $str .= Page::_e("</td>", $indent + 3, TRUE, FALSE);
            $cols +=2;
        }

        if (isset($this->cargo)) {
            $str .= Page::_e("<td  colspan='2'>", $indent + 3, TRUE, FALSE);
            $str .= Page::_e("<h4>" . $this->cargo . "</h4>", $indent + 4, TRUE, FALSE);
            $str .= Page::_e("</td>", $indent + 3, TRUE, FALSE);
            $cols += 2;
        }

        if (isset($this->dependencia)) {
            $str .= Page::_e("<td colspan='" . (count($this->titulos) - $cols) . "' style='text-align: center;'>", $indent + 3, TRUE, FALSE);
            $str .= Page::_e("<h3>" . $this->dependencia . "</h3>", $indent + 4, TRUE, FALSE);
            $str .= Page::_e("</td>", $indent + 3, TRUE, FALSE);
        }
        unset($cols);
        $str .= Page::_e("</tr>", $indent + 2, TRUE, FALSE);
            
        if (isset($this->titulos)) {
            $str .= Page::_e("<tr>", $indent + 2, TRUE, FALSE);
            foreach ($this->titulos as $col) {
                $str .= Page::_e("<td><b>", $indent + 3, TRUE, FALSE);
                $str .= Page::_e($col, 0, FALSE, FALSE);
                $str .= Page::_e("</b></td>", 0, FALSE, FALSE);
            }
            $str .= Page::_e("</tr>", $indent + 2, TRUE, FALSE);
        }

        $str .= Page::_e("</thead>", $indent + 1, TRUE, FALSE);
        $str .= Page::_e("<tbody>", $indent + 1, TRUE, FALSE);
        
        if (isset($this->ficha) && is_array($this->ficha)) {
            foreach ($this->ficha as $m => $fila) {
                $str .= Page::_e("<tr>", $indent + 2, TRUE, FALSE);
                foreach ($fila as $n => $col) {
                    $style = '';
                    switch ($n) {
                        case 0:
                            // fecha
                            $style = "font-weight: bold;";
                            break;
                        
                        case 6:
                            // descargo
                            $style = "font-style: italic;";
                            break;
                        
                        case 7:
                            // tarde
                            if ($col != '-') {
                                $style = "color: #EF9D09;";
                                $style .= ($m < (count($this->ficha) - 1)) 
                                                        ? ' font-size: 25px;' 
                                                        : '';
                            }
                            break;
                        
                        case 8:
                            // hs faltan
                            if ($col != '-') {
                                $style = "color: red;";
                            }
                            break;
                            
                        case 9:
                            // hs comp
                            if ($col != '-') {
                                $style = "color: blue;";
                            }
                            break;
                            
                        case 10:
                            // hs extra
                            if ($col != '-') {
                                $style = "color: green;";
                            }
                            break;
                            

                        default:
                            break;
                    }
                    //$style .= ($m == (count($this->ficha) - 1)) ? ' font-size: medium;' : '';
                    $str .= Page::_e("<td style='" . $style . "'>", $indent + 3, TRUE, FALSE);
                    $str .= Page::_e($col, 0, FALSE, FALSE);
                    $str .= Page::_e("</td>", 0, FALSE, FALSE);
                }
                $str .= Page::_e("</tr>", $indent + 2, TRUE, FALSE);
            }
        }
        
        $str .= Page::_e("<tr>", $indent + 2, TRUE, FALSE);
        $str .= Page::_e("<td colspan='" . count($this->titulos) . 
                            "' style='text-align: center;'>", $indent + 3, 
                                                                TRUE, FALSE);
        $str .= "<h3>Considerando el tiempo adeudado en el mes (<i>" 
                . sprintf('%02d:%02d:%02d', ($this->hs_faltantes_total/3600), 
                                                ($this->hs_faltantes_total/60%60),
                                                $this->hs_faltantes_total%60) 
                . "</i>) y teniendo en cuenta el tiempo compensado (<i>" 
                . sprintf('%02d:%02d:%02d', ($this->hs_compensadas_total/3600), 
                                            ($this->hs_compensadas_total/60%60),
                                            $this->hs_compensadas_total%60) 
                . "</i>), las horas extras reales son: " 
                . sprintf(($this->hs_extras_real_total < 0 ? '-' : '') . 
                                                            '%02d:%02d:%02d', 
                                    abs(($this->hs_extras_real_total/3600)),
                                    abs(($this->hs_extras_real_total/60%60)),
                                    abs($this->hs_extras_real_total%60))
                . ($this->hs_extras_real_total < 0 ? ' (adeuda tiempo)' : '')
                . "</h3>";
        $str .= Page::_e("</td>", $indent + 3, TRUE, FALSE);
        $str .= Page::_e("</tr>", $indent + 2, TRUE, FALSE);
        
        $str .= Page::_e("</tbody>", $indent + 1, TRUE, FALSE);
        $str .= Page::_e("</table>", $indent, TRUE, FALSE);
        
        echo($print ? $str : '');
        return $str;
    }
    
    /**
    * Procesa la ficha del agente y calcula: horas extras, tiempo compensado, 
    * tiempo adeudado.
    * 
    * @param array $entrada_diaria array con la hora de entrada por dia 
    * (formato HH:MM:SS), donde lunes es 0 y viernes, 4.
    * 
    * @return boolean TRUE si se ejecutó con éxito, FALSE si no.
    */
    public function procesarFicha(array $entrada_diaria = 
                                ["07:30", "07:30", "07:30", "07:30", "07:30"])
    {
        $extras = array();
        $extras_total = 0;
        $compensa = array();
        $compensa_total = 0;
        $faltan = array();
        $faltan_total = 0;
        $tarde = array();

        foreach ($this->get_asArray() as $dia) {
            $fecha = DateTime::createFromFormat("d#m#Y", $dia[0]);
            $tiempo = array(0, 0, 0, FALSE);
            // No leer fichaje si tiene una inasistencia
            if (end($dia) == '-' ||
                    stristr(end($dia), 'descargo') || 
                    stristr(end($dia), 'fichaje incompleto') ||
                    empty(end($dia))
            ) {
                // si x algun motivo no figura la hra de entrada, fijo a 7:30
                $entra = is_a($fecha, 'DateTime') 
                            ? ((intval(date("N", $fecha->getTimestamp())) < 6) 
                                ? (static::readHour($entrada_diaria[intval(date("N", $fecha->getTimestamp())) - 1]) 
                                    ?: 27000) 
                                : 27000) 
                            : 27000;
                
                // leo los fichajes validos y elimino el resto.
                // convierto todos a enteros.
                $descargo = array_filter(
                                array_map('intval', 
                                    array_map(array($this, 'readHour'), 
                                        explode(" ", 
                                            preg_replace("/[^0-9,.:]/", 
                                                            ' ', 
                                                            end($dia))))));
                // ordeno de menor a mayor y elimino valores parecidos
                static::removeSimilar($descargo);

                if (count($descargo) == 2) {
                    // Prioridad al descargo: si hay un par de fichajes validos,
                    // leerlos unicamente
                    
                    $entro = (($descargo[0] < $entra) ? $entra : $descargo[0]);
                    $tiempo = static::calcExtra($entro, $descargo[1]);
                    $tiempo[] = static::es_tarde($descargo[0], $entra);
                } else {
                    // genero un array con todos los fichajes, incluido el descargo.
                    $fichajes = array_merge(
                                    array_filter(
                                        array_map('intval', 
                                            array_map(array($this, 'readHour'), 
                                                array_merge(
                                                    preg_split('/[\s,\x0B,\x0D,\x0A,.]+/i', 
                                                                $dia[1]), 
                                                    preg_split('/[\s,\x0B,\x0D,\x0A,.]+/i', 
                                                                $dia[2]))))),
                                $descargo);
                    static::removeSimilar($fichajes);
                    if (count($fichajes) > 1) {
                        if (count($fichajes) % 2) {
                            // impar
                            // hago la diferencia entre el primero y el último
                            // debo descartar el ultimo, si la diff con el 
                            // anteultimo es menor a 5 minutos (o definido por 
                            // DIFF_SALIDA).
                            $entro = (($fichajes[0] < $entra) ? $entra : $fichajes[0]);
                            $salio = end($fichajes);
                            $tiempo = static::calcExtra($entro, $salio);
                            $tiempo[] = static::es_tarde($fichajes[0], $entra);
                        } else {
                            // par
                            // hago diferencias de a dos y voy sumando
                            $diff = 0;
                            $extra = 0;
                            $comp = 0;
                            $falta = 0;
                            while (current($fichajes)) {
                                if (key($fichajes)) {
                                    $entro = current($fichajes);
                                    $salio = next($fichajes);
                                    if ($diff >= 21600) {
                                        // si el 1° periodo era de 6hs o más, 
                                        // todo periodo posterior se suma en las 
                                        // extras si cumple las reglas
                                        $tiempo = static::calcExtra($salio - $entro);
                                    } else {
                                        // si el periodo anterior no alcanzo las 6hs,
                                        // debo sumar hasta alcanzar o superar 
                                        // y luego aplicar regla.
                                        $diff += $salio - $entro;
                                        $falta = 0;
                                        $tiempo = static::calcExtra($diff - 21600);
                                    }
                                    $extra += $tiempo[0];
                                    $comp += $tiempo[1];
                                    $falta += $tiempo[2];
                                } else {
                                    // 1° periodo
                                    $entro = (current($fichajes) < $entra) ? $entra : current($fichajes);
                                    $salio = next($fichajes);
                                    // si hay menos de 6hs, tiempo = 0 y 
                                    // diff el valor correspondiente.
                                    list($extra, $comp, $falta) = static::calcExtra($entro, $salio);
                                    $diff = $salio - $entro;
                                }
                                next($fichajes);
                            }
                            $tiempo = array($extra, $comp, $falta, static::es_tarde($fichajes[0], $entra));
                        }
                    }
                }
            }
            $compensa[] = $tiempo[1] ? DateTime::createFromFormat("Y-m-d e U", "1970-01-01 -0000 " . $tiempo[1])->format('H:i:s') : '';
            $compensa_total += $tiempo[1];
            $extras[] = $tiempo[0] ? DateTime::createFromFormat("Y-m-d e U", "1970-01-01 -0000 " . $tiempo[0])->format('H:i:s') : ($tiempo[1] ? '&lt; 1h' :  '');
            $extras_total += $tiempo[0];
            $faltan[] = $tiempo[2] ? DateTime::createFromFormat("Y-m-d e U", "1970-01-01 -0000 " . $tiempo[2])->format('H:i:s') : '';
            $faltan_total += $tiempo[2];
            $tarde[] = $tiempo[3] ? '•' : '';
        }       
        // el ultimo valor es 0, lo reemplazo por el total de extras
        end($extras);
        $extras[key($extras)] = sprintf('%02d:%02d:%02d', 
                                                ($extras_total/3600),
                                                ($extras_total/60%60), 
                                                $extras_total%60);

        end($compensa);
        $compensa[key($compensa)] = sprintf('%02d:%02d:%02d',
                                                ($compensa_total/3600),
                                                ($compensa_total/60%60),
                                                $compensa_total%60);

        end($faltan);
        $faltan[key($faltan)] = sprintf('%02d:%02d:%02d', 
                                                ($faltan_total/3600),
                                                ($faltan_total/60%60),
                                                $faltan_total%60);

        end($tarde);
        $tarde[key($tarde)] = count(array_filter($tarde));
        
        $this->hs_compensadas_total = $compensa_total;
        $this->hs_compensadas = $compensa;
        $this->hs_extras = $extras;
        $this->hs_extras_total = $extras_total;
        $this->hs_faltantes = $faltan;
        $this->hs_faltantes_total = $faltan_total;

        $adeuda = $this->hs_faltantes_total - $this->hs_compensadas_total;
        $this->hs_extras_real_total = $this->hs_extras_total - 
                                                ($adeuda > 0 ? $adeuda : 0);

        $this->tardes = $tarde;
        return TRUE;
    }
    
    /**
     * 
     * @return array Columna de horas extra con el total al final, o NULL.
     */
    public function getExtras()
    {
        return (isset($this->hs_extras) ? $this->hs_extras : NULL);
    }
    
    /**
     * 
     * @return int Total de horas extras, en segundos.
     */
    public function getExtrasTotal()
    {
        return (isset($this->hs_extras_total) ? $this->hs_extras_total : 0);
    }
    
    /**
     * 
     * @return int Total de horas extras reales, en segundos.
     */
    public function getExtrasRealTotal()
    {
        return (isset($this->hs_extras_real_total) ? 
                                                $this->hs_extras_real_total : 
                                                0);
    }
    
    /**
     * 
     * @return array Columna de horas compensadas con el total al final, o NULL.
     */
    public function getHorasCompensadas()
    {
        return (isset($this->hs_compensadas) ? 
                                            $this->hs_compensadas : NULL);
    }
  
    /**
     * 
     * @return int Total de horas compensadas, en segundos.
     */
    public function getHorasCompensadasTotal()
    {
        return (isset($this->hs_compensadas_total) ? 
                                            $this->hs_compensadas_total : 0);
    }  
    
    /**
     * 
     * @return array Columna de horas faltantes con el total al final, o NULL.
     */
    public function getHorasFaltantes()
    {
        return (isset($this->hs_faltantes) ? 
                                            $this->hs_faltantes : NULL);
    }
    
    /**
     * 
     * @return int Total de horas faltantes, en segundos.
     */
    public function getHorasFaltantesTotal()
    {
        return (isset($this->hs_faltantes_total) ? 
                                            $this->hs_faltantes_total : 0);
    }
    
    public function getTardes()
    {
        return (isset($this->tardes) ? $this->tardes : NULL);
    }
    
    /**
     * 
     * @return int Total de llegadas tarde.
     */
    public function getTardesTotal()
    {
        return (isset($this->tardes) ? end($this->tardes) : 0);
    }

    /**
     * Agrega la columna de horas extras calculadas a la ficha.
     * Idem a: <br />
     * <code>saperficha_obj->add_column(saperficha_obj->getExtras());</code>
     */
    public function add_column_extras()
    {
        if (!empty($this->hs_extras) && is_array($this->hs_extras)) {
            $this->add_column('Extras', $this->hs_extras);
        }
    }
    
    /**
     * Agrega la columna de horas compensadas calculadas a la ficha.
     * Idem a: <br />
     * <code>saperficha_obj->add_column(saperficha_obj->getHorasCompensadas());</code>
     */
    public function add_column_compensadas()
    {
        if (!empty($this->hs_compensadas) && is_array($this->hs_compensadas)) {
            $this->add_column('Comp', $this->hs_compensadas);
        }
    }
    
    /**
     * Agrega la columna de horas faltantes calculadas a la ficha.
     * Idem a: <br />
     * <code>saperficha_obj->add_column(saperficha_obj->getHorasFaltantes());</code>
     */
    public function add_column_faltantes()
    {
        if (!empty($this->hs_faltantes) && is_array($this->hs_faltantes)) {
            $this->add_column('Faltan', $this->hs_faltantes);
        }
    }
    
    /**
     * Agrega la columna de llegadas tarde a la ficha.
     * Idem a: <br />
     * <code>saperficha_obj->add_column(saperficha_obj->getTardes());</code>
     */
    public function add_column_tardes()
    {
        if (!empty($this->tardes) && is_array($this->tardes)) {
            $this->add_column('Tarde', $this->tardes);
        }
    }
    
    /**
     * Devuelve el texto de los detalles de calculo y uso de la planilla
     * @return type
     */
    public static function getDescripcionFicha()
    {
        return "\n<strong>Los c&aacute;lculos se realizan bajo las siguientes condiciones:</strong>" .
        "\n<ul style='text-align: left;'>" .
        "\n\t<li>No se consideran los segundos en los fichajes (se truncan a 0).</li>" .
        "\n\t<li>Si la hora a la que el agente ingres&oacute; es anterior a la hora a la que debe ingresar, se emplear&aacute; esta &uacute;ltima para el c&aacute;lculo.  Esto es, no se toma en cuenta el tiempo anterior a la hora de ingreso.</li>" .
        "\n\t<li>Se considera Hora Extra a todo tiempo trabajado superior a 1 hora respecto de las horas laborales ordinarias.</li>" .
        "\n\t<li>Se considera Tiempo Compensado a todo tiempo adicional a las horas laborales ordinarias inferior a 1h.</li>" .
        "\n\t<li>Se considera Tiempo Faltante o Adeudado cuando no se hayan cumplido las horas laborales ordinarias.</li>" .
        "\n\t<li>Las columnas de la planilla muestran valores propios, esto es, sin interacción entre sí.</li>" .
        "\n\t<li>Cuando se presente m&aacute;s de un par de fichajes, a cada período se le aplicarán las reglas anteriores.</li>" .
        "\n\t<li>La operación matemática realizada para las horas extras reales es: Horas Extra - (Horas Adeudadas - Horas Compensadas), si (Horas Adeudadas - Horas Compensadas) resulta mayor que 0 (esto es, el agente adeuda horas que no compensa y se descuentan de las extras).</li>" .
        "\n\t<li>El Tiempo Compensado nunca se suma a las Horas Extra.</li>" .
        "\n\t<li>Cuando ocurra una llegada tarde (ingreso luego de 15' de la hora de entrada), ser&aacute; indicada en la columna apropiada con un *.  Al final de la misma se indica el total.</li>" .
        "\n</ul>";
    }
}