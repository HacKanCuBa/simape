<?php
/*
 * ¡¡NO MODIFICAR!!
 */
require_once 'load.php';
$dsn = 'mysql:host=' . SMP_DB_HOST . ';dbname=' . SMP_DB_NAME;
$db_user = SMP_DB_USER_CHAT;
$db_pass = SMP_DB_PASS_CHAT;
$driver = 'Custom';
$db_prefix = '';
$uid = Chat::generateUid();
$connected='YES';
$PATH = 'freichat/'; 
$installed = TRUE; 
$admin_pswd = SMP_CHAT_ADMINPASS; 
$debug = FALSE;
$custom_error_handling='YES'; 
$use_cookie='false';
$smtp_username = SMP_EMAIL_USER;
$smtp_password = SMP_EMAIL_PSWD;
$force_load_jquery = 'NO';
$usertable='frei_users';
$row_username='Nombre';
$row_userid='Id';
$avatar_table_name='members';
$avatar_column_name='avatar';
$avatar_userid='id';
$avatar_reference_user='id';
$avatar_reference_avatar='id';
//to avoid unnecessary file changes , *do not change
$avatar_field_name=$avatar_column_name; //
