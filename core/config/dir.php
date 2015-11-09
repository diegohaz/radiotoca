<?php
/**
 * Directory Configuration
 *
 * Definição dos diretórios do núcleo do framework e da aplicação.
 *
 * @package				core.config
 * @since 				Neleus 0.1
 * @version 			0.5
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

// Directory separator
if (!defined('DS'))
	define('DS', '/');

// Base directory
if (!defined('WWW'))
	define('WWW', dirname(dirname(dirname(__FILE__))).DS);

// Neleus core directory
if (!defined('CORE'))
	define('CORE', WWW.basename(dirname(dirname(__FILE__))).DS);

// General config directory
if (!defined('CONFIG'))
	define('CONFIG', basename(dirname(__FILE__)).DS);

// Libraries directory
if (!defined('LIBRARIES'))
	define('LIBRARIES', 'libraries'.DS);

// Controllers directory
if (!defined('CONTROLLERS'))
	define('CONTROLLERS', 'controllers'.DS);

// Views directory
if (!defined('VIEWS'))
	define('VIEWS', 'views'.DS);

// Models directory
if (!defined('MODELS'))
	define('MODELS', 'models'.DS);

// Docs directory
if (!defined('DOCS'))
	define('DOCS', 'docs'.DS);

// Generic layer type
if (!defined('GENERIC'))
	define('GENERIC', 'generic'.DS);

// Pages layer type
if (!defined('PAGES'))
	define('PAGES', 'pages'.DS);

// Errors layer type
if (!defined('ERRORS'))
	define('ERRORS', 'errors'.DS);

// Data layer type
if (!defined('DATA'))
	define('DATA', 'data'.DS);

// Modules layer type
if (!defined('MODULES'))
	define('MODULES', 'modules'.DS);

// Controllers internal directories
$dir['controllers']['generic'] = GENERIC;
$dir['controllers']['pages'] = PAGES;
$dir['controllers']['errors'] = ERRORS;
$dir['controllers']['data'] = DATA;
$dir['controllers']['modules'] = MODULES;

// Models internal directories
$dir['models']['generic'] = '';
$dir['models']['pages'] = '';
$dir['models']['errors'] = '';
$dir['models']['data'] = '';
$dir['models']['modules'] = MODULES;

// Views internal directories
$dir['views']['generic'] = '';
$dir['views']['pages'] = '';
$dir['views']['errors'] = '';
$dir['views']['data'] = '';
$dir['views']['modules'] = MODULES;
?>