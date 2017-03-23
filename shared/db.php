<?php

$dbhost           = "mysql.binarybottle.com"; // mysql host server
$dbname           = "qovert";
$dbuser           = "qoverter";
$dbpass           = "qovertizes";

$dbh = mysql_connect( $dbhost, $dbuser, $dbpass )
                      or die (mysql_error());
$db  = mysql_select_db($dbname, $dbh)
                      or die (mysql_error());
mysql_query("SET NAMES 'utf8'"); // unicode support on!

?>