<?php
/**
 * Application Configurations
 *
 * Definições globais da aplicação.
 *
 * @package				core.config
 * @since 				Neleus 0.1
 * @version 			0.4
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 */

if (file_exists($app['path'].CONFIG.'application.php'))
	include_once $app['path'].CONFIG.'application.php';

// Application mode / 0 = Development; 1 = Production
if (!isset($app['mode'])) {
	$app['mode'] = $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == $_SERVER['REMOTE_ADDR']? 0 : 1;
}

// Classes prefix
if (!isset($app['prefix']))
	$app['prefix'] = '';

// Main page of application
if (!isset($app['main_page']))
	$app['main_page'] = 'Main';

// Default not found error name
if (!isset($app['404_error']))
	$app['404_error'] = 'NotFound';

// Default encoding of application
if (!isset($app['encoding']))
	$app['encoding'] = 'utf-8';

// Determines if session_start() will be called automatically
if (!isset($app['auto_session']))
	$app['auto_session'] = false;

// Determines if benchmark will be displayed
if (!isset($app['display_benchmark']))
	$app['display_benchmark'] = $app['mode']? false : true;

// Determines if PHP and Neleus errors will be displayed
if (!isset($app['display_errors']))
	$app['display_errors'] = $app['mode']? false : true;

// Set the application base URL
if (!isset($app['url'])) {
	$s = isset($_SERVER['HTTPS'])? 's' : null;
	$host = $_SERVER['HTTP_HOST'];
	$path = dirname(dirname(dirname($_SERVER['PHP_SELF'])));
	$path = preg_replace('@/$@', '', $path);
	$app_dir = nice_dir($app['path']);

	if (preg_match("@^$path$app_dir@", $_SERVER['REQUEST_URI']) || count(Application::$apps) > 1) {
		$path .= "$app_dir";
	}

	$app['url'] = "http$s://$host$path";

	unset($path, $host, $s, $app_dir);
}
?>