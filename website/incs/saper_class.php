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
 * Esta clase maneja el saper
 * - Navegacion
 * - Interpretación de los datos del XLS
 * 
 * Saper permite exportar en XLS 97, mucho más fácil de leer que pdf
 * Usa PHPExcel
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @uses PHPExcel Clase lectora de archivos XLS
 * @version 0.2
 */

class Saper extends Curl
{   
    // URL = PROTOCOL://SERVER/ROOT/<page>
    const PROTOCOL = 'http';
    const SERVER = '10.1.0.7:7778';
    const ROOT = 'saper';
    
    const USR_NAME = 'CMF001';
    const USR_PWD = 'CMF001';
    const USR_TIPO = 'A';
    
    const P_LOGIN = 'ValidaLoginAction.do';
    //const P_CARGOSAGENTES = 'MenuPrincipal.do'; // http://10.1.0.7:7778/saper/MenuPrincipal.do?apellidoABuscar=...
    const P_CARGOSAGENTES = 'ConsultasAgenteAction.do';
    const P_ACTION = 'SistemaDigitalAction.do';
    const P_DETALLE = 'verDetalleAgenteSeleccionado.do';
    
    const LOGIN_FORM_USR = 'usuario';
    const LOGIN_FORM_PWD = 'password';
    const LOGIN_FORM_TYPE = 'tipoUsuario';
    
    // URL = PROTOCOL://SERVER/ROOT/P_ACTION/CMD_ACTION+PARAM;
    // http://10.1.0.7:7778/saper/SistemaDigitalAction.do?method=exportarInformeIndividual&fichajeMes=Calendario&tipo=XLS&legajo=...&interno=1&anio=2014&mes=Febrero    
    // NOTA: el parámetro legajo= debe llevar construirse así: <N> . <DNI>
    // (un nro seguido del nro de documento, dnd ese nro es el tipo de doc (DNI/LC/LE/etc))
    // quien hizo esa bosta!?
    const FORMAT_XLS = 'XLS';
    const FORMAT_PDF = 'PDF';

    const FICHAJE_CMD = 'fichajeMes';
    const FICHAJE_ACTION = 'Calendario'; /* fichajeMes= */
    const FICHAJE_PARAM_ANIO = 'anio';
    const FICHAJE_PARAM_MES = 'mes';
    
    // IMPORTANTE: ninguna de estas BUSCAR_ puede ser nula!
    const BUSCAR_DNI = 1;
    const BUSCAR_NOMBRE = 2;
    const BUSCAR_APELLIDO = 3;
    const BUSCAR_LEGAJO = 4;
    const BUSCAR_AUTO = 5;
    
    const ESTADO_ACTIVO = 'A';
    const ESTADO_BAJA = 'B';
    
    const DNI_SEARCH_STR = "onchange='eleccionOpcion(this,";
    
    /**
     * Array de agentes encontrados por la búsqueda.
     * @var array
     */
    protected $Agentes = array();
    
    /**
     * Ruta al archivo de ficha.
     * @var string
     */
    protected $Ficha_fname = '';
    
    /**
     * Ficha del agente como objeto.
     * @var SaperFicha
     */
    protected $Ficha;

    // __SPECIALS
    function __construct() 
    {
        parent::__construct();
        require_once SMP_FS_ROOT . SMP_LOC_LIBS . 'phpexcel/PHPExcel.php';
        $this->Ficha = new SaperFicha;
    }
    // __PRIV
    
    // __PROT
    /**
     * Arma una URL de SAPER.
     * @param string $page Página requerida.  Si es NULL, devuelve la raíz de 
     * SAPER.
     * @return string URL armada.
     */
    protected static function urlMake($page = NULL) 
    {
        return self::PROTOCOL . '://' . self::SERVER . '/' . self::ROOT . '/' 
                . $page;
    }
    
    // __PUB
    /**
     * Array de agentes encontrados por buscarAgentes.
     * @return array Agentes.
     * @see buscarAgentes
     */
    public function getAgentes()
    {
        return $this->Agentes;
    }
    
    public function login()
    {
        $url = static::urlMake(self::P_LOGIN);
        $post = array(
                        self::LOGIN_FORM_USR => urlencode(self::USR_NAME),
                        self::LOGIN_FORM_PWD => urlencode(self::USR_PWD),
                        self::LOGIN_FORM_TYPE => urlencode(self::USR_TIPO)
        );
        $options = array(
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_FORBID_REUSE => 0,
            CURLOPT_COOKIESESSION => 1,
            CURLOPT_HEADER => 1
        );
        
        return $this->post($url, $post, $options);
    }
    
    /**
     * Busca agentes en base a un parámetro determinado y el estado del mismo.
     * El resultado se almancena en el objeto.
     * @param int $tipobusqueda Tipo de búsqueda:<br />
     * <ul>
     * <li>BUSCAR_DNI</li>
     * <li>BUSCAR_NOMBRE</li>
     * <li>BUSCAR_APELLIDO</li>
     * <li>BUSCAR_LEGAJO</li>
     * <li>BUSCAR_AUTO</li>
     * </ul>
     * Esta última determina automáticamente el tipo de parámetro buscado.
     * @param mixed $valor Valor del parámetro buscado.  
     * <i>INT</i> para DNI y Legajo, <i>STRING</i> para Nombre y Apellido.
     * @param bool $estado [opcional]<br />
     * Estado del agente buscado: ESTADO_ACTIVO para Activo (por defecto), 
     * ESTADO_BAJA para Baja.
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     * @see getAgentes
     */
    public function buscarAgentes($tipobusqueda, 
                                    $valor, 
                                    $estado = self::ESTADO_ACTIVO)
    {        
        $status = ($estado == self::ESTADO_ACTIVO) ? $estado : 
                    (($estado == self::ESTADO_BAJA) ? $estado : self::ESTADO_ACTIVO);
        $url = static::urlMake(self::P_CARGOSAGENTES);
        $options = array(
                        CURLOPT_FRESH_CONNECT => 0,
                        CURLOPT_FORBID_REUSE => 0,
                        CURLOPT_COOKIESESSION => 0,
                        CURLOPT_FOLLOWLOCATION => 1,
                        CURLOPT_HEADER => 1,
        );
                        
        $busqueda = [NULL, self::BUSCAR_APELLIDO, self::BUSCAR_DNI, self::BUSCAR_NOMBRE, self::BUSCAR_LEGAJO];
        do {
            if ($tipobusqueda == self::BUSCAR_AUTO) {
                $buscar = next($busqueda);
            } else {
                $buscar = $tipobusqueda;
            }
            
            $post = array(
                        'apellidoABuscar' => urlencode(NULL),
                        'cargosABuscar' => urlencode('A'),
                        'dniABuscar' => urlencode(NULL),
                        'estadoABuscar' => urlencode($status),
                        'idCodigoDependecia' => urlencode('23'),
                        'legajo' => urlencode(NULL),
                        'legajoMostrarABuscar' => urlencode(NULL),
                        'nombreABuscar' => urlencode(NULL),
                        'tipoBusquedaAgente' => urlencode('porAgente'),
                        'tipoDocumentoABuscar' => urlencode(NULL)
            );
            
            switch ($buscar) {
                case self::BUSCAR_DNI:
                    $post['dniABuscar'] = urlencode($valor);
                    break;

                case self::BUSCAR_NOMBRE:
                    $post['nombreABuscar'] = urlencode($valor);
                    break;

                case self::BUSCAR_APELLIDO:
                    $post['apellidoABuscar'] = urlencode($valor);
                    break;

                case self::BUSCAR_LEGAJO:
                    $post['legajoMostrarABuscar'] = urlencode($valor);
                    break;

                default :
                    return FALSE;
            }

            if ($this->post($url, $post, $options)) {
                // recuperar los resultados de la busqueda
                $raw_data = array_values(
                                array_filter(
                                    explode(' ', 
                                        str_ireplace("\r", ' ', 
                                            str_ireplace("\n", ' ', 
                                                str_ireplace("\t", ' ', 
                                                    trim(
                                                        filter_var($this->result,
                                                                    FILTER_SANITIZE_STRING, 
                                                                        FILTER_FLAG_STRIP_LOW 
                                                                        || FILTER_FLAG_STRIP_HIGH))))))));
//                var_dump($raw_data);
                $this->Agentes = array();
                $agentes_index = 0;

                foreach ($raw_data as $key => $value) {
                    if(strstr($value, 'B&uacute;squeda:')) {
                        // BASE de búsqueda
                        // 1° resultado (legajo): BASE + 9
                        // 2° resultado (legajo): Fin_1° (Activo/No Activo) + 32
                        // 3° resultado (legajo): Fin_2° (Activo/No Activo) + 32
                        // ...
                        // i° resultado: Fin_(i-1)° + 32
                        $raw_data_index = $key + 9;
                        while (isset($raw_data[$raw_data_index]) 
                                && (intval($raw_data[$raw_data_index]) > 0)
                        ) {
                            while ($raw_data[$raw_data_index] != 'Ver') {
                                $this->Agentes[$agentes_index][] = $raw_data[$raw_data_index];
                                $raw_data_index++;
                            }
                            $raw_data_index += 31; // se incrementó en 1 previamente
                            $agentes_index++; 
                        }
                        break;
                    }
                }

                if ($agentes_index > 0) {
                    // recuperar DNI/CI/LC/LE
                    //buscar: "onchange='eleccionOpcion(this,1/2/3/4," 
                    //segun tipo doc respectivamente
                    //hasta: ","
                    //lo del medio será el doc
                    $len = strlen($this->result);
                    $dni_fpos = 0;
                    $agentes_index = 0;
                    for ($ipos = 0; $ipos < $len; $ipos = $dni_fpos) {
                        $dni_ipos = strpos($this->result, self::DNI_SEARCH_STR, $ipos) + strlen(self::DNI_SEARCH_STR);
                        if($dni_ipos > strlen(self::DNI_SEARCH_STR)) {
                            $dni_fpos = strpos($this->result, ',', $dni_ipos + 2);
                            $dni = explode(',', substr($this->result, $dni_ipos, $dni_fpos - $dni_ipos));
                            $this->Agentes[$agentes_index][] = $dni[0];
                            $this->Agentes[$agentes_index][] = $dni[1];
                            $agentes_index++;
                        } else {
                            break;
                        }
                    }
                    return TRUE;
                }
            }
        } while ($tipobusqueda == self::BUSCAR_AUTO && $buscar);
        
        return FALSE;
    }

    /**
     * Recupera la ficha el agente seleccionado y la almacena en el objeto.
     * 
     * @see getFicha
     * @see printFicha
     * @param int $doc Tipo y nro. de documento.
     * @param int $year Nro. del año.
     * @param int $month Nro. del mes.
     * @param string $format Formato exportado: FORMAT_XLS (por defecto) o 
     * FORMAT_PDF.
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */
    public function retrieveFicha($doc, $year, $month, $format = self::FORMAT_XLS)
    {
        $format = ($format == self::FORMAT_XLS) ? $format : 
                    (($format == self::FORMAT_PDF) ? $format : self::FORMAT_XLS);

        $url = static::urlMake(self::P_ACTION);
        
        $options = array(
                    CURLOPT_FRESH_CONNECT => 0,
                    CURLOPT_FORBID_REUSE => 0,
                    CURLOPT_COOKIESESSION => 0,
                    CURLOPT_FOLLOWLOCATION => 1,
                    CURLOPT_HEADER => 0,
                    CURLOPT_TIMEOUT => 120,
        );
        $params = array(
                        'method' => urlencode('exportarInformeIndividual'),
                        'fichajeMes' => urlencode('Calendario'),
                        'interno' => urlencode(1),
                        'tipo' => urlencode($format),
                        'legajo' => urlencode($doc),
                        'anio' => urlencode($year),
                        'mes' => urlencode($month)
        );
        
        if ($this->get($url, $params, $options)) {
            $fname = Crypto::getRandomFilename('SMPFICHAJE', 9, SMP_FS_ROOT . SMP_LOC_TMPS);
            if(file_put_contents($fname, $this->result, LOCK_EX)) {
                unset($this->result);
                $this->Ficha->read_xls($fname);
                unlink($fname);
                return TRUE;
            }
        }
        
        return FALSE;        
    }
    
    /**
     * Devuelve la ficha previamente cargada del agente como array.
     * @return SaperFicha La ficha como objeto SaperFicha o NULL.
     */
    public function getFicha()
    {
        return (isset($this->Ficha) ? $this->Ficha : NULL);
    }
    
    /**
     * Almacena la ficha si es un objeto.
     * @param SaperFicha $ficha Objeto a almacenar.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setFicha($ficha)
    {
        if (isset($ficha) && is_a($ficha, 'SaperFicha')) {
            $this->Ficha = $ficha;
            return TRUE;
        }
        
        return FALSE;
    }
}