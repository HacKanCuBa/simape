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
 * Clase para crear y modelizar las barras de navegación
 * 
 * Ejemplo de uso:
 * <pre><code>
 * $navbar = new Navbar;
 * $navbar->addButton('Inicio', '/', Navbar::BTN_CLASS_CATEGORY);
 * $navbar->addButton(['&iexcl;Bienvenido <i>testing</i>!', 'other'], ['', '#'], Navbar::BTN_CLASS_CURRENT);
 * $navbar->setIndent(1);
 * $navbar->getHorizontal(TRUE);
 * </code></pre>
 * 
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.2
 */

class Navbar
{
    /*
     * Clases de botones.  Agregarlos al array! 
     */
    const BTN_CLASS_CATEGORY = 'category';
    const BTN_CLASS_CURRENT = 'current';
    const BTN_CLASS_NONE = NULL;

    /**
     * @var array Clases de botones.
     */
    protected static $btn_class = array(self::BTN_CLASS_CATEGORY, 
                                        self::BTN_CLASS_CURRENT,
                                        self::BTN_CLASS_NONE);
    
    /**
     * @var array Botones de la barra.
     */
    protected $buttons;
    
    /**
     *
     * @var int Nivel de indentado donde debe comenzar la barra.
     */
    protected $indent;
    

    // __ SPECIALS
    
    // __ PRIV
    
    // __ PROT
    /**
     * Determina si la clase de botón indicada es válida.
     * 
     * @param mixed $class Clase de botón, uno de BTN_CLASS_...
     * @return boolean TRUE si es válida, FALSE si no.
     */
    protected static function isValid_btnClass($class)
    {
        if (in_array($class, self::$btn_class)) {
            return TRUE;
        }
        
        return FALSE;
    }
    // __ PUB
    /**
     * Agrega botones a la barra.  Puede pasársele un arreglo de nombres, url y 
     * class para agregar más de un botón.<br />
     * Ejemplo de uso:
     * <pre><code>
     * addButton(['inicio', 'salir'], 
     *           ['inicio.php', 'subdir/logout.php'], 
     *           [ BTN_CLASS_CATEGORY ])
     * </code></pre>
     * Al no estar definida la clase del botón 'salir', se asume NULL.
     * 
     * @param mixed $name Nombre del botón.
     * @param mixed $url URL del enlace del botón.
     * @param mixed $class Clase del botón, uno de BTN_CLASS_...
     * @return boolean TRUE si se agregó correctamente, FALSE si no.
     */
    public function addButton($name, $url = NULL, $class = self::BTN_CLASS_NONE)
    {
        if (!empty($name)) {
            if (!is_array($name)) {
                $name = array($name);
            }
            
            if (!empty($url) && !is_array($url)) {
                $url = array($url);
            }
            
            if (!empty($class) && !is_array($class)) {
                $class = array($class);
            }
            
            for ($i = 0; $i < count($name); $i++) {
                if (isset($url[$i])) {
                    $loc = $url[$i];
                } else {
                    $loc = NULL;
                }
                
                if (isset($class[$i]) 
                    && self::isValid_btnClass($class[$i])
                ) {
                    $type = $class[$i];
                } else {
                    $type = self::BTN_CLASS_NONE;
                }
                
                $this->buttons[] = array('name' => $name[$i], 
                                         'url' => $loc, 
                                         'class' => $type);
            }
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Remueve un botón de la barra de botones.  Recibe el nombre del botón a 
     * eliminar.
     * @param mixed $name Nombre del botón a borrar
     * @return boolean TRUE si se eliminó el botón, FALSE si no.
     */
    public function delButton($name) 
    {
        $retval = FALSE;
        if(isset($this->buttons)) {
            foreach ($this->buttons as $key => $value) {
                if($value['name'] == $name) {
                    unset($this->buttons[$key]);
                    $retval = TRUE;
                }
            }
        }
        
        return $retval;
    }
    
    /**
     * Almacena el valor del indentado con el que se debe comenzar la barra de 
     * navegación.
     * 
     * @param int $indent Nivel de indentado.
     * @return boolean TRUE si se almacenó correctamente, FALSE si no.
     */
    public function setIndent($indent) 
    {
        if (!empty($indent) && is_int($indent)) {
            $this->indent = $indent;
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Imprime o devuelve la barra de navegación vertical.
     * 
     * @param boolean $print TRUE para imprimir la barra de navegación, 
     * FALSE para retornarla como string (por defecto).
     * @return string La barra de navegación vertical o NULL.
     */
    public function getVertical($print = FALSE)
    {
        if (isset($this->indent)) {
            $indent = Page::indent($this->indent);
        } else {
            $indent = '';
        }
        
        $navbar = "\n$indent<div class='nav_vertbox'>";
        $navbar .= "\n$indent\t<ul class='nav_vert'>";
        
        if (isset($this->buttons)) {
            foreach ($this->buttons as $button) {
                if ($button['class']) {
                    $li = "<li class='" . $button['class'] . "'>";
                } else {
                    $li = '<li>';
                }
                
                if ($button['url']) {
                    $a = "<a href='" . $button['url'] . "'>";
                } else {
                    $a = '<a>';
                }
                $navbar .= "\n$indent\t\t$li$a" . $button['name'] . "</a></li>";
            }
        }
        
        $navbar .= "\n$indent\t</ul>";
        $navbar .= "\n$indent</div>";
        
        if ($print) {
            echo $navbar;
        } else {
            return $navbar;
        }
    }
    
    /**
     * Imprime o devuelve la barra de navegación horizontal.
     * 
     * @param boolean $print TRUE para imprimir la barra de navegación, 
     * FALSE para retornarla como string (por defecto).
     * @return string La barra de navegación horizontal o NULL.
     */
    public function getHorizontal($print = FALSE)
    {
        if (isset($this->indent)) {
            $indent = Page::indent($this->indent);
        } else {
            $indent = '';
        }
        
        $navbar = "\n$indent<div class='nav_horbox'>";
        $navbar .= "\n$indent\t<ul id='nav_hor'>";
        
        if (isset($this->buttons)) {
            foreach ($this->buttons as $button) {
                if ($button['class']) {
                    $li = "<li class='" . $button['class'] . "'>";
                } else {
                    $li = '<li>';
                }
                
                if ($button['url']) {
                    $a = "<a href='" . $button['url'] . "'>";
                } else {
                    $a = '<a>';
                }
                $navbar .= "\n$indent\t\t$li$a" . $button['name'] . "</a></li>";
            }
        }
        
        $navbar .= "\n$indent\t</ul>";
        $navbar .= "\n$indent</div>";
        
        // Corrección de posicion para el cuerpo de la página
        $navbar .= "\n$indent<style type='text/css'>";
        $navbar .= "\n$indent\t.data { margin-top: 0px; }";
        $navbar .= "\n$indent</style>";
        
        if ($print) {
            echo $navbar;
        } else {
            return $navbar;
        }
    }
}