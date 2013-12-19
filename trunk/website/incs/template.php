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

// ESTA PAGINA NO DEBE SER ACCEDIDA DIRECTAMENTE POR EL USUARIO

/*
 * Esta página muestra ???????????????????
 */

if (!defined('CONFIG')) { require_once 'loadconfig.php'; }
    
session_do();

// Instanciar un token del formulario
$form_token = form_token_get_new();

// Recuperar datos de login
$sessionkey = session_get_sessionkey();
$usuario = session_get_username();

if (page_token_validate(get_get_pagetkn()) && 
        (form_token_validate(post_get_frmtkn()) !== FALSE) &&
        fingerprint_token_validate() &&
        sessionkey_validate($usuario, $sessionkey)) {
    // Login OK 
    
    
    
    
    // Guardar el form token para validar
    session_set_frmtkn(form_token_get_formtkn($form_token));
}
else
{
    // Error de autenticacion
    //
    session_terminate();
    session_do();
    session_set_errt(SMP_ERR_AUTHFAIL);
    $redirect = SMP_LOC_NAV;  
    $params = 'accion=logout';
}

if (isset($redirect)) {
    page_goto($redirect, $params);
    exit();
}
?>

<?php  echo page_get_head('SiMaPe - Mi perfil de usuario', 'main.css'); ?>
    <?php echo page_get_header(); ?>
    <!-- nav-bar -->
    <?php echo page_get_navbar(SMP_LOC_USUARIO) ?>
    <!-- /nav-bar -->
    <br />
    <form style="text-align: center; margin: 0px auto; width: auto;" 
          method="POST" >
        <fieldset style="text-align: center; width: 30%; margin:0 auto;">
            
            <input type="hidden" name="form_token" 
                   value="<?php echo form_token_get_randtkn($form_token); ?>" />
        </fieldset>
    </form>
<?php echo page_get_footer(); ?>