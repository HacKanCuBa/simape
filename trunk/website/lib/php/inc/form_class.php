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
 * form_class.php
 * Manejo de formularios: crear, modelizar y autenticar.
 *
 * @author Iván A. Barrera Oro <ivan.barrera.oro@gmail.com>
 * @copyright (c) 2013, Iván A. Barrera Oro
 * @license http://spdx.org/licenses/GPL-3.0+ GNU GPL v3.0
 * @version 0.1 untested
 */

class Form extends FormToken
{
    const SMP_FRM_BUTTON = 'frm_button';
    const SMP_FRM_TEXT = 'frm_text';
    const SMP_FRM_PASSWORD = 'frm_password';
    const SMP_FRM_OPTION = 'frm_option';

    // __ SPECIALS
    
    // __ PRIV
    
    // __ PROT
    
    // __ PUB
    public static function createForm()
    {
        
    }
    
    public static function createItem($itemType, 
                                       $itemID = NULL, 
                                       $tabPosition = NULL, 
                                       $style = NULL)
    {
        
    }
    
    public static function getItemValue($itemType, $itemID)
    {
        
    }
}