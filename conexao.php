<?php

$db_banco = '';
$db_server = '';
$db_user = '';
$db_pass = '';

$db = mysql_connect($db_server, $db_user, $db_pass) or die(mysql_error());

$banco = mysql_select_db($db_banco, $db) or die(mysql_error());



?>
