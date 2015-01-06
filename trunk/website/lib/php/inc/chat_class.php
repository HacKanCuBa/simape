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
 * chat_class.php
 * Esta clase implementa "freichat" www.codologic.com
 * @author Pedro Facundo Tamborindeguy <pftambo@gmail.com>
 * @copyright (c) 2014, Pedro Facundo Tamborindeguy
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.12
 */
class Chat
{
    protected $userId;
    
    public function __construct($userId)
    {        
        $this->userId = $userId;   
        //setcookie("freichat_user", "LOGGED_IN", time()+3600, "/");
        //setcookie("freichat_user", "LOGGED_IN", time()+3600, SMP_WEB_ROOT, IP::getServerIP(), is_connection_ssl(), TRUE); // *do not change -> freichat code
    }
    
    /**
     * Devuelve el Id del usuario logueado.
     * @return string Id de sesión de chat.
     */
    public function getXhash()
    {
        return md5($this->userId . static::generateUid());
    }
    
    /**
     * Devuelve el Uid de la sesión en caso de existir, en caso contrario, genera uno aleatorio.
     * @return String Devuelve un Uid aleatorio.
     */    
    public static function generateUid()
    {
        if(Session::retrieve('freichatUid'))
        {
            return Session::retrieve('freichatUid');
        }
        else
        {
            $Uid = Crypto::getRandomIV();
            Session::store('freichatUid', $Uid);
            return $Uid;
        }
    }
    
    /**
     * Inicia el script del chat. Imprime código html para iniciar el chat. Invocarlo dentro de body. 
     * @global string $PATH Es la ruta relativa a freichat.
     * @param int $indent [Opcional] Indentado.
     */
    public function start($indent = 1)
    {
        Page::_e("<script type='text/javascript' src='" 
                    . SMP_WEB_ROOT . SMP_LOC_EXT . 'freichat/' 
                    . 'client/main.php?id=' . $this->userId . '&amp;xhash=' 
                    . $this->getXhash() . "'></script>"
                , $indent);
        Page::_e("<link rel='stylesheet' type='text/css' href='" 
                    . SMP_WEB_ROOT . SMP_LOC_EXT . 'freichat/' 
                    . "client/jquery/freichat_themes/freichatcss.php' />"
                , $indent);                
    }    
}






