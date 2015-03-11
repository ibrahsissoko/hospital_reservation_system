<?php
    set_include_path("./classes");
    spl_autoload_register(function ($class) {
        include 'classes/' . $class . '.php';
    });

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

	$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'); 

    try { 
    	$db = new PDO("mysql:host=" . $dbhost . ";port=" . $dbport . ";dbname=" . $db_name . ";charset=utf8",
            $dbusername,
            $dbpassword,
            $options);

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        header('Content-Type: text/html; charset=utf-8');

        session_start();

    } catch(PDOException $ex) {

    } 

?>
