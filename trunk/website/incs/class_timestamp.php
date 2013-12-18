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
 * Clase que comprende funciones varias de manejo del tiempo.
 * 
 * Ejemplo de uso:
 * <pre><code>
 * $today = Timestamp::getToday();
 * ...
 * if ($today != Timestamp::getToday()) {
 *      echo "cambió el día!!"
 * }
 * </code></pre>
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.2
 */
class Timestamp
{
   /**
    * Devuelve el dia actual en Unix Timestamp, esto es, a las 00:00:00hs.
    * 
    * @return int Unix Timestamp del día actual.
    */
   public static function getToday() 
    {
        //date_default_timezone_set('America/Argentina/Buenos_Aires');

        return strtotime(date('Y/m/d') . " 00:00:00");
    }

    public static function getThisSeconds($seconds) 
    {
        if (!empty($seconds) && is_int($seconds)) {
            return(((int)(time() / ($seconds))) * ($seconds));
        }
        
        return 0;
    }

    public static function getThisMinutes($minutes) 
    {    
        if (!empty($minutes) && is_int($minutes)) {
            return self::getThisSeconds(60 * $minutes);
        }
        
        return 0;
    }

    public static function getThisHours($hours) 
    {    
        if (!empty($hours) && is_int($hours)) {
            return self::getThisMinutes(60 * $hours);
        }
        
        return 0;
    }
}