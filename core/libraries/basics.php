<?php
if (file_exists($app['path'].LIBRARIES.'basics.php')) {
	include $app['path'].LIBRARIES.'basics.php';
}

/**
 * Basics
 *
 * Funções básicas do núcleo do framework.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1
 * @version 			0.4.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/10/2010
 */

if (!function_exists('parse_class')):
/**
 * Analisa a classe e o prefixo definido na configuração.
 *
 * @since 				Neleus 0.2.7
 * @version 			0.3
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/10/2010
 *
 * @param $class (string) - Nome da classe.
 * @param $prefix (string) - Prefixo da classe.
 *
 * @return String com o nome da classe devidamente convertido (com o prefixo incluído) ou FALSE caso a classe
 * não exista.
 */
function parse_class($class, $prefix = null) {
	global $application;

	if (!isset($prefix) && isset($application->config)) {
		$prefix = $application->config['prefix'];
	}

	if ($prefix) {
		if (class_exists($prefix.$class)) {
			return $prefix.$class;
		}
		elseif (class_exists($class)) {
			return $class;
		}
	}
	elseif (class_exists($class)) {
		return $class;
	}

	return false;
}
endif;

if (!function_exists('import')):
/**
 * Importa um source.
 *
 * @since 				Neleus 0.1
 * @version 			0.9
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/10/2010
 *
 * @uses basics::parse_class
 *
 * @param $file (string) - Nome da classe ou caminho para o arquivo.
 * @param $instantiate (boolean) - Determina se a classe será instanciada.
 *
 * @return A instância da classe, TRUE caso não seja para instanciá-la ou FALSE, em caso
 * de erro.
 */
function import($file, $instantiate = false) {
	global $application;

	if (!isset($application->imported['Application'])) {
		$application->imported['Application'][0] = true;
		$application->imported['Application'][1] = $application;
	}

	if (isset($application->imported[$file][$instantiate])) {
		return $application->imported[$file][$instantiate];
	}

	// Class already exists
	if ($instantiate && ($class = parse_class($file))) {
		$application->imported[$file][$instantiate] = new $class;
		return $application->imported[$file][$instantiate];
	}
	// Class doesn't exist, find the file
	elseif (file_exists($file_path = $application->path.LIBRARIES.$file.'.php')
			OR	file_exists($file_path = CORE.LIBRARIES.$file.'.php')) {
		include $file_path;
	}
	// It maybe a folder
	elseif (is_dir($file_path = $application->path.LIBRARIES.$file)
			OR	is_dir($file_path = CORE.LIBRARIES.$file)) {
		$scandir = scandir($file_path);

		foreach ($scandir as $filename) {
			if (ext($filename) == 'php') {
				include $file_path.$filename;
			}
		}
	}

	if ($instantiate) {
		if ($class = parse_class($file))
			$application->imported[$file][$instantiate] = new $class;
		else
			$application->imported[$file][$instantiate] = false;
	}
	else {
		$application->imported[$file][$instantiate] = true;
	}

	return $application->imported[$file][$instantiate];
}
endif;

if (!function_exists('url')):
/**
 * Retorna a url da página.
 *
 * @since 				Neleus 0.1.3
 * @version 			0.7
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	05/07/2010
 *
 * @uses basics::import
 * @uses Loader::page
 * @uses PageController::url
 *
 * @param $page (mixed) - Página cuja URL é requisitada.
 * @param $absolute_or_params (array|boolean) - Parâmetros a serem passados pra action ou parâmetro absolute.
 * @param $absolute (boolean) - Determina se a URL será retornada de forma absoluta.
 *
 * @return String com a URL da página ou FALSE caso a página não exista.
 */
function url($page = null, $absolute_or_params = true, $absolute = true) {
	$load = import('Loader', true);

	if ($page = $load->page($page)) {
		$url = $page->url($absolute_or_params, $absolute);

		return $url;
	}

	return false;
}
endif;

if (!function_exists('ext')):
/**
 * Retorna a extensão do arquivo especificado.
 *
 * @since 				Neleus 0.1
 * @version 			0.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	22/05/2010
 *
 * @param $filename (string) - Arquivo cuja extensão será identificada.
 *
 * @return String contendo a extensão do arquivo ou FALSE caso ela não exista.
 */
function ext($filename) {
	$fileinfo = pathinfo($filename);

	if (isset($fileinfo['extension']))
		return $fileinfo['extension'];
	else return false;
}
endif;

if (!function_exists('filename')):
/**
 * Retorna o nome do arquivo especificado.
 *
 * @since 				Neleus 0.1
 * @version 			0.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	22/05/2010
 *
 * @param $file (string) - Arquivo cujo nome será identificado.
 *
 * @return String contendo o nome do arquivo.
 */
function filename($file) {
	$fileinfo = pathinfo($file);

	if (isset($fileinfo['filename']))
		return $fileinfo['filename'];
	else
		return preg_replace('/\.[a-z0-9]+$/', '', $fileinfo['basename']);
}
endif;

if (!function_exists('nice_dir')):
/**
 * Retorna o diretório a partir da root e não absoluto, além de trocar \ por /.
 *
 * @since 				Neleus 0.1
 * @version 			0.3
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	16/07/2010
 *
 * @param $dir (string) - Diretório a ser tratado.
 *
 * @return String com o diretório alterado.
 */
function nice_dir($dir) {
	$dir = str_replace('\\', '/', $dir);
	$dir = str_replace(str_replace('\\', '/', WWW), '/', $dir);
	$dir = preg_replace('@/$@', '', $dir);

	return $dir;
}
endif;
?>