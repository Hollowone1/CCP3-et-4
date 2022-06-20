<?php 
	session_start();

	// connect to database
       $conn = new PDO ('mysql:host=localhost;dbname=blog', 'root', '');
      
       if (!$conn) {
		die("Error connecting to database: ");
	}

	define ('ROOT_PATH', realpath(dirname(__FILE__)));
	define('BASE_URL', 'http://localhost/blog/');
?>