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
 * Archivo de configuración para SiMaPe.
 * Todas las opciones pueden modificarse acorde a la necesidad; prestar
 * especial atención a la categorizada como "Config interna".
 * 
 * !!! 
 * IMPORTANTE: Una vez definida la configuración, debe ser renombrado a 
 * config.php
 * !!!
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.22
 */

// << Config
// 
// -- Misc
/**
 * Email del administrador de sistema, o del grupo de administradores.
 * Se emplea para mostrar un enlace mailto junto a la palabra 'administrador'
 * vía la función contactar_administrador().
 * Por defecto: ''.
 */
const SMP_ADMIN_EMAIL = '';
// --
// -- Base de datos
const SMP_DB_HOST = 'localhost';
const SMP_DB_NAME = 'SiMaPe';

const SMP_DB_USER_RO = 'appro';
const SMP_DB_PASS_RO = 'SECURE_PASS';

const SMP_DB_USER_RW = 'apprw';
const SMP_DB_PASS_RW = 'SECURE_PASS';

// ATENCION
// Verificar la configuracion de apache (y .htaccess) y el método
// Page::getHead al cambiar el charset!
// (NO RECOMENDADO)
const SMP_DB_CHARSET = 'utf8';
// --
// 
// -- Email
const SMP_EMAIL_SMTP_HOST = 'smtp.yourserver.com';
const SMP_EMAIL_SMTP_PORT = 0;
const SMP_EMAIL_SMTP_PROTO = ''; // "", "ssl", "tls"
const SMP_EMAIL_USER = 'usuario@yourserver.com';
const SMP_EMAIL_PSWD = 'secret_password';
const SMP_EMAIL_FROM = 'simape@yourserver.com';
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
const SMP_TKN_FINGERPRINT = 
        'RANDOM_STRING';
const SMP_TKN_SESSIONKEY = 
        'RANDOM_STRING';
const SMP_TKN_PAGE = 
        'RANDOM_STRING';
const SMP_TKN_FORM = 
        'RANDOM_STRING';
const SMP_TKN_PWDRESTORE = 
        'RANDOM_STRING';
// --
// 
// -- Sessionkey
// Tiempo de vida, en segundos
const SMP_SESSIONKEY_LIFETIME = 28800; // 8hs
// --
//
// Página por defecto al iniciar sesión
define('SMP_HOME', 'usr/index.php');
// --
// 
// Conexion SSL/TLS
/**
 * TRUE para activar la conexión vía SSL/TLS.
 * Por defecto: FALSE.
 */
const SMP_SSL = FALSE;

/**
 * TRUE para indicar que la conexión emplea HSTS
 * (solo válido si SMP_SSL = TRUE).
 * La configuración de Apache de simape_ssl lo utiliza por defecto.
 * Por defecto: TRUE.
 */
const SMP_SSL_HSTS = TRUE;
// --
// 
// Dirección IP del servidor (no es necesario salvo que se emplee SSL).
// IMPORTANTE: si se emplea SSL, la aplicación redireccionará
// todas las peticiones a través de https.  Si esta constante
// no se define, tratará de buscar la IP del servidor en la variable
// superglobal $_SERVER.  Aparte del riesgo de seguridad que esto implica,
// en caso de fallar, la aplicación no funcionaría.
// Se recomienda completar este valor acorde.
const SMP_SERVER_ADDR = '';
// --
// >>

// << Config interna
// No se recomienda modificar los siguientes valores, salvo que se tengan
// buenas razones para hacerlo.
// 
// -- Directorio de páginas
// Raiz del sitio (default '/')
// Debe corresponder a la configuración de apache, y siempre comenzar con '/'.
const SMP_WEB_ROOT = '/';

// Raíz para inclusión de archivos
// Definida en load.php

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
// -- Archivos (en bytes)
// Importante: si se emplea base64 para almacenar archivos, tener en cuenta
// que su tamaño aumenta aprox en un 40% (esto es, totalizaría 140% respecto
// de no usar base64).
// MEDIUMBLOB 15,9MB! Limite de la DB!!
const SMP_FILE_MAXSTORESIZE = 15728640;    // 15MB
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
// Otras constantes internas
const SMP_NAV_ACTION = 'accion';
const SMP_LOGOUT = 'logout';
const SMP_LOGIN = 'login';
const SMP_RESTOREPWD = 'restorePwd';
// --
// 
// >>
const SMP_CONFIG = TRUE;