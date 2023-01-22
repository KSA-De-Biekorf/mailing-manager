<?php

require_once("PersonDBLib/connect.php");
require_once("PersonDBLib/auth/queries.php");
require_once("rsa.php");

$keypair = $GLOBALS["SESSION_KEYPAIR"];

$conn = new_connection();

if ($argv[1] == "new") {
	# Create a new user
	$user = $argv[2];
	$pass = $argv[3];

	$encpass = $keypair->encrypt($pass);
	$user64 = base64_encode($user);
	$pass64 = base64_encode($encpass);

	if (!add_user($conn, $user64, $pass64)) {
		die("an error occured: ".$conn->error);
	} else {
		echo "User created\n";
	}
} else if ($argv[1] == "exists") {
	# Check if user exists
	$user = $argv[2];

	$user64 = base64_encode($user);

	$result = query_user_id($conn, $user64);
	$has_id = false;
	while ($row = $result->fetch_assoc()) {
		$has_id = true;
		echo "User id: ";
		echo $row[0];
	}

	if (!$has_id) {
		echo "User does not exist\n";
	}
} else if ($argv[1] == "record") {
	# get user record
	$user = $argv[2];

	$user64 = base64_encode($user);
	
	$userS = $conn->real_escape_string($user64);
	$result = $conn->query("SELECT * FROM auth_Users WHERE user = '$userS'");
	if (!$result) {
		echo "An error occured: " . $conn->error . "\n";
		return;
	}
	while ($row = $result->fetch_assoc()) {
		print_r($row);
	}
} else if ($argv[1] == "decode") {
	$base64 = $argv[2];
	echo base64_decode($base64) . "\n";
} else if ($argv[1] == "decode-decrypt") {
	$base64 = $argv[2];
	echo $keypair->decrypt($base64) . "\n";
} else {
	echo "Unknown command: ".$argv[1]."\n";
}


?>
