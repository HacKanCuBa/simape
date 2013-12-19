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

// -- Paginas
function page_get_url($loc = NULL, $params = NULL) 
{
    if (!empty($params)) {
        return SMP_WEB_ROOT . $loc . '?' . $params;
    }
    else {
        return SMP_WEB_ROOT . $loc;
    }
}

function page_get_head($title, $stylesheet = NULL) 
{
    /**
     * Devuelve el head del documento.
     * Debe continuarse con page_get_body, que cierra head y abre body
     */
    if (empty($stylesheet)) { 
        $stylesheet = 'main.css'; 
    }
    
    return "<!DOCTYPE html>\n<html lang='es-AR'>\n<head>" 
            . "\n\t<meta content='text/html; charset=UTF-8' "
            . "http-equiv='Content-Type' />"
            . "\n\t<title>$title</title>"
            . "\n\t<meta name='robots' content='noindex,nofollow' />"
            . "\n\t<link rel='stylesheet' type='text/css' href='" 
            . SMP_WEB_ROOT . SMP_LOC_CSS . $stylesheet . "'>"
            . "\n\t<link rel='icon' type='image/ico'  href='" 
            . SMP_WEB_ROOT . "favicon.ico'>";
}

function page_get_body() 
{
    /**
     * Devuelve el cierre de head y apertura de body.
     * Debe continuarse con page_get_header, que carga el encabezado.
     */
    return "\n</head>\n<body>";
}

function page_get_header() 
{
    /**
     * Muestra el encabezado.
     * Debe continuarse con page_get_header_close
     */
    return "\n\t<div class='header'>"
            . "\n\t\t<img src='". SMP_WEB_ROOT . SMP_LOC_IMGS . "header_small.png' " 
            . "alt='CSJN - CMF - SIMAPE' title='Corte Suprema de Justicia de "
            . "la Naci&oacute;n - A&ntilde;o de su Sesquicentenario - Cuerpo "
            . "M&eacute;dico Forense - SiMaPe' id='img_header_small' />";
}

function page_get_header_close() 
{
    /**
     * Cierra el encabezado
     */
    return "\n\t</div>";
}

/* vertical nav bar*/
function page_get_navbarV() 
{
    /**
     * Devuelve la barra de navegacion.
     * Debe ir antes de page_get_main y despues de page_get_header_close.
     */
    
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

function page_get_main() 
{
    /**
     * Abre el cuerpo del documento.
     * Debe ir despues de la barra de navegacion.
     */
    return "\n\t<div class='data'>";
}

function page_get_main_close() 
{
    /**
     * Cierra el cuerpo del documento.
     * Debe ir antes de page_get_footer.
     */
    return "\n\t</div>";
}

function page_get_footer() 
{
    /**
     *  Cierra por completo el documento, no debe haber nada despues de éste.
     */
    return "\n\t<p id='pi'>"
            . "\n\t\t<span class='pi_hidden'>SiMaPe - GPL v3.0 (C) "
            . "2013 Iv&aacute;n Ariel Barrera Oro</span><span "
            . "class='pi_visible'>π</span>"
            . "\n\t</p>"
            . "\n</body>"
            . "\n</html>";
}

function page_token_make($randtkn) 
{
    return hash_get(timestamp_get_thisHours(1) 
                    . $randtkn 
                    . constant('SMP_PAGE_TKN')
    );
}

function page_token_validate($token) 
{
    /**
     * Valida un token con el almacenado en sesión.
     * Devuelve TRUE si son IDENTICOS, FALSE si no lo son.
     * 
     * @param string $token Token a validar.
     * @return bool TRUE si son IDENTICOS, FALSE si no lo son
     */
    
    if (page_token_make($token) === session_get_pagetkn()) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function page_token_get_new() 
{
    /**
     * Devuelve un token para validar una página.
     * Al mismo tiempo, lo almacena en la sesion.
     * 
     * @param void
     * @return string Token
     */
    
    $randtoken = hash_get(get_random_token());
    $pagetoken = page_token_make($randtoken);
    session_set_pagetkn($pagetoken);
    
    return $randtoken;
}

function page_goto ($loc = NULL, $params = NULL) 
{
    header("Location: " . page_get_url($loc, $params));
}
// --

define('FUNC_PAGE', TRUE);