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
 * classpage.php
 * Clase para crear y modelizar las páginas
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.4 untested
 */

class Page
{  
    /**
     * Nombre del archivo de imágen que se carga en el encabezado <br />
     * (debe incluir extensión y residir en SMP_LOC_IMGS).
     */
    const SMP_PAGE_HEADER_IMG = 'header_small.png';
    
    /**
     * Nombre del archivo Favicon predeterminado
     * (debe residir en la raíz del sitio).
     */
    const SMP_PAGE_FAVICON = 'favicon';
    
    /**
     * Nombre de la hoja de estilos predeterminada 
     * (debe residir en el subdirectorio SMP_LOC_CSS).
     */
    const SMP_PAGE_STYLESHEET_DEFAULT = 'main';
    /**
     * Determina la logitud máxima que puede tener una ruta relativa desde '/' 
     * a una página.
     */
    const SMP_PAGE_LOC_MAXLEN = 100;
    
    /**
     * Determina la longitud máxima que puede tener el nombre de una página 
     * (sin extensión, y sin ruta).
     */
    const SMP_PAGE_NAME_MAXLEN = 25;

    /**
     * Tiempo de vida de un token de página, en segundos.
     */
    const SMP_PAGE_TOKEN_LIFETIME = 3600;

    /**
     * Nombre de la hoja de estilos que será cargada 
     * (sin la extensión).  Debe encontrarse en el subdirectorio SMP_LOC_CSS.
     * @var string 
     */
    protected $stylesheet;
    
    /**
     * Título de la página que está siendo cargada. 
     * @var string
     */
    protected $title;
    
    protected $timestamp, $token;

    // __ SPECIALS
    
    // __ PRIV
    
    // __ PROT
    /**
     * Determina si una ruta es válida.<br />
     * Solo puede contener letras mayúsculas y minúsculas del alfabeto inglés,
     * números y los símbolos '/', '-' y '_'.<br />
     * El primer caracter debe ser una letra o un número.<br />
     * La longitud máxima la determina SMP_PAGE_LOC_MAXLEN.
     * 
     * @param string $loc Ruta a validar
     * @return boolean TRUE si la ruta es válida, FALSE si no.
     */
    protected static function isValid_loc($loc)
    {
        if (!empty($loc)
            && is_string($loc)
            && preg_match('/^[a-zA-Z0-9]{1}[a-zA-Z0-9\_\-\/\.]{0,'
                          . (self::SMP_PAGE_LOC_MAXLEN - 1) . '}$/', $loc)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Valida un string y determina si es un título de página válido.
     * @param string $title Título a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
    protected static function isValid_title($title)
    {
        if (isset($title) && is_string($title)) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    protected static function isValid_cssFName($stylesheet)
    {
        if (!empty($stylesheet)
            && is_string($stylesheet)
            && preg_match('/^[a-zA-Z0-9]{1}[a-z0-9A-Z\_\-]{0,' 
                      . (self::SMP_PAGE_NAME_MAXLEN - 1)
                      . '}$/', $stylesheet)
        ) {
            return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Recibe la ruta relativa a una página y los parámetros que le serán
     * pasados, y devuelve un string armado con la ruta y los parámetros.<br />
     * Si $loc es NULL (por defecto), dará como resultado la raíz del sitio.<br />
     * Si $params es NULL (por defecto), no enviará ningún parámetro.<br />
     * NOTA: NO determina si la página existe!
     * 
     * @param string $loc Ruta relativa desde '/' a la página deseada.<br />
     * Solo puede contener letras mayúsculas y minúsculas del alfabeto inglés,
     * números y los símbolos '/', '-' y '_'.
     * El primer caracter debe ser una letra o un número.
     * @param mixed $params Parámetros en la forma:
     * <ul>
     * <li>Como string: <i>nombre=valor&nombre2=valor2,...</i></li>
     * <li>Como array: <i>['nombre'=>'valor', 'nombre2'=>'valor2',...]</li>
     * </ul>
     * @return string Ruta relativa a la página con los parámetros incluidos.
     */
    protected static function makeUrl($loc = NULL, $params = NULL) 
    {
        $strParams = '';
        if (is_array($params)) {
            foreach ($params as $name => $param) {
                $strParams .= $name . '=' . $param . '&';
            }
            // Elimina el último &
            $strParams = '?' . substr($strParams, 0, -1);
        } elseif (is_string($params)) {
            $strParams = '?' . $params;
        }
        
        $pathPage = '';
        if (self::isValid_loc($loc)) {
            $pathPage = $loc; 
        }
        
        return $pathPage . $strParams;
    }
    
    /**
     * Devuelve un token de página armado.
     * @return mixed Token de página o FALSE en caso de error.
     */
    protected function makeToken() 
    {
        if (!empty($this->token)) {
            return Crypto::getHash(Timestamp::getThisSeconds(
                                    self::SMP_PAGE_TOKEN_LIFETIME) 
                                    . $this->token
                                    . SMP_PAGE_TKN);
        }
        
        return FALSE;
    }

    // __ PUB
    /**
     * Devuelve un Token aleatorio, que es el mismo que se emplea para armar
     * el token de página.
     * 
     * @see getToken()
     * @return string Token aleatorio.
     */
    public function getRandomToken()
    {
        $this->token = Crypto::getRandomTkn();
        return $this->token;
    }
    
    /**
     * Devuelve un Token de página.  Debe llamarse primero a getRandomToken()
     * o dará error.
     * 
     * @see getRandomToken()
     * @return mixed Token de página o FALSE en caso de error.
     */
    public function getToken()
    {
        return $this->makeToken();
    }

    /**
    * Devuelve el head del documento HTML.
    * Debe continuarse con getBody, que cierra head y abre body.
    * 
    * @see getBody()
    * @param string $title Título de la página.
    * @param array $stylesheet Array de nombres de hojas de estilos que serán 
    * cargadas, con la forma: ['miCss1', 'miCss2',...].<br />
    * De no indicar ninguna, se cargará SMP_PAGE_STYLESHEET_DEFAULT.<br />
    * @return string Encabezado del documento HTML debidamente formateado para
    * ser usado con echo().
    */
    public static function getHead($title, array $stylesheet = NULL) 
    {
        if (isset($title) && self::isValid_title($title)) {
            $titulo = $title;
        } else {
            $titulo = 'SiMaPe';
        }
        
        $code = "<!DOCTYPE html>\n<html lang='es-AR'>\n<head>" 
                . "\n\t<meta content='text/html; charset=". SMP_PAGE_CHARSET 
                . "' http-equiv='Content-Type' />"
                . "\n\t<title>$titulo</title>"
                . "\n\t<meta name='robots' content='noindex,nofollow' />"
                . "\n\t<link rel='icon' type='image/ico' href='" 
                . SMP_WEB_ROOT . self::SMP_PAGE_FAVICON . ".ico' />";
          
        if (empty($stylesheet)) {
            $code .= "\n\t<link rel='stylesheet' type='text/css' ";
            $code .= "href='" . SMP_WEB_ROOT . SMP_LOC_CSS;
            $code .= self::SMP_PAGE_STYLESHEET_DEFAULT . ".css' />";
        } elseif (is_array($stylesheet)) {
            foreach ($stylesheet as $css) {
                if (self::isValid_cssFName($css)) {
                    $cssFullName = SMP_WEB_ROOT . SMP_LOC_CSS . $css . '.css';
                    if (file_exists($cssFullName)) {
                        $code .= "\n\t<link rel='stylesheet' type='text/css' ";
                        $code .= "href='" . $cssFullName . "' />";
                    }
                }
            }
        }
        
        return $code;
    }

   /**
    * Devuelve el cierre de head y apertura de body.
    * Debe continuarse con getHeader, que carga el encabezado.
    * 
    * @see getHeader()
    * @return string Código HTML de cierre del Head y apertura de Body.
    */
    public static function getBody() 
    {   
        return "\n</head>\n<body>";
    }

    /**
     * Muestra el encabezado.
     * Debe continuarse con getHeaderClose().
     * 
     * @see getHeaderClose()
     * @return string Código HTML del encabezado del sitio.
     */
    public static function getHeader() 
    {
        return "\n\t<div class='header'>"
                . "\n\t\t<img src='". SMP_WEB_ROOT . SMP_LOC_IMGS 
                . self::SMP_PAGE_HEADER_IMG . "' alt='CSJN - CMF - SIMAPE' "
                . "title='Corte Suprema de Justicia de la Naci&oacute;n - "
                . "A&ntilde;o de su Sesquicentenario - Cuerpo "
                . "M&eacute;dico Forense - SiMaPe' id='img_header' />";
    }

    /**
     * Cierra el encabezado.  Permite incluir código personalizado en el
     * encabezado (entre getHeader() y getHeaderClose()).
     * 
     * @return string Código HTML de cierre del encabezado.
     */
    public static function getHeaderClose() 
    {
        return "\n\t</div>";
    }


    /**
     * Devuelve el código HTML de la barra de navegacion vertical.
     * Debe ir antes de getMain() y después de getHeaderClose().
     * 
     * @see getMain()
     * @see getHeaderClose()
     * @return string Código HTML de la barra de navegación vertical
     */
    public static function get_navbarV() 
    {
        // TODO
        // Aceptar un array ['nombre_boton' => 'nombre_pag', ...]
        // y armar dinámicamente la barra de navegación
        //
        
        return "\n\t<div class='nav_vertbox'>"
                . "\n\t\t<ul class='nav_vert'>"
                . "\n\t\t\t<li class='category'><a>&iexcl;Bienvenido <i>" 
                . session_get_username() . "</i>!</a>"
                . "\n\t\t\t<li><a href='" . page_get_url(SMP_LOC_NAV, 'accion=mensajes') 
                . "'>Mensajes</a></li>"
                . "\n\t\t\t<li><a href='" . page_get_url(SMP_LOC_NAV, 'accion=perfilemp') 
                . "'>Mi perfil de empleado</a></li>"
                . "\n\t\t\t<li><a href='" . page_get_url(SMP_LOC_NAV, 'accion=perfilusr') 
                . "'>Mi perfil de usuario</a></li>"            
                . "\n\t\t\t<li class='category'><a>Administraci&oacute;n de usuarios</a></li>"
                . "\n\t\t\t<li><a href='#'>Listar todos los existentes</a></li>"
                . "\n\t\t\t<li><a href='#'>Cargar nuevo perfil</a></li>"
                . "\n\t\t\t<li><a href='#'>Modificar perfil existente</a></li>"
                . "\n\t\t\t<li class='category'><a>Administraci&oacute;n de empleados</a></li>"
                . "\n\t\t\t<li><a href='#'>Listar todos los existentes</a></li>"
                . "\n\t\t\t<li><a href='#'>Cargar nuevo perfil</a></li>"
                . "\n\t\t\t<li><a href='#'>Modificar perfil existente</a></li>"
                . "\n\t\t\t<li><a href='#'>Reemplazos</a></li>"
                . "\n\t\t\t<li class='category'><a>Asistencias / Inasistencias</a></li>"
                . "\n\t\t\t<li><a href='#'>Ver por fecha</a></li>"
                . "\n\t\t\t<li><a href='#'>Ver por empleado</a></li>"
                . "\n\t\t\t<li class='category'><a href='" 
                . page_get_url(SMP_LOC_NAV, 'accion=logout') 
                . "'>Cerrar sesi&oacute;n</a></li>"
                . "\n\t\t</ul>"
                . "\n\t</div>";
    }

    /* horiz nav bar
     * function page_get_navbar($currentpage) {
        $msgs = '';
        $perfilusr = '';
        $perfilemp = '';

        switch ($currentpage) {
            case SMP_LOC_MSGS:
                $msgs = " class='current'";
                break;

            case SMP_LOC_EMPLEADO:
                $perfilemp = " class='current'";
                break;

            case SMP_LOC_USUARIO:
                $perfilusr = " class='current'";
                break;

            default:
        }

        return '<div style="text-align: center; margin-top: auto; top: auto; height: auto;">
            <ul id="nav">
                <li' . $msgs . '><a href="' . page_get_url(SMP_LOC_NAV, "accion=mensajes") . '">&iexcl;Bienvenido <i>' . session_get_username() . '</i>!</a></li>
                <li' . $perfilusr . '><a href="' . page_get_url(SMP_LOC_NAV, "accion=perfilusr") . '">Mi perfil de usuario</a>
                <li' . $perfilemp . '><a href="' . page_get_url(SMP_LOC_NAV, 'accion=perfilemp') . '">Mi perfil de empleado</a></li>
                <li><a href="' . page_get_url(SMP_LOC_NAV, 'accion=logout') . '">Cerrar sesi&oacute;n</a></li>
            </ul>
        </div>';
    }*/

    /**
     * Abre el cuerpo de la página.<br />
     * Debe ir despues de la barra de navegacion.
     * 
     * @return string Código HTML de apertura del cuerpo de la página.
     */
    public static function getMain() 
    {
        return "\n\t<div class='data'>";
    }

    /**
     * Cierra el cuerpo de la página.<br />
     * Debe ir antes de getFooter() y después de getMain().
     * 
     * @return string Código HTML de cierre de la página.
     */
    public static function getMainClose() 
    {
        return "\n\t</div>";
    }

    /**
     * Cierra por completo la página, no debe haber nada despues de éste.
     * 
     * @return string Código HTML de cierre completo de la página.
     */
    public static function getFooter() 
    {
        return "\n\t<p id='pi'>"
                . "\n\t\t<span class='pi_hidden'>"
                . "SiMaPe: GNU GPL v3.0 (C) 2013 Iv&aacute;n Ariel Barrera Oro"
                . "</span><span class='pi_visible'>π</span>"
                . "\n\t</p>"
                . "\n</body>"
                . "\n</html>";
    }
    
    /**
     * Fija un token aleatorio.  Se emplea en la función de verificación.<br />
     * <b>IMPORTANTE</b>: NO emplearlo para generar uno nuevo! 
     * usar los métodos getRandomToken() y getToken() a este fin.
     * 
     * @see getRandomToken()
     * @see getToken()
     * @param string $randToken Token aleatorio.
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
    public function setRandomToken($randToken)
    {
        if (!empty($randToken) && is_string($randToken)) {
            $this->token = $randToken;
            return TRUE;
        }
        
        return FALSE;
    }
            
    /**
     * Autentica un Token de página.  Debe fijarse primero el Token aleatorio
     * con el que se creó, mediante setRandomToken().
     * 
     * @param string $pageToken Token de página a autenticar.
     * @return bool TRUE si es auténtico, FALSE si no.
     */
    public function authenticateToken($pageToken) 
    {
        if ($pageToken === $this->makeToken()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * Envía los headers necesarios para ir a la página indicada, envíando
     * también los parámetros requeridos.
     * Si $loc es NULL (por defecto), dará como resultado la raíz del sitio.<br />
     * Si $params es NULL (por defecto), no enviará ningún parámetro.<br />
     * NOTA: Primero verifica que la página solicitada exista.<br />
     * <b>IMPORTANTE</b>: Es conveniente llamar a exit() 
     * <i>inmediatamente después</i> de este método.
     * 
     * @param string $loc Ruta relativa desde '/' a la página deseada.<br />
     * Solo puede contener letras mayúsculas y minúsculas del alfabeto inglés,
     * números y los símbolos '/', '-' y '_'.
     * El primer caracter debe ser una letra o un número.
     * @param mixed $params Parámetros en la forma:
     * <ul>
     * <li>Como string: <i>nombre=valor&nombre2=valor2,...</i></li>
     * <li>Como array: <i>['nombre'=>'valor', 'nombre2'=>'valor2',...]</li>
     * </ul>
     * @return boolean TRUE si se enviaron correctamente los headers, 
     * FALSE si no.
     */
    public static function go_to ($loc = NULL, $params = NULL) 
    {
        if (file_exists(SMP_INC_ROOT . self::makeUrl($loc))) {
            header("Location: " . SMP_WEB_ROOT . self::makeUrl($loc, $params));
            return TRUE;
        }
        
        return FALSE;
    }
}