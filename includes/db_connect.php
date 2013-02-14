<?php
// Database Connection
$connection = mysql_connect("$db_host","$db_username","$db_password")
 or die ("Couldn't connect to server");
mysql_select_db("$db_name", $connection)
 or die("Couldn't select database");
?>