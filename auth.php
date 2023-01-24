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
    #$encuser = $session_keypair->encrypt($userd->user);
    // $encpass = $session_keypair->encrypt($userd->pass);
    $usr64 = base64_encode($userd->user);
    // $pass64 = base64_encode($encpass);
    $result = query_user_id($conn, $usr64);
    if (!$result) {
      throw new ErrorException("Token creation error", 200);
    }
    $row = $result->fetch_assoc();
		if ($row == null) throw new ErrorException("User does not exist", 202); 
    $id = reset($row); // get first element in array
    $resp->userID = $id;
    
    if (!add_session_token($conn, $tokenBase64, $id, $c_pubkey)) {
      throw new ErrorException("Token could not be added to the database", 201);
    }
    
    return $resp;
  }

  // Verify a request with token, signature and userid
  // - $token: base64 encoded
  // - $signature: signature of the token, not base64 encoded
  // - $userid: the user id the token belongs to
  public function verify_request($conn, string $token, string $signature, int $userid): bool {
    $token_entries = query_token($conn, $userID);
    if (!$token_entries) {
      throw new ErrorException("Invalid token for use $userID, or the token has expired", 498);
    }
    $token_entry = $token_entries->fetch_assoc();
    $stored_token = $token_entry["token"];
    $public_key_pem = base64_decode($token_entry["public_key"]); # is base6' encoded in db
    $public_key = openssl_get_publickey($public_key_pem);
    if (!$public_key) {
      error_log(openssl_error_string());
      throw new ErrorException("Could not make public key", 500);
    }

    # todo: public_key_pem to public_key
    $is_correct_client = openssl_verify($token, $signature, $public_key_pem, "sha256");
    if (!$is_correct_client) {
      throw new ErrorException("Unauthorized", 401);
    }

    if ($token == $stored_token) {
      return true;
    } else {
      throw new ErrorException("Invalid token", 498);
    }
  }
}

class TokenResponse {
  public $token;
  public $userID;
}

$GLOBALS["AUTH"] = new Auth();

//--------------------------------------

function authenticate

?>
