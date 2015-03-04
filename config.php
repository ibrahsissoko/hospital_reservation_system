<?php 
	define('DB_HOST', getenv('OPENSHIFT_MYSQL_DB_HOST'));
	define('DB_PORT', getenv('OPENSHIFT_MYSQL_DB_PORT'));
	define('DB_USER', getenv('OPENSHIFT_MYSQL_DB_USERNAME'));
	define('DB_PASS', getenv('OPENSHIFT_MYSQL_DB_PASSWORD'));
	define('DB_NAME', getenv('OPENSHIFT_GEAR_NAME'));

	$dbhost = constant("DB_HOST"); // Host name 
	$dbport = constant("DB_PORT"); // Host port
	$dbusername = constant("DB_USER"); // Mysql username 
	$dbpassword = constant("DB_PASS"); // Mysql password 
	$db_name = "wal";//"constant("DB_NAME")"; // Database name 

	$conn = mysqli_connect($dbhost, $dbusername, $dbpassword, $db_name, $dbport) or die("Error: no connection");
?>
