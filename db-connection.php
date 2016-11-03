<?php

// Conexión a la base de datos.
function db_connection() {
	$hostname = '';
	$username = '';
	$password = '';
	$database = '';
	$port = 3306;
	$socket = ini_get('mysqli.default_socket');
	$conn = mysqli_connect($hostname, $username, $password, $database, $port, $socket);
	if (mysqli_connect_errno($conn)) {
	    die("Connection failed: " . mysqli_connect_error());
	} else {
		return $conn;
	}
}