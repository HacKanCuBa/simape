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
 * Session::store('indice', $valor);
 * var_dump(Session::retrieve('indice'));
 * // Finalizar la sesión
 * Session::terminate();
 * // Para volver a usar la sesión, deberá llamarse nuevamente a 
 * // Session::initiate()
 * // Al instanciar la clase, la sesión se inicia.
 * $sess = new Session;
 * $sess->setPassword('1234');
 * // Almacena el valor sanitizado y encriptado.
 * $sess->storeEnc('indice', $valor, TRUE, TRUE);
 * $sess->retrieveEnc('indice');
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.21
 */
class Session
{
    use SessionToken;
    
    /**
     * Contraseña empleada por la función de encriptación.
     * @var string
     */
    private $password = NULL;
    
    /**
     * Sal criptográfica para la contraseña.
     * @var string
     */
    private $password_salt = NULL;
    
    /**
     * ID previo de la sesión de PHP.
     * @var string 
     */
    private $session_id_old;
    
    /**
     * Nombre previo de la sesión.
     * @var string
     */
    private $session_name_old;

    /**
     * Determina el tiempo de vida de la cookie.  0 implica 'hasta el
     * cierre del navegador'.
     */
    const COOKIE_LIFETIME = 0;
    
    // __ SPECIALS
    /**
     * Inicia una sesión si no estaba iniciada.<br />
     * Guarda el ID de la sesión si estaba iniciada, y luego lo regenera.
     * @see getID
     * @see getID_old
     */
    public function __construct() 
    {
        $this->session_id_old = self::getID();
        $this->session_name_old = self::getName();
        self::initiate();
    }
    // __ PRIV
    
    // __ PROT	
    /**
     * Crea una nueva sesion y devuelve el nombre de la misma.
     * 
     * @param boolean $dontChangeID [opcional] <br />
     * Si es TRUE, el ID de la sesión no será regenerado.  FALSE por defecto.
     * @param int $lifetime [opcional] <br/>
     * Duracion de la sesion, en segundos (0 implica hasta
     * que se cierre el navegador).
     * @param string $path [opcional] <br/>
     * Ruta en el dominio a la que tendra alcance la sesion.
     * @param string $domain [opcional] <br/>
     * Dominio del sitio.
     * @param bool $https [opcional] <br/>
     * Inidica si se usará https (TRUE) o no (FALSE).
     * @return string Devuelve el nombre de la sesion creada (NO IMPLEMENTADO).
     * 
     */
    protected static function begin($dontChangeID = FALSE, 
                                     $lifetime = NULL, 
                                     $path = NULL, 
                                     $domain = NULL, $https = NULL)
    {
        // Ideas:
        // http://security.stackexchange.com/questions/24177/starting-a-secure-php-session
        // http://www.wikihow.com/Create-a-Secure-Session-Managment-System-in-PHP-and-MySQL
        // 
        
        // Crear una cookie con nombre unico
        // El nombre debe empezar obligatoriamente con una letra minúscula
//        $length = 9;
//        $letters = range('a', 'z');
//        $name = $letters[mt_rand(0, count($letters) - 1)];
//        $name = 's';
//        $name .= substr(str_shuffle(md5(mt_rand()) . md5(mt_rand())), 0, $length);
//        session_name($name);

        // Configurar domain
        $domain = empty($domain) ? Sanitizar::glSERVER('SERVER_NAME') : $domain;

        // Configurar HTTP o HTTPS
        $secure = empty($https) ? boolval(Sanitizar::glSERVER('HTTPS')) : $https;
        
        // Configurar path
        $path = empty($path) ? SMP_WEB_ROOT : $path;
        
        // Configurar cookie lifetime
        $lifetime = empty($lifetime) ? self::COOKIE_LIFETIME : $lifetime;

        // Setear cookie e iniciar sesion
        session_set_cookie_params($lifetime, $path, $domain, $secure, true);
        if (!$dontChangeID) {
            session_regenerate_id(TRUE);
        }
        return session_start();

        //return $name;
    }
    
    // __ PUB
    /**
     * Inicia o continúa una sesión.  Ante cada inicio, regenera el ID 
     * de la misma salvo que se especifique lo contrario.
     * 
     * @param boolean $dontChangeID [opcional] <br />
     * Si es TRUE, el ID de la sesión no será regenerado.  FALSE por defecto.
     * @return mixed TRUE si no ocurrió ningún error, FALSE si ocurrió.<br />
     * Si se inició una nueva sesión, se devuelve el nombre de ésta.<br />
     * <i>NOTA: el nombre de sesión no está aún implementado, se usa el nombre 
     * por defecto.</i>
     */
    public static function initiate($dontChangeID = FALSE)
    {
        if (session_status() == PHP_SESSION_NONE) {
            return self::begin($dontChangeID);
        }
        
        return FALSE;
    }
    
    /**
     * Guarda un valor en la sesión: $_SESSION[$key] = $value.<br />
     * Si $password es asignado, encripta el valor (puede además asignarse
     * la sal criptográfica).<br />
     * Si la sesión no está iniciada, devuelve FALSE.
     * 
     * @param mixed $key Índice, puede ser un string o un entero.
     * @param mixed $value [opcional] <br/>
     * Valor, puede ser cualquier elemento serializable
     * (se recomienta emplear valores escalares o arrays, y evitar objetos).
     * @param boolean $dontSanitize [opcional] <br/>
     * Si es TRUE, NO sanitiza el valor antes de 
     * almacenarlo (FALSE por defecto).
     * @param string $password [opcional] <br/>
     * Contraseña de encriptación.
     * @param string $salt [opcional] <br/>
     * Sal criptográfica para la contraseña.
     * @return boolean TRUE si se almacenó el valor satisfactoriamente, 
     * FALSE si no.
     * @see setPassword().
     */
    public static function store($key,
                                 $value = NULL, 
                                 $dontSanitize = FALSE, 
                                 $password = NULL, 
                                 $salt = NULL
    ) {
        if (self::status() == PHP_SESSION_ACTIVE) {
            if (isset($key) 
                && (is_string($key) || is_integer($key))
            ) {
                if (!$dontSanitize) {
                    $value = Sanitizar::value($value);
                }
                
                if (!empty($password)) {
                    $value = Crypto::encrypt($value, $password, $salt);
                    if (empty($value)) {
                        return FALSE;
                    }
                }
                
                $_SESSION[$key] = $value;
                return TRUE;     
            }
        }
        
        return FALSE;
    }
    
    /**
     * Idem store(), pero siempre guarda el valor encriptado usando la contraseña 
     * proveída por setPassword() y la sal criptográfica por setPasswordSalt().
     * 
     * @param mixed $key Índice, puede ser un string o un entero.
     * @param mixed $value [opcional] <br/>
     * Valor, puede ser cualquier elemento serializable
     * (se recomienta emplear valores escalares o arrays, y evitar objetos).
     * @param boolean $sanitize [opcional] <br/>
     * Si es TRUE, sanitiza el valor antes de 
     * almacenarlo (FALSE por defecto).
     * @return boolean TRUE si se almacenó el valor satisfactoriamente, 
     * FALSE si no.
     * @see setPassword()
     * @see setPasswordSalt()
     */
    public function storeEnc($key, $value = NULL, $sanitize = FALSE)
    {
        if (isset($key)) {
            return self::store($key, $value, $sanitize, $this->password, 
                               $this->password_salt);
        }
        
        return FALSE;
    }

    /**
     * Devuelve un valor almacenado en la sesión: $_SESSION[$key].<br />
     * Si el valor no existe, devuelve NULL.<br />
     * El parámetro $sanitize determina si se sanitizará el valor previamente 
     * (TRUE) o no (FALSE, por defecto).<br />
     * Si se encuentra un valor encriptado y $password tiene un valor asignado, 
     * se desencriptará.<br />
     * En caso de error, se empleara una llamada del sistema para 
     * notificarlo.<br />
     * Si la sesión aún no está iniciada, se empleara una llamada del sistema 
     * para notificarlo.<br />
     * 
     * @param mixed $key Índice, string o int.
     * @param boolean $sanitize [opcional] <br/>
     * TRUE para sanitizar el valor antes de 
     * devolverlo, FALSE para devolverlo sin sanitizar (por defecto).
     * @param string $password [opcional] <br/>
     * Contraseña de encriptación.
     * @param string $salt [opcional] <br/>
     * Sal criptográfica para la contraseña.
     * @return mixed Valor almacenado en $_SESSION[$key] o NULL si dicho valor
     * no existe.<br />
     * En caso de error, realiza una llamada del sistema para 
     * notificarlo.  Usar error_get_last() u otra para determinarlo.
     */
    public static function retrieve($key, 
                                    $sanitize = FALSE, 
                                    $password = NULL, 
                                    $salt = NULL)
    {
        if (self::status() == PHP_SESSION_ACTIVE) {
            if (isset($key) 
                && (is_int($key) || is_string($key))
            ) {
                if (isset($_SESSION[$key])) {
                    $retVal = $_SESSION[$key];
                    if (Crypto::isEncrypted($retVal)) {
                        if (!empty($password)) {
                            $retVal = Crypto::decrypt($retVal, $password, $salt);
                            if (empty($retVal)) {
                                trigger_error(__METHOD__ . '(): Ha ocurrido un '
                                    . 'error al tratar de desencriptar el '
                                    . 'valor solicitado.  Clave incorrecta?',
                                    E_USER_WARNING);
                                
                                return NULL;
                            }
                        }
                    }
                    
                    if ($sanitize) {
                        $retVal = Sanitizar::value($retVal);
                    }
                    
                    return $retVal;
                } else {
                    return NULL;
                }
            } else {
                trigger_error(__METHOD__ . '(): El indice $key indicado '
                              . '($_SESSION["$key"]) no es valido', 
                              E_USER_WARNING);
            }
        } else {
            trigger_error(__METHOD__ . '(): La sesión aún no se ha inciado');
        }
    }
    
    /**
     * Idem retrieve(), pero siempre devuelve desencriptado 
     * (si estaba encriptado) usando la contraseña proveída por setPassword().
     * 
     * @param mixed $key Índice, string o int.
     * @param boolean $sanitize [opcional]<br />
     * TRUE para sanitizar el valor antes de 
     * devolverlo, FALSE para devolverlo sin sanitizar (por defecto).
     * @return mixed Valor almacenado en $_SESSION[$key] o NULL si dicho valor
     * no existe.<br />
     * En caso de error, realiza una llamada del sistema para 
     * notificarlo.  Usar error_get_last() u otra para determinarlo.
     */
    public function retrieveEnc($key, $sanitize = FALSE)
    {
        if (isset($this->password)) {
            return self::retrieve($key, $sanitize, $this->password);
        }
        
        return FALSE;
    }
    
    /**
     * Elimina el valor indicado por $key, es decir, elimina $_SESSION[$key].
     * 
     * @param mixed $key Índice del valor a eliminar.
     */
    public static function remove($key)
    {
        unset($_SESSION[$key]);
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
     * Devuelve el nombre actual de la sesión.  
     * Tener en cuenta que cada llamada<br />
     * a initiate() podría cambiarlo.
     * 
     * @see initiate()
     * @return string Nombre actual de la sesión.
     */
    public static function getName()
    {
        return session_name();
    }
    
    /**
     * Devuelve el nombre anterior de la sesión. Tener en cuenta que 
     * initiate() NO almacena el nombre anterior, solo el constructor del objeto
     * lo hace.
     * 
     * @return string Nombre anterior de la sesión.
     */
    public function getName_old()
    {
        if (isset($this->session_name_old)) {
            return $this->session_name_old;
        }
        
        return '';
    }

    /**
     * Devuelve el ID actual de la sesión.  Tener en cuenta que cada llamada<br />
     * a initiate() podría cambiar este ID.
     * 
     * @see initiate()
     * @return string ID actual de la sesión.
     */
    public static function getID()
    {
        return session_id();
    }
    
    /**
     * Devuelve el ID de la sesión anterior si hay.  Tener en cuenta que 
     * initiate() NO almacena el ID anterior, solo el constructor del objeto lo 
     * hace.
     * 
     * @return string ID de sesión anterior.
     */
    public function geID_old()
    {
        if (isset($this->session_id_old)) {
            return $this->session_id_old;
        }
        
        return '';
    }

    /**
     * Almacena una contraseña que se emplea para encriptar los valores 
     * solicitados (solo hasta que el objeto se destruya).
     * 
     * @param string $password Contraseña.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setPassword($password)
    {
        if (!empty($password) && is_string($password)) {
            $this->password = $password;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Almacena la Sal criptográfica para la contraseña empleada para encriptar
     * los valores solicitados.
     * 
     * @param string $salt Sal criptográfica para la contraseña.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setPasswordSalt($salt)
    {
        if (!empty($salt) && is_string($salt)) {
            $this->password_salt = $salt;
            return TRUE;
        }
        
        return FALSE;
    }
}
// --