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
 * Manejo de archivos: validación de rutas, inclusión de dependencias, etc.
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2014, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.10
 */
class File
{
    /**
     * Constante REQ empleada por File::reqinc
     */
    const REQ = 'req';
    /**
     * Constante INC empleada por File::reqinc
     */
    const INC = 'inc';
    
    /**
     * Extensiones permitidas, separadas por coma.
     */
    const EXTENSIONS = 'php,html,css,js,jpg,jpeg,png';
    
    /**
     * Determina la logitud máxima que puede tener una ruta relativa desde la 
     * raíz del proyecto a un archivo, incluyendo el nombre de archivo.
     */
    const LOC_MAXLEN = 260;
    
    // __SPEC
    // __PRIV
    // __PROT
    // __PUB
    /**
     * Determina si una ruta con nombre de archivo y extensión es válida.<br />
     * Solo puede contener letras mayúsculas y minúsculas del alfabeto inglés,
     * números y los símbolos '/', '-' y '_'.<br />
     * Solo puede contener un único '.' para la extensión.<br />
     * El primer caracter debe ser una letra o un número o bien '/'.<br />
     * La longitud máxima la determina LOC_MAXLEN.<br />
     * P. e.: lib/php/inc/file_class.php
     * 
     * @param string $loc Ruta a validar
     * @param boolean $exists [opcional]<br />
     * Si es TRUE, determina existencia del archivo.
     * FALSE por defecto.
     * @return boolean TRUE si la ruta es válida, FALSE si no. 
     * Si $exists = TRUE, solo devuelve TRUE si la ruta es válida y además el 
     * archivo existe.
     */
    public static function isValid_loc($loc, $exists = FALSE)
    {
        //$ext = pathinfo($loc, PATHINFO_EXTENSION);
        @list($url, $ext) = explode('.', $loc, 2);
        if (static::isValid_extension($ext)
            && preg_match('/^[a-zA-Z0-9\/]{1}[a-zA-Z0-9\_\-\/\.]{0,'
                                . (self::LOC_MAXLEN - 1) . '}$/', $url)
        ) {
            return ($exists ? file_exists($loc) : TRUE);
        }
        
        return FALSE;
    }
    
    /**
     * Determina si una extensión de página dada es válida. P. E.: php
     * 
     * @param string $extension Extensión (¡no debe comenzar con punto!)
     * @return boolean TRUE si es válida, FALSE si no.
     */
    public static function isValid_extension($extension)
    {
        return in_array($extension, 
                    function_exists('array_from_string_list') 
                        ? array_from_string_list(self::EXTENSIONS) 
                        : explode(',', self::EXTENSIONS)
                );
    }
   
    /**
     * Realiza la inclusión de un archivo de manera segura.
     * Cabe destacar que el resultado de la inclusión siempre es NULL, 
     * ¡que puede evaluarse como FALSE en ciertas condiciones!.
     * @param string $filepath Ruta absoluta al archivo con su extensión 
     * para incluir.
     * @param string $type [opcional]<br />
     * File::REQ | File::INC: determina si se requerirá 
     * el archivo (por defecto) o simplemente se incluirá.
     * @return boolean|NULL Si la ruta es válida, devuelve el resultado de la 
     * inclusión (require_once o include_once).  
     * Si la ruta no es válida, devuelve FALSE.
     */
    public static function reqinc($filepath, $type = self::REQ)
    {
        $fpath = method_exists('Sanitizar', 'value') 
                    ? Sanitizar::value($filepath) 
                    : $filepath;
        return (static::isValid_loc($fpath) 
                    ? call_user_func(($type == self::REQ) 
                                        ? 'require_once' 
                                        : 'include_once'
                                    , $fpath)
                    : FALSE
        );
    }
    
    /**
     * Realiza la inclusión de una librería PHP externa de manera segura.
     * @param string $libname Nombre de la librería, sin extensión.
     * @param string $type [opcional]<br />
     * File::REQ | File::INC: determina si se requerirá 
     * el archivo (por defecto) o simplemente se incluirá.
     * @return boolean|NULL Si la librería es válida, devuelve el resultado de la 
     * inclusión (require_once o include_once).  
     * Si no es válida, devuelve FALSE.
     */
    public function reqincExtlib($libname, $type = self::REQ) 
    {
        return static::reqinc(SMP_FS_ROOT . SMP_LOC_EXT . $libname . '.php'
                                , $type);
    }
    
    /**
     * Realiza la inclusión de un archivo de configuración 
     * (que debe estar en /etc).
     * @param string $configfile Nombre del archivo de configuración
     * , sin extensión.
     * @param string $type [opcional]<br />
     * File::REQ | File::INC: determina si se requerirá 
     * el archivo (por defecto) o simplemente se incluirá.
     * @return boolean|NULL Si la librería es válida, devuelve el resultado de la 
     * inclusión (require_once o include_once).  
     * Si no es válida, devuelve FALSE.
     */
    public function reqincConfig($configfile, $type = self::REQ) 
    {
        return static::reqinc(SMP_FS_ETC . $configfile . '.php'
                                , $type);
    }
}
