<?php

require_once("PersonDBLib/connect.php");
require_once("rsa.php");

$keypair = $GLOBALS["SESSION_KEYPAIR"];

$conn = new_connection();

if ($argv[1] == "new") {
	# Create a new user
	$user = $argv[2];
	$pass = $argv[3];

	$encuser = $keypair->encrypt($user);
	$encpass = $keypair->encrypt($pass);
	
}


?>
