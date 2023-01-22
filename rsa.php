<?php

// echo base64_encode($session_keypair->public_key_pem);

class RSAKeys {
  private $private_key;
  public $public_key_pem;
  private $public_key;

  public function __construct() {
    $pubpem_file_loc = dirname(__FILE__)."/keys/public.pem";
    $pubpem_file = fopen($pubpem_file_loc, "r") or die("unreachable");
    $this->public_key_pem = fread($pubpem_file, filesize($pubpem_file_loc));
    fclose($pubpem_file);
    $this->public_key = openssl_get_publickey($this->public_key_pem);

    $privkey_file_loc = dirname(__FILE__)."/keys/private.pem";
    $this->private_key = openssl_get_privatekey(file_get_contents($privkey_file_loc));
  }

  // decrypt base64 encoded data
  public function decrypt($data) {
    if (openssl_private_decrypt(base64_decode($data), $decrypted, $this->private_key)) {
      return $decrypted;
    } else {
      throw new ErrorException("Couldn't decrypt", 100);
    }
  }

  public function encrypt($data) {
    if (openssl_public_encrypt($data, $encrypted, $this->public_key)) {
      return $encrypted;
    } else {
      throw new ErrorException("Couldn't encrypt", 101);
    }
  }
}

$session_keypair = new RSAKeys();

?>
