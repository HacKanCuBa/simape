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
 * Maneja la creacion de contraseñas y la autenticación.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.63
 */
class Password
{
    use Passwordt, SessionToken {
        Passwordt::authenticateToken insteadof SessionToken;
        Passwordt::generateToken insteadof SessionToken;
    }
    
    // Metodos
    // __ SPECIALS
    /**
     * Crea un nuevo objeto Password.  Si recibe el parámetro, lo almacena como 
     * una contraseña en texto plano, si la misma cumple las 
     * restricciones (es decir, es válida), y prepara para encriptarla.
     * Llamar a encryptPassword() para encriptarla.
     * 
     * @see encryptPassword()
     * @param string $passwordPT Contraseña en texto plano
     * @param string $passwordEC Contraseña encriptada.
     */
    public function __construct($passwordPT = NULL, $passwordEC = NULL)
    {
        $this->setPasswordCost();
        $this->setPasswordPlaintext($passwordPT);
        $this->setPasswordEncrypted($passwordEC);
    }
    // __ PRIV
    
    // __ PROT 

    // __ PUB
    /**
     * Almacena en la DB el password encriptado guardado en el objeto, si lo hay.
     * 
     * @param string $username Nombre de usuario al que le pertenece el 
     * password encriptado.
     * @return boolean TRUE si se almacenó en la DB exitosamente, 
     * FALSE en caso contrario.
     */
    public function store_inDB($username) 
    {
        if (!empty($this->passwordEC) 
            && !empty($username) 
            && is_string($username)
        ) {
            $db = new DB(TRUE);
            $db->setQuery('UPDATE Usuario SET PasswordSalted = ? '
                        . 'WHERE Nombre = ?');
            $db->setBindParam('ss');
            $db->setQueryParams([$this->passwordEC, $username]);
            //// atenti porque la func devuelve tb nro de error
            // ToDo: procesar nro de error
            $retval = $db->queryExecute();
            if (is_bool($retval)) {
                return $retval;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Recupera de la DB la contraseña encriptada del usuario indicado y la 
     * almacena en el objeto.
     * 
     * @param string $username Nombre de usuario.
     * @return boolean TRUE si se encontró y almacenó correctamente, 
     * FALSE si no.
     */
    public function retrieve_fromDB($username)
    {
        if (!empty($username) && is_string($username)) {
            $db = new DB;
            $db->setQuery('SELECT PasswordSalted FROM Usuario WHERE Nombre = ?');
            $db->setBindParam('s');
            $db->setQueryParams($username);
            $db->queryExecute();
            return $this->setPasswordEncrypted($db->getQueryData());
        }
        
        return FALSE;
    }
}