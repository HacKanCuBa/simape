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
 * @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 * 
 *****************************************************************************/

/*
 * Este archivo debe tener todas las funciones, o bien
 * cargar desde aquí todos los archivos de funciones que 
 * correspondan
 */

// TODO
// - Documentar c/ funcion...
// - Crear archivos separados p/ categoria

if (!defined('CONFIG')) { require_once 'loadconfig.php'; }
if (!defined('FUNC_CRYPTO')) { require_once 'func_crypto.php'; }
if (!defined('FUNC_FORM')) { require_once 'func_form.php'; }
if (!defined('FUNC_SESSIONKEY')) { require_once 'func_sessionkey.php'; }
if (!defined('FUNC_FINGERPRINT')) { require_once 'func_fingerprint.php'; }
if (!defined('FUNC_DB')) { require_once 'func_db.php'; }
if (!defined('FUNC_FILE')) { require_once 'func_file.php'; }
if (!defined('FUNC_VALIDATIONS')) { require_once 'func_validations.php'; }
if (!defined('FUNC_PAGE')) { require_once 'func_page.php'; }

// << Funciones
// 

//
// -- Sanitizar entrada
function sanitizar_str($str) 
{
    return filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
}

function sanitizar_post($post) 
{
    return filter_input(INPUT_POST, "$post", FILTER_SANITIZE_STRING, 
                        FILTER_FLAG_STRIP_LOW);
}

function sanitizar_get($get) 
{
     return filter_input(INPUT_GET, "$get", FILTER_SANITIZE_STRING, 
                         FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
}

function sanitizar_server($server) 
{
    return filter_input(INPUT_SERVER, $server, FILTER_SANITIZE_STRING, 
                        FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
}

function sanitizar_array(&$array) 
{
    /**
     * Sanitiza un array uni o multidimensional.  Devuelve el mismo, con las
     * mismas dimensiones e índices, sanitizado.
     * 
     * @param mixed $array Array uni o multidimensional
     * @return mixed Array sanitizado
     */
    
    $sanitized = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            sanitizar_array($value);
        } else {
            $sanitized[$key] = sanitizar_str($value);
        }
    }
    
    return $sanitized;
}
// --
// 
// -- Otras
function msgbox($str) 
{
    echo('<script type="text/javascript"> alert("' . $str . '"); </script>');
}

function timestamp_get_today() 
{
    // Devuelve el dia actual en unixtimestamp
    // Esto es, a las 00hs
    //date_default_timezone_set('America/Argentina/Buenos_Aires');
    
    return strtotime(date('Y/m/d') . " 00:00:00");
}

function timestamp_get_thisSeconds($seconds) 
{
    return(((int)(time() / ($seconds))) * ($seconds));
}

function timestamp_get_thisMinutes($minutes) 
{    
    return timestamp_get_thisSeconds(60 * $minutes);
}

function timestamp_get_thisHours($hours) 
{    
    return timestamp_get_thisMinutes(60 * $hours);
}

function shorthand_to_bytes($val) 
{
    // http://us1.php.net/ini_get
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

function username_format($username) 
{
    /**
     * Formatea un string acorde al formato requerido para un nombre de usuario,
     * y devuelve el string formateado.
     * ADVERTENCIA: ¡no valida el string! emplead isValid_username para eso.
     * 
     * @param string $username String de nombre de usuario.
     * @return string String de nombre de usuario formateado.
     */
    if (isset($username)) {
        return strtolower($username);
    }
    
    return NULL;
}
// --
//  
// -- SESSION

function session_get($key) 
{
    if (isset($_SESSION[$key])) {
        return sanitizar_str($_SESSION[$key]);
    }
    
    return NULL;
}
function session_get_sessionkey() 
{
    $tkn = session_get('sessionkey_tkn');
    $key = session_get('sessionkey_key');
    $timestamp = session_get('sessionkey_timestamp');
    
    if(!empty($tkn) && !empty($key) && !empty($timestamp)) {
        return array('tkn' => $tkn, 
                     'key' => $key, 
                     'timestamp' => $timestamp
        );
    }
    
    return NULL;
}

function session_get_frmtkn() 
{
    return session_get('form_token');
}

function session_get_fingerprinttkn() 
{
    return session_get('fingerprint_token');
}

function session_get_errt() 
{
    return session_get('err_t');
}

function session_get_msg() 
{
    return session_get('msg');
}

function session_get_username() 
{
    return session_get('usuario');
}

function session_get_pagetkn() 
{
    return session_get('pagetkn');
}

function session_get_data() 
{
    if (isset($_SESSION['miscdata'])) {
        return sanitizar_array($_SESSION['miscdata']);
    }
    
    return NULL;
}

function session_get_data_dirty() 
{
    // En caso de haber guardado datos serializados
    // PELIGROSO!!!!
    if (isset($_SESSION['miscdata'])) {
        return $_SESSION['miscdata'];
    }
    return NULL;
}

function session_unset_sessionkey()
{
    unset($_SESSION['sessionkey_tkn'], 
            $_SESSION['sessionkey_key'], 
            $_SESSION['sessionkey_timestamp']);
}

function session_unset_frmtkn()
{
    unset($_SESSION['form_token']);
}

function session_unset_fingerprinttkn()
{
    unset($_SESSION['fingerprint_token']);
}

function session_unset_errt()
{
    unset($_SESSION['err_t']);
}

function session_unset_msg() {
    unset($_SESSION['msg']);
}

function session_unset_username() {
    unset($_SESSION['usuario']);
}

function session_unset_pagetkn() {
    unset($_SESSION['pagetkn']);
}

function session_unset_data() {
    unset($_SESSION['miscdata']);
}

function session_set_sessionkey($sessionkey) 
{
    $_SESSION['sessionkey_tkn'] = sessionkey_get_token($sessionkey);
    $_SESSION['sessionkey_key'] = sessionkey_get_key($sessionkey);
    $_SESSION['sessionkey_timestamp'] = sessionkey_get_timestamp($sessionkey);
}

function session_set_frmtkn($value) 
{
    $_SESSION['form_token'] = $value;
}

function session_set_fingerprinttkn($value) 
{
    $_SESSION['fingerprint_token'] = $value;
}

function session_set_errt($value) 
{
    $_SESSION['err_t'] = $value;
}

function session_set_msg($msg) 
{
    $_SESSION['msg'] = $msg;
}

function session_set_username($usuario) 
{
    $_SESSION['usuario'] = $usuario;
}

function session_set_pagetkn($value) 
{
    $_SESSION['pagetkn'] = $value;
}

function session_set_data($value) 
{
    // Para guardar datos miscelaneos
    $_SESSION['miscdata'] = $value;
}

function session_terminate() 
{
    // Destruir sesion de manera segura    
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

function session_begin($lifetime = 0, $path = '/', 
                       $domain = NULL, $https = NULL)
{
    /**
     * Crea una nueva sesion y devuelve el nombre de la misma
     * 
     * @param int $lifetime Duracion de la sesion, en segundos (0 implica hasta
     * que se cierre el navegador)
     * @param string $path Ruta en el dominio a la que tendra alcance la sesion
     * @param string $domain Dominio del sitio
     * @param bool $https Inidica si se usara htts (TRUE) o no (FALSE)
     * @return string Devuelve el nombre de la sesion creada
     * 
     */
    
   // Crear una cookie con nombre unico
   $name = substr(shuffle(range('a', 'z')), 0, 1) . get_random_token(9);
   //session_name($name);

   // Configurar domain
   $domain = isset($domain) ? $domain : sanitizar_server('SERVER_NAME');

   // Configurar HTTP o HTTPS
   $secure = isset($https) ? $https : isset($_SERVER['HTTPS']);

   // Setear cookie e iniciar sesion
   session_set_cookie_params($lifetime, $path, $domain, $secure, true);
   session_regenerate_id(TRUE);
   session_start();
   
   return $name;
}

function session_continue($name = NULL) 
{
    session_name($name);
    session_regenerate_id(TRUE);
    session_start();
       
    //die("cont");
}

function session_do() 
{
    session_begin();
    
    return;
    // despues veo...
    //die("sess:" . session_status());
    if (session_status() == PHP_SESSION_NONE) {
        session_begin();
    }
    else {
        session_continue();
    }
}
// --
// 
// -- POST
function post_encode($value) 
{
    return base64_encode($value);
}

function post_decode($value) 
{
    return base64_decode($value, TRUE);
}

function post_get_dirty($id) 
{
    if (isset($_POST[$id])) {
        return $_POST[$id];
    }
    
    return NULL;
}

function post_get($id) 
{
    return sanitizar_post($id);
}

function post_get_frmtkn() 
{
    return sanitizar_post('form_token');
}

function post_get_frmSelect($id = NULL) 
{
    return sanitizar_post('frm_select' . $id);
}

function post_get_frmSelect_dirty($id = NULL) 
{
    return $_POST['frm_select' . $id];
}

function post_get_frmCheckbox($id = NULL) 
{
    return sanitizar_post('frm_checkbox' . $id);
}

function post_get_frmCheckbox_dirty($id = NULL) 
{
    return post_get_dirty('frm_checkbox' . $id);
}

function post_get_frmText($id = NULL) 
{
    return sanitizar_post('frm_txt' . $id);
}

function post_get_frmText_dirty($id = NULL) 
{
    return post_get_dirty('frm_txt' . $id);
}

function post_get_frmPwd($id = NULL) 
{
    return sanitizar_post('frm_pwd' . $id);
}

function post_get_frmPwd_dirty($id = NULL) 
{
    return post_get_dirty('frm_pwd' . $id);
}

function post_get_frmBtn($id = NULL) 
{
    return sanitizar_post('frm_btn' . $id);
}

function post_get_frmBtn_dirty($id = NULL) 
{
    return post_get_dirty('frm_btn' . $id);
}

function post_unset_frmtkn() 
{
    unset($_POST['form_token']);
}
//
//function post_send($url, $post_array) {
//    /**
//     * (PHP 5)<br />
//     * Envia un array mediante POST a una URL especificada.
//     * @param string $url
//     * @param array $post_array
//     * @return Devuelve TRUE si tuvo éxito, FALSE si no.
//     * 
//     */
//    // Crear conexion
//    
//    $ch = curl_init();
//    
//    // Generar string de datos
//    $postString = http_build_query($post_array, '', '&');
//    
//    // Opciones de la conexion
//    curl_setopt($ch, CURLOPT_URL, $url);
//    curl_setopt($ch, CURLOPT_POST, 1);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
//    curl_setopt($curl, CURLOPT_RETURNTRANSFER, FALSE);
//    
//    // Resultado
//    $response = curl_exec($ch);
//    curl_close($ch);
//    
//    return $response;
//}
// --
//
// -- GET
function get_get_errt() 
{
    return sanitizar_get('err_t');
}

function get_get_action() 
{
    return sanitizar_get('accion');
}

function get_get_pagetkn() 
{
    return sanitizar_get('pagetkn');
}

function get_unset_errt() 
{
    unset($_GET['err_t']);
}
// --
// 
// -- SERVER
function server_get($svr_id) 
{
    return filter_input(INPUT_SERVER, $svr_id, FILTER_SANITIZE_STRING, 
                        FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
}
// --
// 
// -- Errores
function err_get_errt() 
{
    $session_errt = session_get_errt();
    $get_errt = get_get_errt();
    
    if (!empty($session_errt)) {
        return $session_errt;
    } elseif (!empty($get_errt)) {
        return $get_errt;
    } else {
        return NULL;
    }
}

function err_unset_errt() 
{
    session_unset_errt();
    get_unset_errt();
}
// --
//
// -- Guardar archivos
function file_store_db($db, $tabla, $campo, &$datos, $tipo = __SMP_FS_BASE64) 
{
    /**
     * Guarda un archivo en la base de datos.
     * El campo DEBE ser tipo BLOB.
     * 
     * Nota: Al leer el dato de la DB, para determinar si esta guardado en B64
     * o binario, tomar los primeros bytes y usar la funcion isValid_b64
     * 
     * @param mysqli $db Objeto MYSQLi de conexión
     * @param string $tabla Tabla donde se encuentra el campo
     * @param string $campo Campo donde se almacenara el archivo
     * @param mixed &$datos Datos que serán almacenados (por referencia para
     * evitar replica)
     * @param int $$tipo Constante que define la forma en que se 
     * guardará el archivo.  Puede ser __SMP_FS_BASE64 (por defecto)
     * o __SMP_FS_BINARY.  El primero implica guardar convertido en un
     * string BASE64.  El segundo, lo guarda como binario.
     * @return bool Devuelve TRUE si se guardó exitosamente, 
     * FALSE en caso contrario.
     */
    
    // *** Tener en cuenta que $datos puede ser enorme! ***
    // 
    // Sanitizar
    $table = db_sanitizar($db, $tabla);
    $field = db_sanitizar($db, $campo);
    if (!empty($db) && !empty($table) && !empty($field) && !empty($tipo)) {
        $data_size = strlen($datos);
        if ($data_size <= constant('FILE_MAXSTORESIZE')) {
            if ($tipo == constant('__SMP_FS_BASE64')) {
                if ($data_size <= (constant('FILE_MAXSTORESIZE') * 0.6)) {
                    // Esto puede demorar bastante!
                    $query = "INSERT INTO " . $table . " (" . $field 
                            . ") VALUES (" . base64_encode($datos) . ")";
                    $do_query = TRUE;
                }
            } elseif ($tipo == constant('__SMP_FS_BINARY')) {
                $query = "INSERT INTO " . $table . " (" . $field 
                        . ") VALUES (" . $datos . ")";
                $do_query = TRUE;
            }
        }
        
        if (isset($do_query)) {
            return mysqli_real_query($db, $query);
        }
    }
    
    return FALSE;
}
// --
// 
define('FUNCIONES', TRUE);
// --
?>