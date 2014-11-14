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
 * @version 0.1
 */
class Chat
{
    protected $userId;
    
    public function __construct($host, $dbname, $dbuser, $dbpass, $adminpass, $userId, $dbg = FALSE)
    {
        require_once SMP_LOC_ETC . 'config_chat.php';
        /* Data base details */
        global $dsn;
        $dsn = 'mysql:host=' .$host . ';dbname=' . $dbname; //DSN
        global $db_user;
        $db_user = $dbuser; //DB username
        global $db_pass; //DB password 
        $db_pass = $dbpass; //DB password
        global $driver; //Integration driver
        $driver = 'Custom'; //Integration driver
        global $db_prefix; //prefix used for tables in database
        $db_prefix = ''; //prefix used for tables in database
        global $uid; //Any random unique number
        $uid = static::generateUid(); //Any random unique number

        global $connected; //only for custom installation
        $connected='YES';
        global $PATH; // Use this only if you have placed the freichat folder somewhere else
        $PATH = /*SMP_LOC_EXT . */'freichat/'; 
        global $installed; //make it false if you want to reinstall freichat
        $installed=true; 
        global $admin_pswd; //backend password 
        $admin_pswd = $adminpass; 

        global $debug;
        $debug = $dbg;
        global $custom_error_handling; // used during custom installation
        $custom_error_handling='YES'; 

        global $use_cookie;
        $use_cookie='false';

        /* email plugin */
        global $smtp_username;
        $smtp_username = '';
        global $smtp_password;
        $smtp_password = '';

        global $force_load_jquery;
        $force_load_jquery = 'NO';

        /* Custom driver */
        global $usertable; //specifies the name of the table in which your user information is stored.
        $usertable='frei_users';
        global $row_username; //specifies the name of the field in which the user's name/display name is stored.
        $row_username='Nombre';
        global $row_userid; //specifies the name of the field in which the user's id is stored (usually id or userid)
        $row_userid='Id';


        global $avatar_table_name; //specifies the table where avatar information is stored
        $avatar_table_name='members';
        global $avatar_column_name; //specifies the column name where the avatar url is stored
        $avatar_column_name='avatar';
        global $avatar_userid; //specifies the userid  to the user to get the user's avatar
        $avatar_userid='id';
        global $avatar_reference_user; //specifies the reference to the user to get the user's avatar in user table 
        $avatar_reference_user='id';
        global $avatar_reference_avatar; //specifies the reference to the user to get the user's avatar in avatar
        $avatar_reference_avatar='id';
        global $avatar_field_name;
        $avatar_field_name=$avatar_column_name; //to avoid unnecessary file changes , *do not change
        
        
        $this->userId = $userId;
        
        setcookie("freichat_user", "LOGGED_IN", time()+3600, "/");
        
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
    protected static function generateUid()
    {
        return '545b9d013e441';
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
    public function start($indent = 0)
    {
        global $PATH;
        Page::_e('<script type="text/javascript" language="javascript" src="' 
                    . 'https://' . IP::getServerIP() .SMP_WEB_ROOT . SMP_LOC_EXT . $PATH . 'client/main.php?id=' . $this->userId . '&xhash=' 
                    . $this->getXhash() 
                    .  '"></script>'
                , $indent);
        Page::_e('<link rel="stylesheet" href="' 
                    . 'https://' . IP::getServerIP() . SMP_WEB_ROOT . SMP_LOC_EXT . $PATH 
                    . 'client/jquery/freichat_themes/freichatcss.php"'
                    . ' type="text/css">'
                , $indent);
        //setcookie("freichat_user", "LOGGED_IN", time()+3600, "/");
        //setcookie("freichat_user", "LOGGED_IN", time()+3600, SMP_WEB_ROOT, IP::getServerIP(), is_connection_ssl(), TRUE); // *do not change -> freichat code
                
    }
        
    
    
}






