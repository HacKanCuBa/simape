<?php

/* 
 * NO EDITAR
 */

/* Data base details */
$dsn; //DSN
$db_user; //DB username 
$db_pass; //DB password 
$driver; //Integration driver
$db_prefix; //prefix used for tables in database
$uid; //Any random unique number

$connected; //only for custom installation

$PATH; // Use this only if you have placed the freichat folder somewhere else
$installed; //make it false if you want to reinstall freichat
$admin_pswd; //backend password 

$debug;
$custom_error_handling; // used during custom installation

$use_cookie;

/* email plugin */
$smtp_username;
$smtp_password;

$force_load_jquery;

/* Custom driver */
$usertable; //specifies the name of the table in which your user information is stored.
$row_username; //specifies the name of the field in which the user's name/display name is stored.
$row_userid; //specifies the name of the field in which the user's id is stored (usually id or userid)


$avatar_table_name; //specifies the table where avatar information is stored
$avatar_column_name; //specifies the column name where the avatar url is stored
$avatar_userid; //specifies the userid  to the user to get the user's avatar
$avatar_reference_user; //specifies the reference to the user to get the user's avatar in user table 
$avatar_reference_avatar; //specifies the reference to the user to get the user's avatar in avatar



