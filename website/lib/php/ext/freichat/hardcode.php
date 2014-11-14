<?php
require_once 'load.php';
$session = new Session;
$session->useSystemPassword();

$db = new DB(SMP_DB_CHARSET);

$usuario = new Usuario($db, $session->retrieveEnc(SMP_SESSINDEX_USERNAME));
$chat = new Chat(SMP_DB_HOST, SMP_DB_NAME, SMP_DB_USER_CHAT, SMP_DB_PASS_CHAT, SMP_CHAT_ADMINPASS, $usuario->getUsuarioId());