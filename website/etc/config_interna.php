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

/*
 * Archivo de configuración interna para SiMaPe.
 * No se recomienda modificar las opciones aquí presentes si no se está seguro
 * de lo que está haciendo.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.00
 */

// -- DB
/**
 * Charset de la DB
 */
// ATENCION
// Verificar la configuracion de apache (y .htaccess) y el método
// Page::getHead al cambiar el charset!
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
/**
 * Tamaño máximo permitido para archivos almacenados.
 *  Importante: si se emplea base64 para almacenar archivos, tener en cuenta
 * que su tamaño aumenta aprox en un 40% (esto es, totalizaría 140% respecto
 * de no usar base64).
 * MEDIUMBLOB 15,9MB! Limite de la DB!!
 */
const SMP_FILE_MAXSTORESIZE = 15728640;    // 15MB
// NOTA: los siguientes deben ser menor que SMP_FILE_MAXSTORESIZE!!
// IMPORTANTE: los parametros upload_max_filesize, post_max_size y memory_limit
//  del archivo /etc/php5/mods-available/security.ini determinan, en ese orden,
//  el limite de subida de archivos.  
//  Debe ser mayor al valor de estas constantes.
//  De no ser así, no se podrá subir archivos.
/**
 * Tamaño máximo permitido para los archivos subidos (en bytes).
 */
const SMP_FILE_MAXUPLOADSIZE = 5242880;    // 5MB
/**
 * Tamaño máximo permitido para las imágenes subidas (en bytes).
 */
const SMP_FILE_MAXIMGSIZE = 3145728;        // 3MB
// --
//
// -- Sessionkey
// Tiempo de vida, en segundos
const SMP_SESSIONKEY_LIFETIME = 28800; // 8hs
// --
//
// -- Directorio de páginas
// Raíz para inclusión de archivos
// Definida en config.php

// Definir las siguientes rutas en forma relativa, tal de poder emplear luego
// SMP_FS_ROOT o SMP_WEB_ROOT según sea necesario.

// Directorios principales
// El directorio que contiene este archivo es el mismo que contiene otros
// archivos de configuración y opciones del sistema.
// configload.php se encarga de buscarlo y definirlo.
const SMP_LOC_LIBS = 'lib/';
const SMP_LOC_MEDIA = 'media/';
const SMP_LOC_TMPS = 'tmp/';
const SMP_LOC_USR = 'usr/';

// Subdirectorios 1er nivel
define('SMP_LOC_LIB_PHP', SMP_LOC_LIBS . 'php/');
define('SMP_LOC_CSS', SMP_LOC_MEDIA . 'css/');
define('SMP_LOC_IMGS', SMP_LOC_MEDIA . 'img/');
define('SMP_LOC_ADMIN', SMP_LOC_USR . 'adm/');
define('SMP_LOC_CONTENT', SMP_LOC_USR . 'content/');

// Subdirectorios 2do nivel
define('SMP_LOC_INC', SMP_LOC_LIB_PHP . 'inc/');
define('SMP_LOC_EXT', SMP_LOC_LIB_PHP . 'ext/');
define('SMP_LOC_FOTOSPERFIL', SMP_LOC_CONTENT . 'perfil/');
define('SMP_LOC_UPLOADS', SMP_LOC_CONTENT . 'upload/');
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
 * Tiempo de vida del Token de restablecimiento de contraseña, en segundos.
 */
const SMP_PASSWORD_RESTORETIME = 1800;  // 30 minutos

/**
 * Determina si se requerirá que las contraseñas de los usuarios sean 
 * <i>fuertes</i> por defecto.<br />
 * <b>NOTA:</b> Puede ser anulada desde el código.
 */
const SMP_PASSWORD_REQUIRESTRONG = TRUE;

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
// -- Constantes internas de índice de $_SESSION[]
const SMP_SESSINDEX_SESSIONKEY_TOKEN = 'sessionkeyToken';
const SMP_SESSINDEX_FORM_TOKEN = 'formToken';
const SMP_SESSINDEX_FORM_TIMESTAMP = 'formTimestamp';
const SMP_SESSINDEX_FORM_RANDOMTOKEN = 'formRandomToken';
const SMP_SESSINDEX_PAGE_TOKEN = 'pageToken';
const SMP_SESSINDEX_PAGE_TIMESTAMP = 'pageTimestamp';
const SMP_SESSINDEX_PAGE_RANDOMTOKEN = 'pageRandomToken';
const SMP_SESSINDEX_NOTIF_ERR = 'notifErr';
const SMP_SESSINDEX_NOTIF_MSG = 'notifMsg';
const SMP_SESSINDEX_USERNAME = 'username';
const SMP_SESSINDEX_SYSTEMPASSWORDSALT = 'systemPasswordSalt';
const SMP_SESSINDEX_SESSIONPASSWORD = 'sessionPassword';
// --
//
// -- Constantes internas de índice de $_GET
const SMP_GETINDEX_PAGE_TOKEN = 'pageToken';
const SMP_GETINDEX_RESTOREPWD = 'restorePwd';
const SMP_GETINDEX_PASSRESTORETKN = 'passRestoreToken';
// --
// 
// Otras constantes internas
const SMP_NAV_ACTION = 'accion';
const SMP_NAV_PARAMS = 'params';
const SMP_LOGOUT = 'logout';
const SMP_LOGIN = 'login';
const SMP_HTTP_ERROR = 'error';
// --
// 
// >>