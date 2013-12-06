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
 * Archivo de configuración para SiMaPe.
 * Todas las opciones pueden modificarse acorde a la necesidad; prestar
 * especial atención a la categorizada como "Config interna".
 * 
 */

// << Config
// 
// -- Base de datos
define('DB_HOST', 'localhost');
define('DB_NOMBRE', 'SiMaPe');

define('DB_USUARIO_RO', 'appro');
define('DB_PASS_RO', 'cEae2;*p(eH-Cm39iX])yrY}$,g=0p');

define('DB_USUARIO_RW', 'apprw');
define('DB_PASS_RW', '2/+W<W#Qety$fn!=O{^k*gP`TC!x`V');

// ATENCION
// Verificar la configuracion de apache (y .htaccess) y la funcion
// page_get_head()
// al cambiar el charset!
// (NO RECOMENDADO)
define('DB_CHARSET', 'utf8');
// --
// 
// -- Raiz del sitio (default '/')
define('WEB_ROOT', '/');
// --
// 
// -- Idioma, localización
// Los disponibles: cat /usr/share/i18n/SUPPORTED
setlocale(LC_TIME,'es_AR.UTF-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');
// --
// 
// -- Archivos (en bytes)
// NOTA: debe ser menor que FILE_MAXSTORESIZE!!
// IMPORTANTE: los parametros upload_max_filesize, post_max_size y memory_limit
//  del archivo /etc/php5/mods-available/security.ini determinan, en ese orden,
//  el limite de subida de archivos.  
//  Debe ser mayor al valor de estas constantes.
//  De no ser así, no se podrá subir archivos.
// Se emplea para archivos adjuntos
define('FILE_MAXUPLOADSIZE', 5242880);    // 5MB
// Se emplea para imagenes, como las de perfil
define('FILE_MAXIMGSIZE', 3145728);        // 3MB
// --
// 
// -- Crypto config
// Cambiar estos valores de tanto en tanto
// Usar: https://www.grc.com/passwords.htm
define('FINGERPRINT_TKN', 
        'SGxtTKP"6c~^M>h^l<_KR80~]Y=?Ziwer[TPPW&/IT?!r^`%fgArI5oZ[:JP+O<');
define('SESSIONKEY_TKN', 
        'TKt~bc!isdxP-sFA8V%2"Km!ihQvMPk#Y6;KC0[nOX8sczDK?FT6>ot43Ac<c-J');
define('PAGE_TKN', 
        '},jw7s%d}o|Da+dKh|jEcn@XLq&SMç{wHyYE5g%tX:KG5n96`A{p_<796bS1gA/');
define('FORM_TKN', 
        '=IMA"wPJh*C.C!GQZz%pCh!RdWIXsk_zIO`X?!#8%jH<XA?;ñFU3?(2rYO(#:I`');
// --
// 
// -- Sessionkey
// Tiempo de vida, en segundos
define('SESSIONKEY_LIFETIME', 21600); // 6hs
// --
// 
// >>

// << Config interna
// No se recomienda modificar los siguientes valores, salvo que se tengan
// buenas razones para hacerlo.
// 
// -- Directorio de páginas
// Raiz para inclusion de archivos
define( 'INC_ROOT', dirname(__FILE__) .'/');

define('LOC_CSS', 'css/');
define('LOC_IMGS', 'imgs/');
define('LOC_PAGS', 'pags/');
define('LOC_INC', 'inc/');
define('LOC_UPLOAD', 'upload/');
define('LOC_UPLOAD_FOTOS', LOC_UPLOAD . 'fotos/');

define('LOC_LOGIN', 'login.php');
define('LOC_NAV', 'nav.php');

define('LOC_FUNCIONES', LOC_INC . 'funciones.php');

define('LOC_MSGS', LOC_PAGS . 'mensajes.php');
define('LOC_USUARIO', LOC_PAGS . 'usuario.php');
define('LOC_EMPLEADO', LOC_PAGS . 'empleado.php');
define('LOC_FICHAJE', LOC_PAGS . 'fichaje.php');
// --
//
// -- Errores
$err_authfail = "Acceso incorrecto";
$err_dbconn = "Error de conexion con la base de datos";
$err_wrongpass = "Contrase&ntilde;a incorrecta";
// --
//
// -- Nombre de usuario y contraseña
define('USRNAME_MAXLEN', 15);
define('USRNAME_MINLEN', 5);
define('PWD_MAXLEN', 150);
define('PWD_MINLEN', 10);
// --
// 
// -- Mensajeria
define('MGS_MAXLEN', 250);
// --
// 
// -- Archivos (en bytes)
// Importante: si se emplea base64 para almacenar archivos, tener en cuenta
// que su tamaño aumenta aprox en un 40% (esto es, totalizaría 140% respecto
// de no usar base64).
// MEDIUMBLOB 15,9MB! Limite de la DB!!
define('FILE_MAXSTORESIZE', 15728640);    // 15MB
// --
// 
// -- Otras constantes internas
define('__SMP_FS_BINARY', 1);
define('__SMP_FS_BASE64', 2);
// --
// 
// >>

define('CONFIG', TRUE);
?>