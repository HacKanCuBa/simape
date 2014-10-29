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
 * @version 0.12
 */
class Curl
{
    /**
     * Recurso devuelto por curl_init().
     * @var resource
     */
    protected $ch = NULL;
    
    /**
     * Resultado de curl_exec().
     * @var mixed
     */
    protected $result = NULL;
    
    /**
     * Cookie path
     * @var string
     */
    protected $cookiePath;
    
    /**
     * Parámetros por defecto para CURL.
     * @var array
     */
    protected $curl_defaults;

    // __SPECIALS
    function __construct() 
    {
        $this->ch = curl_init();
        $this->cookiePath = Crypto::getRandomFilename('SMPSAPER', 
                                                        '', 
                                                        9, 
                                                        SMP_FS_ROOT 
                                                            . SMP_LOC_TMPS);
        $this->curl_defaults = array(
                CURLOPT_AUTOREFERER => 1,
                CURLOPT_MAXREDIRS => 20,
                CURLOPT_FOLLOWLOCATION => 0,
                CURLOPT_HEADER => 0,
                CURLOPT_FRESH_CONNECT => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FORBID_REUSE => 0,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_COOKIEJAR => $this->cookiePath,
                CURLOPT_COOKIEFILE => $this->cookiePath,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_USERAGENT => Sanitizar::glSERVER('HTTP_USER_AGENT') ?: 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20120101 Firefox/29.0'
        );
    }

    function __destruct() 
    {
        curl_close($this->ch);
    }
    // __PRIV
    // 
    // __PROT
    /**
     * Ejecuta curl_exec.
     * @param type $options Array de opciones
     */
    protected function exec($options) 
    {
        $redirects = $options[CURLOPT_MAXREDIRS];
        $curlopt_header = $options[CURLOPT_HEADER];
        
        // http://stackoverflow.com/a/5498992 
        if ((!ini_get('open_basedir') && !ini_get('safe_mode')) || !$options[CURLOPT_FOLLOWLOCATION]) {
            curl_setopt_array($this->ch, $options);
            $this->result = curl_exec($this->ch);
        } else {
            $options[CURLOPT_FOLLOWLOCATION] = 0;
            $options[CURLOPT_HEADER] = 1;
            $options[CURLOPT_RETURNTRANSFER] = 1;
            $options[CURLOPT_FORBID_REUSE] = 0;
            curl_setopt_array($this->ch, $options);
            
            do {
                $data = curl_exec($this->ch);
                if (curl_errno($this->ch)) {
                    break;
                }
                $code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
                if ($code != 301 && $code != 302) {
                    break;
                }
                $header_start = strpos($data, "\r\n")+2;
                $headers = substr($data, 
                                    $header_start, 
                                    strpos($data, "\r\n\r\n", $header_start) 
                                        + 2 - $header_start
                );
                if (!preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches)) {
                    break;
                }
                curl_setopt($this->ch, CURLOPT_URL, $matches[1]);
            } while (--$redirects);
            if (!$redirects) {
                trigger_error('Demasiadas redirecciones. Al seguir las redirecciones, libcurl alcanzo la cantidad maxima permitida.', E_USER_WARNING);
            }
            if (!$curlopt_header) {
                $data = substr($data, strpos($data, "\r\n\r\n")+4);
            }
            $this->result = $data;
        }        
    }
    // __PUB
    /**
     * Devuelve el último mensaje de error.
     * @return string Mensaje de error sobre el recurso almacenado o ''.
     */
    public function getError()
    {
        if (is_resource($this->ch)) {
            return curl_error($this->ch);
        }
        return '';
    }
    
    /**
     * Devuelve el resultado de la última operación con CURL.
     * @return string Resultado de la última operación
     */
    public function getResult()
    {
        if (is_string($this->result)) {
            return $this->result;
        }
        return '';
    }
    
    /**
     * Send a POST requst using cURL
     * @link http://ar2.php.net/manual/en/function.curl-exec.php#98628 Fuente
     * @param string $url to request
     * @param array $post values to send
     * @param array $options for cURL
     * @return boolean TRUE si se obtuvieron datos, FALSE si no.
     */
    public function post($url, array $post = NULL, array $options = array())
    {
        $post_params = array(
                        CURLOPT_POST => count($post),
                        CURLOPT_URL => $url,
                        CURLOPT_POSTFIELDS => http_build_query($post)
        );
        
        $options += $this->curl_defaults + $post_params;
        
        $this->exec($options);
        
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
    public function get($url, array $get = NULL, array $options = array())
    {   
        $get_params = array(
                    CURLOPT_URL => $url 
                                    . (strpos($url, '?') === FALSE ? '?' : '') 
                                    . http_build_query($get),
        );
        
        $options += $this->curl_defaults + $get_params;
        
        $this->exec($options);
        
        return boolval($this->result);
    } 
}