<?php

require_once("PersonDBLib/auth/queries.php");
require_once("rsa.php");

/// https://github.com/KSA-De-Biekorf/ICT-draaiboek/blob/aa611c53018ba30b88ac9e0e67acfc4baa53185c/auth/flow.md
class Auth {
  public function __construct() {}
  
  public function authenticate(string $user, string $pass): bool {
    return $user == "test" && $pass == "123";
  }

  // Returns a base64 encoded token
  public function new_token($conn, $userd, $c_pubkey): TokenResponse {
    $resp = new TokenResponse();
    
    $token = random_bytes(256);
    $tokenBase64 = base64_encode($token);
    $resp->token = $tokenBase64;

    // Store in database
    if ($conn == null) {
      // DEBUG
      error_log("SEVERE ERROR: Connection is null. The session token will not work");
      return $resp;
    }
		$session_keypair = $GLOBALS["SESSION_KEYPAIR"];
    $encuser = $session_keypair->encrypt($userd->user);
    // $encpass = $session_keypair->encrypt($userd->pass);
    $usr64 = base64_encode($encuser);
    // $pass64 = base64_encode($encpass);
    $result = query_user_id($conn, $usr64);
    if (!$result) {
      throw new ErrorException("Token creation error", 200);
    }
    $row = $result->fetch_assoc();
		if ($row == null) throw new ErrorException("User does not exist", 202); 
    $id = $row[0];
    $resp->userID = $id;
    
    if (!add_session_token($conn, $tokenBase64, $id, $c_pubkey)) {
      throw new ErrorException("Token could not be added to the database", 201);
    }
    
    return $resp;
  }
}

class TokenResponse {
  public $token;
  public $userID;
}

#$auth = new Auth();
$GLOBALS["AUTH"] = new Auth();

?>
