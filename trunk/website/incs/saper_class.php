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
 * @uses excel_reader2 Clase lectora de archivos XLS
 * @version 0.1
 */

include SMP_FS_ROOT  . SMP_LOC_INCS . 'phpexcel/PHPExcel/IOFactory.php';

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
    const P_CARGOSAGENTES = 'MenuPrincipal.do'; // http://10.1.0.7:7778/saper/MenuPrincipal.do?apellidoABuscar=...
    const P_CARGOSAGENTES2 = 'ConsultasAgenteAction.do';
    const P_ACTION = 'SistemaDigitalAction.do';
    
    const LOGIN_FORM_USR = 'usuario';
    const LOGIN_FORM_PWD = 'password';
    const LOGIN_FORM_TYPE = 'tipoUsuario';
    
    // URL = PROTOCOL://SERVER/ROOT/P_ACTION/CMD_ACTION+PARAM;
    // http://10.1.0.7:7778/saper/SistemaDigitalAction.do?method=exportarInformeIndividual&fichajeMes=Calendario&tipo=PDF&legajo=...&interno=1&anio=2014&mes=Febrero
    const METHOD_CMD = 'method';
    const METHOD_ACTION_INFINDIVIDUAL = 'exportarInformeIndividual'; /* method= */
    
    const FICHAJE_CMD = 'fichajeMes';
    const FICHAJE_ACTION = 'Calendario'; /* fichajeMes= */
    const FICHAJE_PARAM_ANIO = 'anio';
    const FICHAJE_PARAM_MES = 'mes';
    
    const BUSQUEDA_PARAM_DNI = 'dniABuscar';
    const BUSQUEDA_PARAM_NOMBRE = 'nombreABuscar';
    const BUSQUEDA_PARAM_APELLIDO = 'apellidoABuscar';
    const BUSQUEDA_PARAM_LEGAJO = 'legajoMostrarABuscar';
    const BUSQUEDA_PARAM_ESTADO = 'estadoABuscar';
    const BUSQUEDA_BTN = 'bus';
    
    // __SPECIALS
    function __construct() 
    {
        parent::__construct();
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
    
    public function login()
    {
        $url = static::urlMake(self::P_LOGIN);
        $post = array(
                        self::LOGIN_FORM_USR => urlencode(self::USR_NAME),
                        self::LOGIN_FORM_PWD => urlencode(self::USR_PWD),
                        self::LOGIN_FORM_TYPE => urlencode(self::USR_TIPO)
        );
        
        $this->post($url, $post);
    }


    // __PUB
}