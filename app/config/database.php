<?php
/**
 * Database Configuration
 *
 * Configuração da base de dados.
 *
 * @package				app.config
 * @since 				Neleus 0.2.5
 * @version 			0.3.6
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	15/09/2010
 */
global $db;

$db['auto_connect'] = array('default');
$db['default_profile'] = 'default';

$db['default'] = array(
	'dbdriver' => 'mysqli',
	'hostname' => 'localhost',
	'username' => 'root',
	'password' => '',
	'database' => 'radiotoca'
);
?>