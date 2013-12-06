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

require_once 'loadconfig.php';
?>

<?php echo page_get_head('SiMaPe - TESTING'); ?>
<?php echo page_get_body(); ?>
<?php echo page_get_header(); ?>
<?php echo page_get_header_close(); ?>
<?php echo page_get_navbarV(); ?>
<?php echo page_get_main(); ?>

<?php $query=//"delete from Oficina";
        $db = new mysqli;
        db_connect_rw($db);
        var_dump(db_query_prepared_transaction($db, $query));//, 'ss', array('1', '52')));
        $db->close();
        ?>

<?php echo page_get_main_close(); ?>
<?php echo page_get_footer(); ?>