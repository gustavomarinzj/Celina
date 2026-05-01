<?php
session_start();

if (isset($_SESSION['logged_in'])) {
	session_unset();
	session_destroy();
	session_regenerate_id(true);

	header("location: index.php");
	exit;
}

?>