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
 * Esta clase maneja todo lo referido al usuario:
 * - Autenticacion
 * - Permisos
 * - Creacion nuevo
 * - Cambio de datos
 * - etc
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.83
 */
class Usuario extends Empleado
{    
    // SessionToken ya incorpora UID.
    use SessionToken, Passwordt {        
        SessionToken::authenticateToken as protected SessionToken_authenticateToken;
        
        Passwordt::authenticateToken as protected Password_authenticateToken;
        
        SessionToken::authenticateToken insteadof Passwordt;
        
        // SessionToken
        retrieve_fromDB_TokenId as protected;
        retrieve_tblToken as protected;
        authenticateToken as protected;
        
        // Passwordt
        retrieve_fromDB_PwdRestore as protected;
        store_inDB_PwdRestore as protected;
    }
    
    const TOKEN_PASSWORDRESTORE = TRUE;
    const TOKEN_SESSION = FALSE;

    /**
     * El método authenticateSession fija el valor de esta variable, 
     * que determina si el usuario está loggeado (TRUE) o no (FALSE).
     * @var boolean
     */
    private $isLoggedIn;

//    /**
//     * Tabla Usuario de la DB.
//     * @var array
//     */
//    protected $tblUsuario = array ('UsuarioId' => 0,
//                                'EmpleadoId' => 0,
//                                'UsuarioPerfilId' => 0,
//                                'TokenId' => 0,
//                                'Nombre' => '',
//                                'UID' => '',
//                                'PasswordSalted' => '',
//                                'PasswordTimestamp' => 0,
//                                'Activo' => FALSE,
//                                'PrivKey' => '',
//                                'PubKey' => '',
//                                'CreacionTimestamp' => 0,
//                                'ModificacionTimestamp' => 0
//                                );
    
    protected $UsuarioId = 0;
    protected $UsuarioNombre = '';
    protected $Activo = FALSE;
    protected $PrivKey = NULL;
    protected $PubKey = NULL;
    protected $UsuarioCreacionTimestamp = 0;
    protected $UsuarioModificacionTimestamp = 0;
    
//    /**
//     * Tabla UsuarioPerfil de la DB.
//     * @var array
//     */
//    protected $UsuarioPerfil = array('UsuarioPerfilId' => 0,
//                                     'Nombre' => '',
//                                     'Timestamp' => 0
//                                    );
    
    protected $UsuarioPerfilId = 0;
    protected $UsuarioPerfilNombre = '';
    protected $UsuarioPerfilTimestamp = 0;
    
    /**
     * Indica si el usuario es nuevo o ya existe.  Importante para determinar
     * si se debe cambiar CreacionTimestamp.
     * @var boolean
     */
    protected $esNuevoUsuario;
    
    /**
     * Determina si al grabar en la DB se escribirá el ID de la tabla (TRUE)
     * o no (FALSE, por defecto) al crear un nuevo Usuario, dado que la DB
     * maneja este valor automáticamente.
     * @var boolean
     */
    protected $write_id = FALSE;
   
    // Metodos
    // __ SPECIALS
    /**
     * Busca en la DB si ya existe un Usuario con los datos pasados:
     * UsuarioId, Nombre o UID (en ese orden de prioridad)<br />
     * Si lo encuentra, recupera todos los datos desde la DB.  No almacena los 
     * datos pasados.<br />
     * Si no lo encuentra, considera que se está creando un nuevo Usuario y 
     * almacena los datos pasados.<br />
     * <i>No es recomendable crear un nuevo usuario con UsuarioId manual,
     * dado que la DB genera uno automáticamente.</i>
     * 
     * @param string $Nombre Nombre de usuario.
     * @param string $UID UID del usuario.
     * @param int $UsuarioId Id de la tabla Usuario.
     */
    function __construct($Nombre = NULL, $UID = NULL, $UsuarioId = NULL) 
    {        
        // Es necesario incializar passwordcost!
        $this->setPasswordCost();
        
        // Búsqueda
        $this->setNombre($Nombre);
        $this->setUID($UID);
        $this->setUsuarioId($UsuarioId);
        $this->esNuevoUsuario = !$this->retrieve_fromDB();
        
        // Búsqueda de empleado
        parent::__construct(NULL, NULL, $this->EmpleadoId);
    }
    // __ PRIV
    
    // __ PROT    
    /**
     * Valida un string y determina si cumple las restricciones impuestas 
     * en la configuración sobre los nombres de usuario.
     * 
     * @param string $username Nombre de usuario.
     * @return boolean TRUE si el string es un nombre de usuario válido, 
     * FALSE si no lo es.
     */
    protected static function isValid_username($username) 
    {
        if (!empty($username) 
            && is_string($username)
            && (strlen($username) <= constant('SMP_USRNAME_MAXLEN')) 
            && (strlen($username) >= constant('SMP_USRNAME_MINLEN'))
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
//    /**
//     * Valida y determina si se trata de un objeto Empleado.
//     * 
//     * @param mixed $empleado
//     * @return boolean TRUE si se trata de un objeto Empleado, FALSE si no.
//     */
//    protected static function isValid_Empleado($empleado)
//    {
//        if (!empty($empleado) && is_a($empleado, 'Empleado')) {
//            return TRUE;
//        }
//        
//        return FALSE;
//    }
    
//    /**
//     * Valida y determina si se trata de un objeto DB.
//     * 
//     * @param mixed $db
//     * @return boolean TRUE si se trata de un objeto DB, FALSE si no.
//     */
//    protected static function isValid_DB($db)
//    {
//        if (!empty($db) && is_a($db, 'DB')) {
//            return TRUE;
//        }
//        
//        return FALSE;
//    }
//    
//    /**
//     * Valida y determina si se trata de un objeto Password.
//     * 
//     * @param mixed $password
//     * @return boolean TRUE si se trata de un objeto Password, FALSE si no.
//     */
//    protected static function isValid_Password($password)
//    {
//        if (!empty($password) && is_a($password, 'Password')) {
//            return TRUE;
//        }
//        
//        return FALSE;
//    }
    
    /**
     * Verifica si todos los datos están en orden para guardar en la DB.
     * 
     * @return boolean TRUE si los datos están en orden, FALSE si no.
     */
    protected function isDataReady() 
    {        
        if (!empty($this->EmpleadoId)
            && !empty($this->UsuarioPerfilId)
            && !empty($this->TokenId)
            && !empty($this->UsuarioNombre)
            && !empty($this->uid)
            && !empty($this->passwordEC)
            && !empty($this->passwordModificationTimestamp)
            && !empty($this->UsuarioCreacionTimestamp)
            && !empty($this->UsuarioModificacionTimestamp)    
        ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Busca en la DB todos los datos del usuario, usando como parámetro
     * UsuarioId, UID o Nombre.
     * 
     * @param mixed (int) UsuarioId, (string) UID o (string) Nombre.
     * @return mixed Todos los valores en un array, FALSE si se produjo
     * un error.
     */
    protected static function retrieve_fromDB_tbl($searchParam) {
        if (!empty($searchParam)) {
            $db = new DB;
            if (DB::isValid_TblId($searchParam)) {
                $db->setQuery('SELECT * FROM Usuario WHERE UsuarioId = ?');
                $db->setBindParam('i');
            } elseif (self::isValid_UID($searchParam)) {
                $db->setQuery('SELECT * FROM Usuario WHERE UID = ?');
                $db->setBindParam('s');
            } elseif (self::isValid_username($searchParam)) {
                $db->setQuery('SELECT * FROM Usuario WHERE Nombre = ?');
                $db->setBindParam('s');
            } else {
                return FALSE;
            }

            $db->setQueryParams($searchParam);
            $db->queryExecute();
            $result = $db->getQueryData();
            unset($db);
            return $result;
        }
        
        return FALSE;
    }
    
    /**
     * Fija el valor del timestamp de creación de usuario (CreacionTimestamp), 
     * y el de la contraseña (PasswordTimestamp).  Al tratarse de un usuario 
     * nuevo, deben tener el mismo valor.
     * 
     * @param int $NuevoTimestamp CreacionTimestamp.
     * @return TRUE si tuvo éxito, FALSE si no.
     */
    protected function setCreacionTimestamp($NuevoTimestamp)
    {
        if (is_int($NuevoTimestamp)) {
            $this->UsuarioCreacionTimestamp = $NuevoTimestamp;
            $this->passwordModificationTimestamp = $NuevoTimestamp;
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Genera y almacena un Token del tipo indicado.
     * @param boolean $type Indica el tipo de token a generar: 
     * TOKEN_PASSWORDRESTORE (TRUE) o TOKEN_SESSION (FALSE).
     * @return boolean TRUE si se generó exitosamente, FALSe si no.
     * @access protected
     */
    protected function generateToken($type) {
        if (isset($type)
            && !empty($this->randToken)
            && !empty($this->timestamp) 
            && !empty($this->uid)
        ) {
            $token = $type ? 
                        $this->tokenMake($this->randToken,
                                            SMP_TKN_PWDRESTORE,
                                            $this->timestamp,
                                            SMP_PASSWORD_RESTORETIME,
                                            $this->uid) : 
                        $this->tokenMake($this->randToken,
                                            SMP_TKN_SESSIONKEY,
                                            $this->timestamp,
                                            SMP_SESSIONKEY_LIFETIME,
                                            $this->uid); 
            if(self::isValid_token($token)) {
                $this->token = $token;
                return TRUE;
            }
        }
               
        return FALSE;
    }
    // __ PUB
    /**
     * Almacena en el objeto el ID de la tabla Usuario.<br />
     * <i>No es recomendable crear un nuevo usuario con UsuarioId manual, 
     * dado que la DB genera uno automáticamente.</i>
     * 
     * @param int $UsuarioId Identificador de la tabla Usuario.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setUsuarioId($UsuarioId)
    {
        if (DB::isValid_TblId($UsuarioId)) {
            $this->UsuarioId = $UsuarioId;
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function setNombre($NuevoNombreUsuario) {
        if ($this->isValid_username($NuevoNombreUsuario)) {
            $this->UsuarioNombre = strtolower(trim($NuevoNombreUsuario));
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Almacena una nueva contraseña, como objeto o string.<br />
     * Si es string, y determina que es una contraseña encriptada, 
     * la almacena como tal.  Si no, como texto plano.<br />
     * Si es un objeto, reemplaza todos los valores propios por los del objeto.
     * @param mixed $password Password como objeto o string
     * @param boolean $requireStrong TRUE para requerirle al Plaintext que 
     * sea una contraseña <i>fuerte</i>.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.  
     * Para el caso del objeto, siempre devuelve TRUE.
     */
    public function setPassword($password, 
                                    $requireStrong = SMP_PASSWORD_REQUIRESTRONG) 
    {
        $retval = FALSE;
        
        if (is_a($password, 'Password')) {
            $this->setPasswordPlaintext($password->getPasswordPlaintext(), $requireStrong);
            $this->setPasswordEncrypted($password->getPasswordEncrypted());
            $this->setRandomToken($password->getRandomToken());
            $this->setTimestamp($password->getTimestamp());
            $this->setToken($password->getToken());
            $this->setModificationTimestamp($password->getModificationTimestamp());
            $retval = TRUE;
        } elseif (is_string($password)) {
            $retval = $this->setPasswordEncrypted($password) ?: 
                        $this->setPasswordPlaintext($password, $requireStrong);
        }
        
        return $retval;
    }
  
    /**
     * Fija el estado, que indica si el usuario está activado 
     * (puede loggearse y operar) o no.
     * @param boolean $NuevoActivo TRUE para Activo, FALSE para No Activo.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setActivo($NuevoActivo) {
        if (!empty($NuevoActivo) 
            && is_bool($NuevoActivo)
        ) {
            $this->Activo = $NuevoActivo;
            return TRUE;
        }
        return FALSE;
    }
    
//    /**
//     * Almacena el UID indicado como nuevo UID del usuario.  Acepta objeto UID 
//     * o string.<br />
//     * El objeto debe contener un UID válido para ser aceptado.<br />
//     * Al crear un usuario nuevo, el sistema creará un nuevo UID si no se 
//     * almacenó uno previamente, por lo que no es necesario llamar a éste 
//     * método a tal fin.
//     * 
//     * @param mixed $NuevoUID (UID) o (string) Nuevo UID.
//     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
//     */
//    public function setUID($NuevoUID) 
//    {
//        $retval = FALSE;
//    
//        if (self::isValid_UID($NuevoUID) && !empty($NuevoUID->get())) {
//            $this->uid = $NuevoUID;
//            $retval = TRUE;
//        } elseif (UID::isValid($NuevoUID)) {
//            $this->uid = new UID;
//            $this->uid->set($NuevoUID);
//            $retval = TRUE;
//        }
//        
//        return $retval;
//    }
    // --
    // Get    
    /**
     * Devuelve el nombre de usuario, si hay.
     * 
     * @return string Nombre de usuario o string vacío
     */
    public function getNombre() {
        return $this->UsuarioNombre;
    }
    
    /**
     * Devuelve el estado del usuario que indica si el usuario está activado 
     * (puede loggearse y operar) o no: TRUE para Activo, FALSE para No Activo
     * 
     * @return boolean Estado del usuario.
     */
    public function getActivo()
    {
        return $this->Activo;
    }
    
    /**
     * Devuelve el identificador de la tabla Usuario, si hay.
     * 
     * @return int Identificador de la tabla Usuario o 0.
     */
    public function getUsuarioId()
    {
        return $this->UsuarioId;
    }
    
    /**
     * Devuelve el identificador de la tabla UsuarioPerfil, si hay.
     * 
     * @return int Identificador de la tabla UsuarioPerfil o 0.
     */
    public function getPerfilId()
    {
        return $this->UsuarioPerfilId;
    }
    
    /**
     * Devuelve el valor del timestamp de creación de la tabla Usuario, si hay.
     * 
     * @return int Valor del timestamp de creación de la tabla Usuario o 0.
     */
    public function getCreacionTimestamp()
    {
        return $this->UsuarioCreacionTimestamp;
    }
    
    /**
     * Devuelve el valor del timestamp de modificación de la tabla Usuario, 
     * si hay.
     * 
     * @return int Valor del timestamp de modificación de la tabla Usuario o 0.
     */
    public function getModificacionTimestamp()
    {
        return $this->UsuarioModificacionTimestamp;
    }
    
//    /**
//     * Devuelve el objeto password almacenado.
//     * 
//     * @return Password Password.
//     */
//    public function getPassword()
//    {
//        return $this->password;
//    }
    
//    /**
//     * Devuelve la contraseña del usuario en texto plano almacenada, 
//     * o FALSE si no hay ninguna.
//     * 
//     * @return string|FALSE Contraseña en texto plano.
//     */
//    public function getPasswordPlaintext()
//    {
//        return $this->Password_getPlaintext();
//    }
    
//    /**
//     * Devuelve la contraseña encriptada del usuario, o FALSE si no hay.
//     * 
//     * @return string|FALSE La contraseña encriptada.
//     */
//    public function getPasswordEncrypted() 
//    {
//        return $this->Password_getPasswordEncrypted();
//    }
    
//    /**
//     * Devuelve el Token de Restablecimiento de contraseña.
//     * @return string Token de restablecimiento de contraseña o string vacío.
//     */
//    public function getPasswordRestoreToken()
//    {
//        return $this->getToken();
//    }

    /**
     * Recupera de la DB todos los datos del usuario, siempre y cuando se haya
     * establecido previamente el ID, Nombre o UID del mismo (la búsqueda se 
     * realiza en ese orden de prioridad).<br />
     * <b>ATENCIÓN:</b> ¡se sobreescribirán los datos almacenados respecto del 
     * usuario!
     * 
     * @return boolean TRUE si se recuperó correctamente, FALSE si no.
     */
    public function retrieve_fromDB()
    {
        $searchParams = array($this->UsuarioId, 
                            $this->UsuarioNombre, 
                            $this->uid);
        foreach ($searchParams as $searchP) {
            $usuario = self::retrieve_fromDB_tbl($searchP);
            if (is_array($usuario) && !empty($usuario)) {
                //$this->Usuario = $usuario;
                list($this->UsuarioId, 
                        $this->EmpleadoId, 
                        $this->UsuarioPerfilId, 
                        $this->TokenId, 
                        $this->UsuarioNombre, 
                        $this->uid, 
                        $this->passwordEC, 
                        $this->passwordModificationTimestamp, 
                        $this->Activo, 
                        $this->PrivKey, 
                        $this->PubKey, 
                        $this->UsuarioCreacionTimestamp, 
                        $this->UsuarioModificacionTimestamp) = array_values($usuario);
//                $this->password->setPasswordEncrypted($usuario['PasswordSalted']);
//                $this->password->setModificationTimestamp($usuario['PasswordTimestamp']);
                $this->esNuevoUsuario = FALSE;
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Guarda el usuario en la DB.  Devuelve TRUE si tuvo éxito, 
     * FALSE si no.
     * 
     * @return boolean TRUE si tuvo éxito, FALSE si no.
     */
    public function store_inDB() {
        
        
        return FALSE;
    }
    
    /**
     * Inicia una nueva sesión de usuario, esto es, realiza el log in al 
     * sistema.<br />
     * <i>Se requiere el UID del usuario antes de llamar a este método.</i><br />
     * <ul>
     * <li>Almacena en $_SESSION el nombre de usuario;</li>
     * <li>Genera una nueva llave de sesión, almacena las partes 
     * correspondientes en la DB y $_SESSION;</li>
     * <li>Genera y almacena el Fingerprint Token en la DB.</li>
     * </ul>
     * @return boolean TRUE si se inició correctamente, 
     * FALSE si alguna instancia de las mencionadas falló.  
     * De ser así, no prosigue con las siguientes, que se ejecutan en el orden 
     * indicado.
     * @access public
     */
    public function sesionIniciar()
    {
        if (!(isset($this->isLoggedIn) && is_bool($this->isLoggedIn))) {
            $this->isLoggedIn = FALSE;

            // Guardo el nombre de usuario en $_SESSION
            Session::store(SMP_SESSINDEX_USERNAME, $this->getNombre());

            // Genero nuevo sessionkey
            $this->generateRandomToken();
            $this->generateTimestamp();
            !empty($this->uid) ?: $this->retrieve_fromDB();
            !empty($this->TokenId) ?: $this->retrieve_fromDB_TokenId($this->UsuarioNombre);
            if($this->generateToken(self::TOKEN_SESSION)) {
                if($this->store_inDB_SessionToken()) {
                    Session::store(SMP_SESSINDEX_SESSIONKEY_TOKEN, 
                                                        $this->getToken());
                    // en este punto la sesión está iniciada
                    $this->isLoggedIn = TRUE;   // podria considerarse o no el fingTkn...
                    //
                    // Fingerprint
                    $fingerprint = new Fingerprint;
                    $fingerprint->setMode(Fingerprint::MODE_USEIP);
                    $fingerprint->generateToken();
                    // Guardarlo en DB
                    $fingerprint->setTokenId($this->getTokenId());
                    $fingerprint->store_inDB(); //acá lo considero, pero si falla, como proceder?
                    unset($fingerprint);
                }
            }
        }
        
        return $this->isLoggedIn;
    }
    
    /**
     * Cierra la sesión del usuario, es decir, hace logout:
     * <ul>
     * <li>Remueve el token de sesión de $_SESSION,</li>
     * <li>Anula los tokens y el timestamp,</li>
     * <li>Escribe en la DB.</li>
     * </ul>
     * @access public
     */
    public function sesionFinalizar()
    {
        Session::remove(SMP_SESSINDEX_SESSIONKEY_TOKEN);
        $this->token = NULL;
        $this->isLoggedIn = FALSE;
        !empty($this->TokenId) ?: $this->retrieve_fromDB_TokenId($this->UsuarioNombre);
        $this->remove_fromDB_PwdRestore();
    }
    
    /**
     * Autentica una sesión de usuario, incluyendo el Fingerprint.  
     * En caso que éste falle, cerrará la sesión como medida de seguridad.
     * 
     * @return boolean TRUE si la sesión es auténtica, es decir, el usuario 
     * está loggeado; FALSE si no.
     */
    public function authenticateSession()
    {
        if (isset($this->isLoggedIn) && is_bool($this->isLoggedIn)) {
            return $this->isLoggedIn;
        } else {
            $this->isLoggedIn = FALSE;

            if ($this->setToken(Session::retrieve(SMP_SESSINDEX_SESSIONKEY_TOKEN))) {
                if(!empty($this->uid) ?: $this->retrieve_fromDB()
                    && !empty($this->TokenId) ?: $this->retrieve_fromDB_TokenId($this->UsuarioNombre)
                ) {
                    $fingerprint = new Fingerprint;
                    $fingerprint->setTokenId($this->TokenId);
                    $fingerprint->retrieve_fromDB();
                    if ($fingerprint->authenticateToken()) {
                        if ($this->retrieve_fromDB_SessionToken()) {
                            $this->isLoggedIn = $this->SessionToken_authenticateToken();
                        }
                    } else {
                        // fallo el fingerprint
                        $this->sesionFinalizar();
                    }
                }
            }
        }
        
        return $this->isLoggedIn;
    }
    
    /**
     * Realiza el procedimiento para restablecer la contraseña:
     * <ul>
     * <li>Genera los tokens,</li>
     * <li>Almacena en la DB,</li>
     * <li>Envía email</li>
     * </ul>
     * <i>IMPORTANTE: Es posible que se recuperen todos los datos del usuario 
     * desde la DB, pisando los existentes si hubieran.</i>
     * @return TRUE si tuvo éxito, FALSE si no.
     */
    public function passwordRestore()
    {
        if((!empty($this->uid) ?: $this->retrieve_fromDB())
                && (!empty($this->TokenId) ?: $this->retrieve_fromDB_TokenId($this->UsuarioNombre))
        ) {
            $this->generateRandomToken();
            $this->generateTimestamp();
            if ($this->generateToken(self::TOKEN_PASSWORDRESTORE)
                    && $this->store_inDB_PwdRestore()
            ) {
                $passrestore_url = Sanitizar::glSERVER('HTTPS') ? 'https://' : 'http://';
                $passrestore_url .= Sanitizar::glSERVER('SERVER_NAME') 
                                    . '/nav.php' 
                                    . '?accion=' . SMP_RESTOREPWD 
                                    . '&username=' . $this->UsuarioNombre 
                                    . '&passRestoreToken=' . $this->password->getToken();

                // Enviar email
                $email = new Email;

                $email->setFrom('SiMaPe', SMP_EMAIL_FROM);
                $email->addAddress(/*$this->Email*/"hackan@gmail.com");/*!!!!!!!!!!!!!!!!!!!!!!!!*/
                $email->setSubjet('Restablecimiento de contraseña para SiMaPe');
                $email->setBody("<!DOCTYPE html>"
                . "\n<html lang='es-AR'>"
                . "\n<head>" 
                . "\n\t<meta content='text/html; charset=". $email->getCharset() . "' http-equiv='Content-Type' />"
                . "\n</head>"
                . "\n<body style='background:#e0e0e0;'>"
                . "\n\t<h2 style='text-align: center;'>"
                . "\n\t\t<span style='font-family:courier new,courier,monospace;'>"
                        . "Sistema Integrado de Manejo de Personal</span>"
                . "\n\t</h2>"
                . "\n\t<p><span style='font-family:courier new,courier,monospace;'>"
                        . "Ha solicitado restablecer su contrase&ntilde;a en "
                        . "SiMaPe, y por eso recibe este correo.&nbsp; Si no "
                        . "realiz&oacute; esta acci&oacute;n, puede omitir "
                        . "este mensaje sin m&aacute;s, su cuenta sigue "
                        . "estando segura</span>"
                . "\n\t</p>"
                . "\n\t<p><span style='font-family:courier new,courier,monospace;'>"
                        . "Para continuar con el proceso, dir&iacute;jase a "
                        . "este enlace (o bien copie y pegue en su navegador):"
                        . "<br />"
                        . "<a href='" . $passrestore_url . "'>" . $passrestore_url . "</a></span>"
                . "\n\t</p>"
                . "\n\t<p><span style='font-family:courier new,courier,monospace;'>"
                        . "Tenga en cuenta que el v&iacute;nculo arriba "
                        . "indicado caducar&aacute; a los " 
                        . (SMP_PASSWORD_RESTORETIME / 60)  
                        . " minutos de recibido este email (exactamente a las " 
                        . strftime('%H:%M:%S del %d de %B del %G' , 
                                    $this->password->getTimestamp()) 
                        . "), y deber&aacute; solicitar restablecer su "
                        . "contrase&ntilde;a nuevamente.</span>"
                . "\n\t</p>"
                . "\n\t<p>"
                . "\n\t<span style='font-family:courier new,courier,monospace;'>"
                        . "Atte.:<br />"
                        . "SiMaPe</span>"
                . "\n\t</p>"
                . "\n\t<p><span style='font-family:courier new,courier,monospace;'>"
                        . "<em><small>P. D.: este mensaje ha sido generado "
                        . "autom&aacute;ticamente.&nbsp; Por favor, no "
                        . "responder al mismo dado que ninguna persona lo "
                        . "leer&aacute;.</small></em></span>"
                . "\n\t</p>"
                . "\n</body>"
                . "\n</html>");

                return $email->send();
            }
        }
        
        return FALSE;
    }
    
    /**
     * Autentica un token de restablecimiento de contraseña.
     * Debe fijarse primero el mismo mediante setToken().
     * @see setToken
     * @return boolean TRUE si el token de restablecimiento de contraseña es 
     * válido, FALSE si no.
     * @access public
     */
    public function authenticatePasswordRestore()
    {
        if((!empty($this->uid) ?: $this->retrieve_fromDB())
                && (!empty($this->TokenId) ?: $this->retrieve_fromDB_TokenId($this->UsuarioNombre))
        ) {
            if ($this->retrieve_fromDB_PwdRestore()) {
                return $this->Password_authenticateToken();
            }
        }
    }
}