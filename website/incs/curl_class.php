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
 * Esta clase facilita el uso de curl
 * - POST
 * - GET
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.1
 */
class Curl
{
    protected $ch;
    protected $result;

    // __SPECIALS
    function __construct() 
    {
        $this->ch = curl_init();
    }

    function __destruct() 
    {
        curl_close($this->ch);
    }
    // __PRIV
    // 
    // __PROT
    // 
    // __PUB
    public function getError()
    {
        return curl_error($this->ch);
    }
    
    /**
     * Send a POST requst using cURL
     * @link http://ar2.php.net/manual/en/function.curl-exec.php#98628 Fuente
     * @param string $url to request
     * @param array $post values to send
     * @param array $options for cURL
     * @return boolean
     */
    public function post($url, array $post = NULL, array $options = array())
    {
        $defaults = array(
            CURLOPT_POST => count($post),
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_POSTFIELDS => http_build_query($post)
        );

        curl_setopt_array($this->ch, ($options + $defaults));
        $this->result = curl_exec($this->ch);
        
        return boolval($this->result);
    }
    
    /**
     * Send a GET requst using cURL
     * @link http://ar2.php.net/manual/en/function.curl-exec.php#98628 Fuente
     * @param string $url to request
     * @param array $get values to send
     * @param array $options for cURL
     * @return string
     */
    public static function get($url, array $get = NULL, array $options = array())
    {   
        $defaults = array(
            CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get),
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 4
        );

        curl_setopt_array($this->ch, ($options + $defaults));
        $this->result = curl_exec($this->ch);
        
        return boolval($this->result);
    } 
}