<?php
function is_ie6() {
	$browser = $_SERVER['HTTP_USER_AGENT'];
	
	if (strpos($browser, 'MSIE 6.0') !== false && strpos($browser, 'MSIE 7.0') === false) return true;
	else return false;
}
?>