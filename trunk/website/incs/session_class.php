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
 * Maneja la carga y almacenamiento de datos en la variable super global SESSION
 * 
 * Ejemplo de uso:
 * <pre><code>
 * // Inicia o continua una sesión, debe usarse como session_start()
 * Session::initiate();
 * Session::set('indice', $valor);
 * var_dump(Session::get('indice'));
 * // Finalizar la sesión
 * Session::terminate();
 * // Para volver a usar la sesión, deberá llamarse nuevamente a 
 * // Session::initiate()
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.8 untested
 */
class Session
{
    use Token;

    /**
     * Determina el tiempo de vida de la cookie.  0 implica 'hasta el
     * cierre del navegador'.
     */
    const SMP_SESSION_COOKIE_LIFETIME = 0;
    
    // __ SPECIALS
    /**
     * Guarda el valor en la sesión: $_SESSION[$key] = $value.
     * 
     * @param mixed $key Índice, puede ser un string o un entero.
     * @param mixed $value Valor, puede ser cualquier elemento serializable
     * (se recomienta emplear valores escalares o arrays, y evitar objetos).
     * @param boolean $sanitize Si es TRUE, sanitiza el valor antes de 
     * almacenarlo. (FALSE por defecto)
     */
    public function __construct($key, $value = NULL, $sanitize = FALSE) 
    {
        if (isset($key)) {
            self::set($key, $value, $sanitize);
        }
    }
    // __ PRIV
    
    // __ PROT
	/**
     * Verifica si el Token de Sesión es válido.
     * 
     * @param type $sessionToken Token a validar.
     * @return boolean TRUE si es válido, FALSE si no.
     */
	protected static function isValid_sessionToken($sessionToken)
    {
        // No difiere de un token estandard
        self::isValid_token($fingerprintToken);
    }
	
	/**
     * Devuelve un Token de Sesión armado.
     * 
     * @param string $randToken Token aleatorio.
     * @param float $timestamp Timestamp.
     * @return mixed Token de Sesión o FALSE en caso de error.
     */
    protected static function tokenMake($randToken, $timestamp)
	{
	}
	
    /**
     * Crea una nueva sesion y devuelve el nombre de la misma.
     * 
     * @param int $lifetime Duracion de la sesion, en segundos (0 implica hasta
     * que se cierre el navegador).
     * @param string $path Ruta en el dominio a la que tendra alcance la sesion.
     * @param string $domain Dominio del sitio.
     * @param bool $https Inidica si se usara https (TRUE) o no (FALSE).
     * @return string Devuelve el nombre de la sesion creada.
     * 
     */
    protected static function begin($lifetime = SMP_SESSION_COOKIE_LIFETIME, $path = NULL, 
                                    $domain = NULL, $https = NULL)
    {
        // Ideas:
        // http://security.stackexchange.com/questions/24177/starting-a-secure-php-session
        // http://www.wikihow.com/Create-a-Secure-Session-Managment-System-in-PHP-and-MySQL
        // 
        
        // Crear una cookie con nombre unico
        // El nombre debe empezar obligatoriamente con una letra minúscula
        $length = 9;
        $name = substr(shuffle(range('a', 'z')), 0, 1);
        $name .= substr(str_shuffle(md5(mt_rand()) . md5(mt_rand())), 0, $length);
        //session_name($name);

        // Configurar domain
        $domain = empty($domain) ? Sanitizar::glSERVER('SERVER_NAME') : $domain;

        // Configurar HTTP o HTTPS
        $secure = empty($https) ? isset($_SERVER['HTTPS']) : $https;
        
        // Configurar path
        $path = empty($path) ? SMP_WEB_ROOT : $path;

        // Setear cookie e iniciar sesion
        session_set_cookie_params($lifetime, $path, $domain, $secure, true);
        session_regenerate_id(TRUE);
        session_start();

        return $name;
    }

    protected static function encrypt($key, $password)
    {
        
    }

    // __ PUB
    /**
     * Guarda un valor en la sesión: $_SESSION[$key] = $value.<br />
     * Si la sesión no está iniciada, devuelve FALSE.
     * 
     * @param mixed $key Índice, puede ser un string o un entero.
     * @param mixed $value Valor, puede ser cualquier elemento serializable
     * (se recomienta emplear valores escalares o arrays, y evitar objetos).
     * @param boolean $sanitize Si es TRUE, sanitiza el valor antes de 
     * almacenarlo. (FALSE por defecto)
     * @return boolean TRUE si se almacenó el valor satisfactoriamente, 
     * FALSE si no.
     */
    public static function set($key, $value = NULL, $sanitize = FALSE)
    {
        if (self::status() == PHP_SESSION_ACTIVE) {
            if (isset($key) 
                && (is_string($key) || is_integer($key))
            ) {
                if ($sanitize) {
                    $_SESSION[$key] = Sanitizar::value($value);
                } else {
                    $_SESSION[$key] = $value;
                }
                return TRUE;
            }
        }
        
        return FALSE;
    }
	
	/**
     * Fija un Token aleatorio.  Se emplea en la función de autenticación.<br />
     * <b>IMPORTANTE</b>: NO emplearlo para generar un Token de Sesión nuevo!<br /> 
     * Usar el método getRandomToken() a este fin.
     * 
     * @see getRandomToken()
     * @param string $randToken Token aleatorio.
     * @return boolean TRUE si se almacenó exitosamente, FALSE si no.
     */
	public function setRandomToken($randToken)
    {
        return $this->t_setRandomToken($randToken);
    }
	
	public function setToken($sessionToken)
    {
        if ($this->isValid_sessionToken($sessionToken)) {
            $this->key = $sessionToken;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve un valor almacenado en la sesión: $_SESSION[$key].<br />
     * Si el valor no existe, devuelve NULL.<br />
     * El parámetro $sanitize determina si se sanitizará el valor previamente 
     * (TRUE) o no (FALSE, por defecto).<br />
     * En caso de error, se empleara una llamada del sistema para 
     * notificarlo.<br />
     * Si la sesión aún no está iniciada, se empleara una llamada del sistema 
     * para notificarlo.
     * 
     * @param mixed $key Índice, string o int.
     * @param boolean $sanitize TRUE para sanitizar el valor antes de 
     * devolverlo, FALSE para devolverlo sin sanitizar (por defecto).
     * @return mixed Valor almacenado en $_SESSION[$key] o NULL si dicho valor
     * no existe.  En caso de error, realiza una llamada del sistema para 
     * notificarlo.  Usar error_get_last() u otra para determinarlo.
     */
    public static function get($key, $sanitize = FALSE)
    {
        if (self::status() == PHP_SESSION_ACTIVE) {
            if (isset($key) 
                && (is_int($key) || is_string($key))
            ) {
                if (isset($_SESSION[$key])) {
                    if ($sanitize) {
                        return Sanitizar::glSESSION($key);
                    } else {
                        return $_SESSION[$key];
                    }
                } else {
                    return NULL;
                }
            } else {
                trigger_error(__METHOD__ . '(): El indice $key indicado '
                              . '($_SESSION["$key"]) no es valido');
            }
        } else {
            trigger_error(__METHOD__ . '(): La sesión aún no se ha inciado');
        }
    }
    
    /**
     * Devuelve el ID de la sesión actual.  Tener en cuenta que cada llamada<br />
     * a initiate() podría cambiar este ID.
     * 
     * @see initiate()
     * @return string ID de la sesión actual.
     */
    public static function ID()
    {
        return session_id();
    }

    /**
     * Devuelve el estado de la sesión.
     * 
     * @return mixed 
     * <ul>
     * <li><b>PHP_SESSION_DISABLED</b> si las sesiones están 
     * deshabilitadas.</li>
     * <li><b>PHP_SESSION_NONE</b> si las sesiones están habilitadas pero no 
     * existe ninguna aún.</li>
     * <li><b>PHP_SESSION_ACTIVE</b> si las sesiones están habilitadas y 
     * existe una.</li>
     * </ul>
     */
    public static function status()
    {
        return session_status();
    }

    /**
     * Destruye la sesión actual.
     */
    public static function terminate() 
    {
        // Destruir sesion de manera segura 
        // http://us3.php.net/manual/en/function.session-destroy.php
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $name = session_name();

        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie($name, '', time() - 4600,
                      $params["path"], $params["domain"],
                      $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }
    
    /**
     * Inicia o continúa una sesión.  Ante cada inicio, regenera el ID 
     * de la misma salvo que se especifique lo contrario.
     * 
     * @param boolean $dontChangeID Si es TRUE, el ID de la sesión no será 
     * regenerado.<br />
     * FALSE por defecto.
     * @return mixed TRUE si no ocurrió ningún error, FALSE si ocurrió.<br />
     * Si se inició una nueva sesión, se devuelve el nombre de ésta.<br />
     * <i>NOTA: el nombre de sesión no está aún implementado, se usa el nombre 
     * por defecto.</i>
     */
    public static function initiate($donChangeID = FALSE)
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            if (!$donChangeID) {
                session_regenerate_id(TRUE);
            }
            return TRUE;
        } elseif (session_status() == PHP_SESSION_NONE) {
            return self::begin();
        } else {
            return FALSE;
        }
    }
}
// --