<?php

function generar_token_csrf() {
	if (empty($_SESSION['token_csrf'])) {
    $_SESSION['token_csrf'] = bin2hex(random_bytes(32)); // Generate a secure token
  } 	
}

function regenerar_token_csrf() {
	if (isset($_SESSION['token_csrf'])) {
		unset($_SESSION['token_csrf']);
	}
	generar_token_csrf();
}

?>