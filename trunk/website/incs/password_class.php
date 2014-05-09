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
 * Ejemplo de uso:
 * <pre><code>
 * $pass = new Password();
 * $pass->setPlaintext($ptPass);
 * $pass->encryptPassword();
 * // Obtengo la contraseña encriptada
 * $encPass = $pass->getEncrypted();
 * // Valido una nueva contraseña plana
 * $pass->setPlaintext($NewptPass); 
 * if ($pass->authenticatePassword()) {
 *      echo "Contraseña valida";
 * } else {
 *      echo "Contraseña incorrecta";
 * }
 * </code></pre>
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 1.5
 */
class Password
{
    use Passwordt;
}