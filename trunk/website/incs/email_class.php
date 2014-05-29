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
 * Envía emails de manera sencilla.  Requiere PHPMailer!
 * Es un simple wrapper de PHPMailer.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.21
 * @uses PHPMailer PHP email creation and transport class.
 */
class Email
{
    protected $phpmailer;
    
    public function __construct() 
    {
        /*
         * No extiendo la clase PHPMailer porque me resulta insoportable
         * y horrible que todas las propiedades sean public
         */
        require_once SMP_FS_ROOT . SMP_LOC_INCS . 'phpmailer/PHPMailerAutoload.php';
        
        $this->phpmailer = new PHPMailer(FALSE);
        $this->phpmailer->isSMTP();
        $this->phpmailer->CharSet = SMP_PAGE_CHARSET;
        $this->phpmailer->Debugoutput = 'error_log';
        $this->phpmailer->SMTPDebug = 0;
        $this->phpmailer->SMTPAuth = TRUE;
        $this->phpmailer->SMTPSecure = SMP_EMAIL_SMTP_PROTO;
        $this->phpmailer->Host = SMP_EMAIL_SMTP_HOST;
        $this->phpmailer->Port = SMP_EMAIL_SMTP_PORT;
        $this->phpmailer->Username = SMP_EMAIL_USER;
        $this->phpmailer->Password = SMP_EMAIL_PSWD;
        $this->phpmailer->isHTML(TRUE);
        // Sale mal el link de password restore con WordWrap pq elimina el token
        //$this->phpmailer->WordWrap = 80;
    }
    
    /**
     * Fija el cuerpo del mensaje.  Puede contener formato HTML.
     * 
     * @param string $body Cuerpo del mensaje.
     */
    public function setBody($body)
    {
        $this->phpmailer->msgHTML($body);
    }
    
    /**
     * Fija el campo <i>De:</i> (<i>From:</i>).
     * 
     * @param string $from_name Nombre.
     * @param string $from_address Dirección de email.
     * @return boolean TRUE si se almacenó correctamente en el objeto, 
     * FALSE si no.
     */
    public function setFrom($from_name, $from_address)
    {
        if (is_string($from_name) && is_string($from_address)) {
            $this->phpmailer->FromName = $from_name;
            $this->phpmailer->From = $from_address;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Fija el valor de <i>Asunto:</i> (<i>Subject:</i>).
     * @param string $subject Asunto.
     * @return boolean TRUE si se almacenó correctamente en el objeto, 
     * FALSE si no.
     */
    public function setSubjet($subject)
    {
        if (is_string($subject)) {
            $this->phpmailer->Subject = $subject;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Devuelve el último error, si hubo.
     * 
     * @return string Último error disponible.
     */
    public function getLastError()
    {
        return $this->phpmailer->ErrorInfo;
    }
    
    /**
     * Devuelve el charset del email.
     * 
     * @return string Charset.
     */
    public function getCharset()
    {
        return $this->phpmailer->CharSet;
    }

        /**
     * Agrega una dirección <i>Para:</i> (<i>To:</i>).  Permite agregar uno o 
     * más receptores.
     * 
     * @param string $address Dirección de email.
     * @param type $name [opcional]<br />
     * Nombre del receptor.
     * @return boolean TRUE si se agregó correctamente, FALSE si no.
     */
    public function addAddress($address, $name = NULL)
    {
        return $this->phpmailer->addAddress($address, $name);
    }

    /**
     * Envía un email.  En caso de error, ver getLastError.
     * @see getLastError
     * @return boolean TRUE si se envió correctamente, FALSE si no.
     */
    public function send()
    {
        return $this->phpmailer->send();
    }
}