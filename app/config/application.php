<?php
/**
 * Application Configurations
 *
 * Definições globais da aplicação.
 *
 * @package				app.config
 * @since 				Neleus 0.1
 * @version 			0.4
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 */

//$app['mode'] = 1;
//$app['prefix'] = '';
//$app['404_error'] = 'NotFound';
//$app['encoding'] = 'utf-8';
//$app['auto_session'] = false;
//$app['display_benchmark'] = true;
//$app['display_errors'] = true;

/**
 * Globals
 */
$app['hour_diff'] = '+5 minutes +12 seconds';
$app['main_page'] = 'Begin';

/**
 * Pagination
 */
global $pagination;

$pagination['rows_per_page'] = 15;
$pagination['numbers_per_page'] = 7;

/**
 * Twitter
 */
global $twitter;

$twitter['url'] = 'http://twitter.com/';
$twitter['request_interval'] = 24; // seconds

/**
 * Shoutcast auth
 */
global $shoutcast;

$shoutcast['status'] = 0;
$shoutcast['host'] = '127.0.0.1'; //'208.115.232.115';
$shoutcast['port'] = '80'; //7042;
$shoutcast['password'] = 'radiotoke';
?>