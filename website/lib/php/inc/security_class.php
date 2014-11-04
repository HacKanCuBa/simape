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

/**
 * security_class.php
 * Esta clase comprende funciones relacionadas con seguridad, analisis de datos,
 * prevencion de hacking, etc.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.10
 */
class Security 
{
    /**
     * Analiza un parámetro dado y determina si se ha intentado realizar un 
     * hack.  Esto es, si se ha ingresado un valor indebido como ser 
     * "<script>" o símbolos especiales que no deben ingresarse.
     * Si recibe el parámetro $db, también verifica intentos de inyección SQL.
     * @param mixed $value Valor a verificar.  Debe ser un string o 
     * numérico o similar.  No puede ser una estructura.
     * @param DB $db [opcional]<br />
     * Objeto de base de datos.
     * @return boolean TRUE si el parámetro es válido y seguro, FALSE en caso 
     * contrario (esto es, se detectó un intento de hackeo).
     */
    public static function isValueSafe($value, $db = NULL)
    {
        $p = Crypto::getHash($value, 1, 'md5');
        $ps = Crypto::getHash(Sanitizar::value($value), 1, 'md5');
        $pdb = is_a($db, 'DB') ? $db->sanitizar($value) : $ps;
        return (($p === $ps) && ($p === $pdb));
    }
    
    public static function errorHackDetected()
    {
        Page::httpError(1337);
    }
}
