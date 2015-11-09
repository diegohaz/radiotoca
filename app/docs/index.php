<?php
/**
 * Neleus Application Docs Index
 *
 * @package				app.docs
 * @since 				Neleus 0.1
 * @version 			0.4.2
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

// Set the application start time for benchmarking
$app_start_time = microtime(true);

// Directory definitions
if (!defined('DS')) 	define('DS', '/');
if (!defined('WWW'))	define('WWW', dirname(dirname(dirname(__FILE__))).DS);
if (!defined('CORE'))	define('CORE', WWW.'core'.DS);

// Application config
$app['path'] = dirname(dirname(__FILE__)).DS;

// Require stuff
require CORE.'config'.DS.'dir.php';
require CORE.LIBRARIES.'basics.php';
require CORE.LIBRARIES.'Application.php';

// Start application
$application = new Application($app['path']);
$application->run();
?>