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

// -- Validaciones
function isValid_username(string $username) 
{
    /**
     * Valida un string y determina si cumple las restricciones impuestas en la
     * configuración sobre los nombres de usuario.
     * 
     * @param string $username Nombre de usuario.
     * @return boolean TRUE si el string es un nombre de usuario válido, 
     * FALSE si no lo es.
     */
    
    if (!empty($username) 
        && is_string($username)
        && (strlen($username) <= constant('USRNAME_MAXLEN')) 
        && (strlen($username) >= constant('USRNAME_MINLEN'))
    ) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function isValid_password(string $password) 
{
    /**
     * Valida un string y determina si cumple las restricciones impuestas sobre
     * las contraseñas.
     * IMPORTANTE: ¡NO ES UNA FUNCIÓN DE AUTENTICACIÓN!
     * 
     * @param string $password Contraseña a ser validada.
     * @return boolean TRUE si el string es una contraseña válida, 
     * FALSE si no lo es.
     */
    
    if (!empty($password)
        && is_string($password)
        && (strlen($password) <= constant('PWD_MAXLEN')) 
        && (strlen($password) >= constant('PWD_MINLEN'))
    ) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function isValid_msg(string $msg) 
{
    /**
     * Valida un string y determina si cumple las restricciones de los mensajes.
     * 
     * @param string $msg Mensaje a ser validado.
     * @return boolean TRUE si el string es un mensaje válido, 
     * FALSE si no lo es.
     */
    
    if (!empty($msg) 
        && is_string($msg)
        && (strlen($msg) <= constant('MGS_MAXLEN'))
    ) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function isValid_b64($data) 
{
    /**
     * Valida un string y determina si está efectivamente codificado en BASE64.
     * 
     * @param string $data String a ser validado.
     * @return boolean TRUE si el string está codificado en BASE64, 
     * FALSE si no.  Si el mismo está VACIO, devuelve NULL.
     */
    if(!empty($data)) {
        if (base64_encode(base64_decode($data, TRUE)) === $data) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    return NULL;
}

function isValid_email($email) 
{
    /**
     * Valida un string y determina si cumple las restricciones de las 
     * direcciones de email.
     * 
     * @param string $email Dirección de email a validar.
     * @return boolean TRUE si el string es una dirección de email válida, 
     * FALSE si no lo es.  Si la misma es VACIA, devuelve NULL.
     */
    
    if (!empty($email)) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    return NULL;
}

/**
* Valida un string y determina si se trata de un código UUID4.
* 
* @param string $uuid String a validar.
* @return boolean TRUE si el string cumple los requisitos y es un código 
* UUID4 válido, FALSE si no lo es..
*/
function isValid_uuid($uuid) 
{
   if (!empty($uuid) && is_string($uuid)) {
       return (bool) preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-'
                                . '[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', 
                                $uuid);
   }

   return FALSE;
}

function isValid_uploadedfile($file_id) 
{
    /**
     * Devuelve TRUE si el archivo indicado fue verdaderamente subido usando
     * HTTP POST, FALSE en cualquier otro caso.
     * 
     * @param string $file_id Nombre del indice de archivo de la variable 
     * super global $_FILES
     * @return boolean TRUE si se trata de un archivo subido por HTTP POST,
     * FALSE en caso contrario.  Si el identificador es nulo, devuelve NULL.
     */
    
    if (!empty($file_id)) {
        return is_uploaded_file(files_get_tmpname($file_id));
    }
    
    return NULL;
}

function isValid_diaSemana($diaSemana) {
    /**
     * Determina si el string pasado corresponde a un día de la semana
     * 
     * @param string $diaSemana String a validar
     * @return boolean TRUE si el string corresponde a un día de la semana, 
     * FALSE si no.
     */
    
    if (isset($diaSemana)) {
        if (substr_count(diasSemana(), $diaSemana)) {
            return TRUE;
        } elseif (substr_count(diasSemana(TRUE), $diaSemana)) {
            return TRUE;
        }   
    }
    
    return FALSE;
}
// --

define('FUNC_VALIDATIONS', TRUE);