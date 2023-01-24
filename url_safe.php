<?php

function url_safe_to_base64($url_safe) {
	return strtr($url_safe, '._-', '+/=');
}

?>
