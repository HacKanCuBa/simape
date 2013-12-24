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
 * Archivo de configuración para SiMaPe.
 * Todas las opciones pueden modificarse acorde a la necesidad; prestar
 * especial atención a la categorizada como "Config interna".
 * 
 * !!! IMPORTANTE: Una vez definida la configuración, debe ser renombrado a 
 * config.php
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.81
 */

// << Config
// 
// -- Base de datos
const SMP_DB_HOST = 'localhost';
const SMP_DB_NAME = 'SiMaPe';

const SMP_DB_USER_RO = 'appro';
const SMP_DB_PASS_RO = 'SECURE_PASS';

const SMP_DB_USER_RW = 'apprw';
const SMP_DB_PASS_RW = 'SECURE_PASS';

// ATENCION
// Verificar la configuracion de apache (y .htaccess) y la funcion
// page_get_head()
// al cambiar el charset!
// (NO RECOMENDADO)
const SMP_DB_CHARSET = 'utf8';
// --
// 
// -- Idioma, localización
// Los disponibles: cat /usr/share/i18n/SUPPORTED
const SMP_LOCALE = 'es_AR.UTF-8';
const SMP_TIMEZONE = 'America/Argentina/Buenos_Aires';
const SMP_PAGE_CHARSET = 'UTF-8';
//--
// 
// -- Archivos (en bytes)
// NOTA: debe ser menor que SMP_FILE_MAXSTORESIZE!!
// IMPORTANTE: los parametros upload_max_filesize, post_max_size y memory_limit
//  del archivo /etc/php5/mods-available/security.ini determinan, en ese orden,
//  el limite de subida de archivos.  
//  Debe ser mayor al valor de estas constantes.
//  De no ser así, no se podrá subir archivos.
// Se emplea para archivos adjuntos
const SMP_FILE_MAXUPLOADSIZE = 5242880;    // 5MB
// Se emplea para imagenes, como las de perfil
const SMP_FILE_MAXIMGSIZE = 3145728;        // 3MB
// --
// 
// -- Crypto config
// Cambiar estos valores de tanto en tanto
// Usar: https://www.grc.com/passwords.htm
const SMP_FINGERPRINT_TKN = 
        'RANDOM_STRING';
const SMP_SESSIONKEY_TKN = 
        'RANDOM_STRING';
const SMP_PAGE_TKN = 
        'RANDOM_STRING';
const SMP_FORM_TKN = 
        'RANDOM_STRING';
// --
// 
// -- Sessionkey
// Tiempo de vida, en segundos
const SMP_SESSIONKEY_LIFETIME = 21600; // 6hs
// --
// 
// >>

// << Config interna
// No se recomienda modificar los siguientes valores, salvo que se tengan
// buenas razones para hacerlo.
// 
// -- Directorio de páginas
// Raiz del sitio (default '/')
// Debe corresponder a la configuración de apache, y siempre comenzar con '/'.
const SMP_WEB_ROOT = '/';

// Raiz para inclusion de archivos
// Si se mueve este archivo a otro directorio, modificar esta definición 
// apropiadamente.
// NOTA: Solo puede moverse este archivo a otro directorio superior al del 
// sitio.  Si se lo mueve a otro directorio no superior, loadconfig.php no 
// podrá encontrar este archivo.  Ver loadconfig.php para más información.
define('SMP_INC_ROOT', dirname(__FILE__) .'/');

// Definir las siguientes rutas en forma relativa, tal de poder emplear luego
// SMP_INC_ROOT o SMP_WEB_ROOT según sea necesario.
const SMP_LOC_CSS = 'css/';
const SMP_LOC_IMGS = 'imgs/';
const SMP_LOC_PAGS = 'pags/';
const SMP_LOC_INCS = 'incs/';
const SMP_LOC_TMPS = 'tmps/';
const SMP_LOC_UPLOAD = 'upload/';
const SMP_LOC_UPLOAD_FOTOS = 'fotos/';

const SMP_LOC_LOGIN = 'login.php';
const SMP_LOC_NAV = 'nav.php';

define('SMP_LOC_MSGS', SMP_LOC_PAGS . 'mensajes.php');
define('SMP_LOC_USUARIO', SMP_LOC_PAGS . 'usuario.php');
define('SMP_LOC_EMPLEADO', SMP_LOC_PAGS . 'empleado.php');
define('SMP_LOC_FICHAJE', SMP_LOC_PAGS . 'fichaje.php');
// --
//
// -- Errores
const SMP_ERR_AUTHFAIL = 'Acceso incorrecto';
const SMP_ERR_DBCONN = 'Error de conexi&oacute;n con la base de datos';
const SMP_ERR_WRONGPASS = 'Contrase&ntilde;a incorrecta';
// --
// 
// -- Nombre de usuario y contraseña
/**
 * Este parámetro se emplea en la clase Password para generar nuevas
 * contraseñas.<br />
 * Es conveniente buscar un valor óptimo ejecutando en
 * una página de prueba: echo Password::getOptimalCost().<br />
 * <b>NOTA</b>: si vale menos que 10 o más de 31, no será tenido en cuenta.
 */
const SMP_PASSWORD_COST = 13;

/**
 * Tiempo de vida de la contraseña, en días. Pasado este tiempo, la contraseña
 * deberá cambiarse.<br />
 * Si es 0, la contraseña nunca expira.
 */
const SMP_PASSWORD_MAXDAYS = 365;

/**
 * Tiempo de vida del Token de reestablecimiento de contraseña, en segundos.
 */
const SMP_PASSWORD_RESTORETIME = 1800;  // 30 minutos

const SMP_USRNAME_MAXLEN = 15;
const SMP_USRNAME_MINLEN = 5;
const SMP_PWD_MAXLEN = 150;
const SMP_PWD_MINLEN = 13;
// --
// 
// -- Mensajeria
const SMP_MGS_MAXLEN = 140;
// --
// 
// -- Archivos (en bytes)
// Importante: si se emplea base64 para almacenar archivos, tener en cuenta
// que su tamaño aumenta aprox en un 40% (esto es, totalizaría 140% respecto
// de no usar base64).
// MEDIUMBLOB 15,9MB! Limite de la DB!!
const SMP_FILE_MAXSTORESIZE = 15728640;    // 15MB
// --
// 
// >>
const SMP_CONFIG = TRUE;
