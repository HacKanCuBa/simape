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
 * Todas las opciones pueden modificarse acorde a la necesidad.
 * Puede encontrar más opciones en config_interna.php, pero debe tener 
 * cuidado al modificar esos valores.
 * 
 * !!! 
 * IMPORTANTE: Una vez definida la configuración, debe ser renombrado a 
 * config.php
 * !!!
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 2.01
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
// 
// -- Base de datos
/**
 * Host de la base de datos, generalmente 'localhost'
 */
const SMP_DB_HOST = 'localhost';
/**
 * Nombre de la base de datos, por defecto 'SiMaPe'
 */
const SMP_DB_NAME = 'SiMaPe';
/**
 * Prefijo que reciben las tablas en la DB.
 */
const SMP_DB_TABLEPREFIX = '';
/**
 * Usuario de solo lectura (SELECT)
 */
const SMP_DB_USER_RO = 'appro';
/**
 * Contraseña para el usuario de solo lectura
 */
const SMP_DB_PASS_RO = 'SECURE_PASS';
/**
 * Usuario con permiso de escritura en la DB
 */
const SMP_DB_USER_RW = 'apprw';
/**
 * Contraseña para el usuario con permiso de escritura
 */
const SMP_DB_PASS_RW = 'SECURE_PASS';
/**
 * Usuario para el chat, que de tener lectura/escritura SOLO en las tablas de 
 * chat
 */
const SMP_DB_USER_CHAT = 'chat';
/**
 * Contraseña para el usuario de chat
 */
const SMP_DB_PASS_CHAT = 'SECURE_PASS';
// --
// 
// -- Chat
/**
 * Contraseña de la página de administración del chat.
 */
const SMP_CHAT_ADMINPASS = 'admin';
// 
// -- Email
/**
 * Dirección del servidor SMTP empleado.
 */
const SMP_EMAIL_SMTP_HOST = 'smtp.yourserver.com';
/**
 * Puerto del servidor SMTP.
 */
const SMP_EMAIL_SMTP_PORT = 0;
/**
 * Protocolo del servidor SMTP.
 * Valores posibles: '', 'ssl', 'tls'.
 */
const SMP_EMAIL_SMTP_PROTO = ''; // "", "ssl", "tls"
/**
 * Usuario del servidor SMTP.
 */
const SMP_EMAIL_USER = 'usuario@yourserver.com';
/**
 * Contraseña del servidor SMTP
 */
const SMP_EMAIL_PSWD = 'secret_password';
/**
 * Dirección 'De:' que identifica a los correos salientes.
 */
const SMP_EMAIL_FROM = 'simape@yourserver.com';
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
// -- IP
/**
 * Define una lista de IPs que tienen permitido el acceso en modo mantenimiento.
 * Deben estar separadas por comas, sin espacios.  Puede ser solo una.
 */
const SMP_MAINTENANCE_IP = 'IP1,IP2,IPn';
/**
 * Dirección IP del servidor (no es necesario salvo que se emplee SSL).
 * IMPORTANTE: si se emplea SSL, la aplicación redireccionará 
 * todas las peticiones a través de https.  Si esta constante
 * no se define, tratará de buscar la IP del servidor en la variable
 * superglobal $_SERVER.  Aparte del riesgo de seguridad que esto implica,
 * en caso de fallar, la aplicación no funcionaría.
 * Se recomienda completar este valor acorde.
 */
const SMP_SERVER_ADDR = '';
// --
// 
// -- Directorio de páginas
/**
 * Raiz del sitio (default '/').
 * Debe corresponder a la configuración de apache, y siempre comenzar 
 * y terminar con con '/'.
 */
const SMP_WEB_ROOT = '/';
// --
// 
// >>
// LÍMITE DE EDICIÓN, NO CAMBIAR NADA MÁS
// ¡NO MODIFICAR ESTA CONSTANTE!
/**
 * Define que la configuración se leyó correctamente.
 */
const SMP_CONFIG = TRUE;