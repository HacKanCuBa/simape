<?php

/*****************************************************************************
 *  SiMaPe
 *  Sistema Integrado de Manejo de Personal
 *  Copyright (C) <2013>  <Ivan Ariel Barrera Oro>
 *  ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *****************************************************************************/

/**
 * IP Class:
 * Métodos varios para manipular IP's u obtenerlas.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.2
 */
class IP
{
    /**
     * Devuelve la IP del servidor.
     * @access public
     * @return string IP del servidor.
     */
    public static function getServerIP()
    {
        return SMP_SERVER_ADDR ?: 
                        (stristr(Sanitizar::glSERVER('SERVER_SOFTWARE'), 'win') ? 
                                            Sanitizar::glSERVER('LOCAL_ADDR') : 
                                            Sanitizar::glSERVER('SERVER_ADDR'));
    }

    /**
     * Devuelve la IP del cliente, tratando de resolver aún en caso de proxy.
     * No es 100% fiable, es más efectivo un script en java o similar.
     * @access public
     * @return string IP del cliente o string vacío.
     */
    public static function getClientIP()
    {
        // https://stackoverflow.com/questions/15699101/get-the-client-ip-address-using-php
        $possible_ip = [    
                            Sanitizar::value(getenv('HTTP_CLIENT_IP')),
                            Sanitizar::value(getenv('HTTP_X_FORWARDED_FOR')),
                            Sanitizar::value(getenv('HTTP_X_FORWARDED')),
                            Sanitizar::value(getenv('HTTP_FORWARDED_FOR')),
                            Sanitizar::value(getenv('HTTP_CLIENT_IP')),
                            Sanitizar::value(getenv('HTTP_FORWARDED')),
                            Sanitizar::glSERVER('HTTP_CLIENT_IP'),
                            Sanitizar::glSERVER('HTTP_X_FORWARDED_FOR'),
                            Sanitizar::glSERVER('HTTP_X_FORWARDED'),
                            Sanitizar::glSERVER('HTTP_FORWARDED_FOR'),
                            Sanitizar::glSERVER('HTTP_FORWARDED'),
                            Sanitizar::glSERVER('REMOTE_ADDR'),
                            Sanitizar::value(getenv('REMOTE_ADDR')),
                        ];
        $client_ip = '';

        foreach ($possible_ip as $ip) {
            $client_ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
            if ($client_ip) {
                break;
            }
        }

        return $client_ip ?: '';
    }
    
    /**
     * Devuelve los octetos de una IP, ya sea v4 o v6.
     * @param string $ip IP.
     * @return array Octetos de la IP como array, o NULL si la IP no era válida.
     */
    public static function getOctets($ip) 
    {
        return (static::isValid_ipV4($ip) ? 
                                    explode('.', $ip) : 
                                    (static::isValid_ipV6($ip) ?
                                                            explode(':', $ip) : 
                                                            NULL)
                );
    }
    
    /**
     * Valida un string para determinar si se trata de una IP v4.
     * @param string|array $ip4 IP a validar, como string o array de octetos.
     * @return boolean TRUE si se trata de una IP v4, FALSE si no.
     */
    public static function isValid_ipV4($ip4)
    {
        $ip = is_array($ip4) ? implode('.', $ip4) : $ip4;
        return boolval(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4));
    }
    
    /**
     * Valida un string para determinar si se trata de una IP v6.
     * @param string|array $ip6 IP a validar, como string o array de octetos.
     * @return boolean TRUE si se trata de una IP v6, FALSE si no.
     */
    public static function isValid_ipV6($ip6)
    {
        $ip = is_array($ip6) ? implode(':', $ip6) : $ip6;
        return boolval(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6));
    }
}