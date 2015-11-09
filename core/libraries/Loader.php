<?php
if (!class_exists('Loader')):
/**
 * Loader Class
 *
 * Classe de carregamento.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1.3
 * @version 			0.9.9
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/10/2010
 */

class Loader {

	private $app;
	private $config;

	public $object;

	/**
	 * Inicia o objeto Loader e passa o parâmetro.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @uses basics::import
	 *
	 * @param $object (object) - Objeto ao qual o Loader está sendo anexado.
	 */
	public function __construct($object = null) {
		$this->config = import('Config', true);
		$this->app = import('Application', true);

		if (isset($object)) {
			$this->object = $object;
		}
	}

	/**
	 * Faz o carregamento dos arquivos de configuração e os passa para o objeto Config.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.5
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	25/07/2010
	 */
	public function config() {
		global $app;

		$_offset = count($GLOBALS);
		$scandir[CORE.CONFIG] = scandir(CORE.CONFIG);
		$scandir[$app['path'].CONFIG] = scandir($app['path'].CONFIG);

		foreach ($scandir as $_path => $_files) {
			foreach ($_files as $_file) {
				if (ext($_file) == 'php') {
					global ${filename($_file)};
					include $_path.$_file;
				}
			}
		}

		$configs['app'] = $app;
		$configs['dir'] = $dir;
		$configs['autoload'] = $autoload;
		$configs['db'] = $db;
		$configs += array_slice($GLOBALS, $_offset);

		foreach ($configs as $attr => $value) {
			$this->config->$attr = $value;
		}
	}

	/**
	 * Carrega uma aplicação.
	 *
	 * @since 				Neleus 0.3
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/10/2010
	 *
	 * @uses Application::$apps
	 * @uses Application::__construct
	 * @uses Application::run
	 * @uses Loader::set
	 *
	 * @param $name (string) - Aplicação a ser carregada.
	 * @param $property (string) - Nome da propriedade a ser criada no objeto corrente.
	 *
	 * @return O objeto Application da aplicação carregada ou FALSE em caso de erro.
	 */
	public function app($name, $property = null) {
		if (isset(Application::$apps[$name])) {
			return Application::$apps[$name];
		}
		elseif ($this->config['mode'] || is_dir(WWW.$name)) {
			global $app, $application;

			foreach ($app as $attr => $value) {
				unset($app[$attr]);
			}

			// Application config
			$app['path'] = WWW.$name.DS;

			// Require stuff
			require CORE.LIBRARIES.'basics.php';
			require CORE.LIBRARIES.'Application.php';

			// Start application
			$application = new Application($app['path']);
			$application->run();

			$this->set($property, $name, $application);

			return $application;
		}

		return false;
	}

	/**
	 * Carrega determinado arquivo da biblioteca e instancia sua classe, se existir.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/10/2010
	 *
	 * @uses basics::import
	 * @uses Loader::set
	 *
	 * @param $name (string|array) - Arquivo(s) a ser(em) carregado(s).
	 * @param $property (string|array) - Nome da propriedade a ser criada no objeto corrente.
	 *
	 * @return O objeto da biblioteca carregada ou FALSE em caso de erro ou caso a classe não
	 * exista.
	 */
	public function library($name, $property = null) {
		if (is_string($name)) {
			global $application;

			$application = $this->app;
			$instantiate = $property? true : false;
			$library = import($name, $instantiate);

			if (is_object($library)) {
				$this->set($property, $name, $library);
			}

			return $library;
		}
		elseif (is_array($name)) {
			$libraries = array();

			foreach ($name as $i => $library) {
				if (is_array($property) && isset($property[$i])) {
					$prop = $property[$i];
				}
				else {
					$prop = null;
				}

				$libraries[] = $this->library($library, $prop);
			}

			return $libraries;
		}
	}

	/**
	 * Faz o carregamento das páginas da aplicação.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @uses Loader::page
	 * @uses Loader::library
	 * @uses basics::ext
	 * @uses basics::parse_class
	 * @uses PageController::__construct
	 *
	 * @return Os objetos PageController das páginas.
	 */
	public function pages() {
		$args = func_get_args();

		if ($args) {
			return call_user_func_array(array($this, 'page'), $args);
		}
		elseif (!$this->app->pages) {
			$app_pages_path = $this->config['path'].CONTROLLERS.$this->config->dir['controllers']['pages'];
			$core_pages_path = CORE.CONTROLLERS.PAGES;

			// Scans directories and removes '.' and '..' pseudofiles
			$scandir[$app_pages_path] = array_slice(scandir($app_pages_path), 2);
			$scandir[$core_pages_path] = array_slice(scandir($core_pages_path), 2);
			$offset = count(get_declared_classes());

			foreach ($scandir as $path => $files) {
				// If directory has a file with its same name .php, include only this file
				if (file_exists($path.basename($path).'.php')) {
					ini_set('include_path', $path);

					include_once $path.basename($path).'.php';

					ini_restore('include_path');
					continue;
				}

				foreach ($files as $file) {
					if (ext($file) == 'php') {
						include_once $path.$file;
					}
				}
			}

			// Get only classes declared in included files
			$classes = get_declared_classes();
			$classes = array_slice($classes, $offset);

			foreach ($classes as $class) {
				if (is_subclass_of($class, parse_class('PageController'))) {
					$name = $class;

					if ($this->config['prefix']) {
						$name = preg_replace('/^'.$this->config['prefix'].'/', '', $class);
					}

					$this->app->pages[$name] = new $class;
				}
			}
		}

		return $this->app->pages;
	}

	/**
	 * Carrega uma página existente.
	 *
	 * @since 				Neleus 0.1.3
	 * @version 			1.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Loader::set
	 * @uses basics::parse_class
	 *
	 * @param $name (string|array) - Arquivo(s) a ser(em) carregado(s).
	 * @param $property (string|array) - Nome da propriedade a ser criada no objeto corrente.
	 *
	 * @return O objeto PageController da página ou FALSE em caso de erro.
	 */
	public function page($name = null, $property = null) {
		if (!isset($name)) {
			$name = $this->config['main_page'];
		}

		if (is_string($name) && $name != 'PageController') {

			// Is it an direct access attempt to a page holder? e.g. 'Main'
			if (isset($this->app->pages[$name])) {
				$page = $this->app->pages[$name];
			}

			// Is it an direct access attempt to an action? e.g. 'Main.index'
			elseif (strpos($name, '.') !== false) {
				$names = explode('.', $name);
				list($page, $action) = $names;

				if (isset($this->app->pages[$page]->actions[$action])) {
					$page = $this->app->pages[$page]->actions[$action];
				}
				else return false;
			}

			// Was $name passed like URI?
			elseif (strpos($name, '/') !== false || empty($name)) {
				$router = import('Router', true);

				$page = $router->page($name);
			}
			elseif (!$page = $this->controller($name, $property, 'page')) {
				return false;
			}

			if ($page) {
				$this->set($property, $name, $page);

				return $page;
			}
		}
		elseif (is_array($name)) {
			$pages = array();

			foreach ($name as $i => $page) {
				if (is_array($property) && isset($property[$i])) {
					$prop = $property[$i];
				}
				else {
					$prop = null;
				}

				$pages[] = $this->page($page, $prop);
			}

			return $pages;
		}
		elseif (is_object($name) && ($class = parse_class('PageController')) && $name instanceof $class) {
			return $name;
		}

		return false;
	}

	/**
	 * Loader action
	 *
	 * Carrega uma determinada action.
	 *
	 * @package				core.libraries.loader
	 * @since 				Neleus 0.2.1
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @modifiedby 		Diego Haz
	 * @lastmodified	04/07/2010
	 *
	 * @uses Loader::page
	 *
	 * @param $name (string) - Action a ser carregada.
	 * @param $property (string) - Nome da propriedade a ser criada no objeto corrente.
	 *
	 * @return O objeto PageController da action ou FALSE em caso de erro.
	 */
	public function action($name, $property = null) {
		if (strpos($name, '.') === false) {
			$name = $this->object->name.'.'.$name;
		}

		return $this->page($name, $property);
	}

	/**
	 * Carrega um determinado controller.
	 *
	 * @since 				Neleus 0.1.3
	 * @version 			1.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/10/2010
	 *
	 * @uses basics::parse_class
	 * @uses Loader::set
	 *
	 * @param $name (string|array) - Controller a ser carregado.
	 * @param $property (string|array) - Nome da propriedade a ser criada no objeto corrente.
	 * @param $type (string) - Determina o tipo de controller que será carregado.
	 *
	 * @return O objeto Controller ou FALSE em caso de erro.
	 */
	public function controller($name, $property = null, $type = 'generic') {
		if (is_string($name))	{
			$this->parseDir('controllers', $type, $app_path, $core_path, $attr);

			if (isset($this->app->{$attr}[$name])) {
				$controller = $this->app->{$attr}[$name];
			}
			else {
				if (!$class = parse_class($name)) {
					if (file_exists($controller_path = $app_path.$name.'.php')
					OR  file_exists($controller_path = $core_path.$name.'.php')) {
						include_once $controller_path;
					}
					else {
						$this->app->{$attr}[$name] = false;
					}
				}

				if ($class || ($class = parse_class($name))) {
					if (is_subclass_of($class, parse_class($type.'Controller'))) {
						$controller = $this->app->{$attr}[$name] = new $class;
					}
					else return false;
				}
				else return false;
			}

			$this->set($property, $name, $controller);

			return $controller;
		}
		elseif (is_array($name)) {
			$controllers = array();

			foreach ($name as $i => $controller) {
				if (is_array($property) && isset($property[$i])) {
					$prop = $property[$i];
				}
				else {
					$prop = null;
				}

				$controllers[] = $this->controller($controller, $prop, $type);
			}

			return $controllers;
		}

		return false;
	}

	public function controllers($name, $property = null) {
		return $this->controller($name, $property);
	}

	/**
	 * Carrega um determinado erro.
	 *
	 * @since 				Neleus 0.1.3
	 * @version 			0.6
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @uses basics::parse_class
	 * @uses Loader::library
	 * @uses Loader::controller
	 *
	 * @param $name (string|array) - Erro a ser carregado.
	 * @param $property (string|array) - Nome da propriedade a ser criada no objeto corrente.
	 *
	 * @return O objeto ErrorController ou FALSE em caso de erro.
	 */
	public function error($name, $property = null) {
		if (!parse_class('ErrorController')) {
			$this->library('ErrorController');
		}

		return $this->controller($name, $property, 'error');
	}

	public function errors($name, $property = null) {
		if (!parse_class('ErrorController')) {
			$this->library('ErrorController');
		}

		return $this->controller($name, $property, 'error');
	}

	/**
	 * Carrega um determinado controlador de dados.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.5
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses basics::parse_class
	 * @uses Loader::library
	 * @uses Loader::controller
	 * @uses DataController::__construct
	 * @uses Loader::set
	 *
	 * @param $name (string|array) - Controlador de dados a ser carregado.
	 * @param $property (string|array) - Nome da propriedade a ser criada no objeto corrente.
	 *
	 * @return O objeto DataController ou FALSE em caso de erro.
	 */
	public function data($name, $table = null) {
		if (!parse_class('DataController')) {
			$this->library('DataController');
		}

		$data = $this->controller($name, $name, 'data');

		if (!$data) {
			$data = new DataController($name, $table);

			$this->set($name, $name, $data);
		}

		return $data;
	}

	/**
	 * Carrega um determinado módulo.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @uses basics::parse_class
	 * @uses Loader::library
	 * @uses Loader::controller
	 *
	 * @param $name (string|array) - Módulo a ser carregado.
	 * @param $property (string|array) - Nome da propriedade a ser criada no objeto corrente.
	 *
	 * @return O objeto ModuleController ou FALSE em caso de erro.
	 */
	public function module($name, $property = null) {
		if (!parse_class('ModuleController')) {
			$this->library('ModuleController');
		}

		return $this->controller($name, $property, 'module');
	}

	public function modules($name, $property = null) {
		if (!parse_class('ModuleController')) {
			$this->library('ModuleController');
		}

		return $this->controller($name, $property, 'module');
	}

	/**
	 * Método mágico para carregar os controllers criados pelo usuário (FooController).
	 *
	 * @since 				Neleus 0.3
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses basics::parse_class
	 * @uses Loader::library
	 * @uses Loader::controller
	 */
	public function __call($method, $args) {
		$class = ucfirst($method).'Controller';

		if (!parse_class($class)) {
			$this->library($class);
		}

		$name = $args[0];
		$property = isset($args[1])? $args[1] : null;

		return $this->controller($name, $property, $method);
	}

	/**
	 * Carrega uma view.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @uses basics::parse_class
	 * @uses basics::filename
	 * @uses Loader::parseDir
	 *
	 * @param $view_source (string|array) - Parâmetro opcional para carregar outras views além da padrão.
	 * @param $get_output (boolean) - Determina se o output da view será retornado.
	 *
	 * @return Objeto View, output da view caso $get_output seja verdadeiro ou FALSE em caso de erro.
	 */
	public function view($view_source = null, $get_output = false) {
		$view = false;
		$controller = $this->object? $this->object : new stdClass;

		if (!isset($view_source)) { # Load default view
			if (!isset($controller->view)) {
				$view_source =& $controller->viewSource;
				$controller->view =& $view;
			}
			else return $controller->view;
		}
		else {
			if (is_array($view_source)) { # Load another view in another path array('path' => 'view')
				$type = key($view_source);
				$view_source = current($view_source);
			}
			elseif ($view_source instanceof View) # Load another view by object
				$view_source = $view_source->source;

			if (is_string($view_source)) { # Load another view by source
				$controller->currentViewSource =& $view_source;
				$controller->currentView =& $view;
			}
		}

		if (!$view_source) { # No view specified
			if (isset($controller->name)) {
				$filename = strtolower($controller->name);
				$file = $filename.'.php';
			}
			else return false;
		}
		else {
			$view_source = preg_replace('/\.php$/', '', $view_source).'.php';
			$fileinfo = pathinfo($view_source);
			$dirname = preg_replace('/^[\.\/](?!\.)/', '$1', $fileinfo['dirname']);
			$dirname = $dirname? $dirname.DS : '';
			$filename = $dirname.filename($view_source);
			$ext = isset($fileinfo['extension'])? '.'.$fileinfo['extension'] : '.php';
			$file = $filename.$ext;
		}

		if (!isset($type)) {
			$type = isset($controller->type)? $controller->type : 'generic';
		}

		$this->parseDir('views', $type, $app_path, $core_path);

		if (file_exists($view_source = $app_path.$file) # There is a matched view file in app
		OR	file_exists($view_source = $core_path.$file) # There is a matched view file in core
		OR	$view_source = $app_path.$file) {} # There is no view file anywhere

		$class = parse_class('View');
		$view = new $class($view_source, $controller);

		if ($get_output) {
			return $view->render(true);
		}
		else return $view;
	}

	/**
	 * Carrega um model.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.8
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	26/07/2010
	 *
	 * @uses basics::filename
	 * @uses Loader::set
	 *
	 * @param $model_source (string|array) - Parâmetro opcional para carregar outros models.
	 * @param $property (string) - Parâmetro opcional para inserir o model em uma propriedade do
	 * controller.
	 *
	 * @return O objeto Model do controller ou NULL caso não haja um model.
	 */
	public function model($model_source = null, $property = null) {
		$controller = isset($this->object)? $this->object : new stdClass;

		if (!isset($model_source)) {
			if (!isset($controller->model)) {
				$model_source =& $controller->modelSource;
			}
			else return $controller->model;
		}

		$xpath = null;

		if (!empty($model_source)) { # Model specified
			if (is_array($model_source)) { # A model path and/or xpath specified (array)
				if (!is_numeric(key($model_source))) {
					$type = key($model_source);
				}

				$file = current($model_source);

				if (next($model_source)) {
					$xpath = current($model_source);
				}
			}
			else { # Just a model file specified (single string)
				$file = $model_source = preg_replace('/\.xml$/', '', $model_source).'.xml';
			}

			$fileinfo = pathinfo($file);
			$dirname = preg_replace('/^[\.\/]([^\.])/', '$1', $fileinfo['dirname']);
			$dirname = $dirname? $dirname.DS : '';
			$filename = filename($file);
			$ext = isset($fileinfo['extension'])? '.'.$fileinfo['extension'] : '.xml';
			$file = $dirname.$filename.$ext;
		}
		else { # No model specified, get the default
			$filename = strtolower($controller->name);
			$file = $filename.'.xml';
		}

		if (isset($this->app->models[$file])) {
			$model = $this->app->models[$file];
		}
		else {
			if (!isset($type)) {
				$type = isset($controller->type)? $controller->type : 'generic';
			}

			$this->parseDir('models', $type, $app_path, $core_path);

			if (file_exists($path = $app_path.$file)
			OR	file_exists($path = $core_path.$file)) {
				$model = Model::create($path);
				$this->app->models[$file] = $model;
			}
		}

		if (isset($model) && $model) {
			if (isset($xpath)) {
				$model = $model->xpath($xpath);
				$model = count($model) > 1? $model : current($model);
			}

			if (isset($controller->modelSource) && $model_source == $controller->modelSource) {
				$controller->model = $model;
			}
			else {
				$this->set($property, $filename, $model);
			}

			return $model;
		}
		else {
			$this->app->models[$file] = false;
			return false;
		}
	}

	/**
	 * Analisa os diretórios dos controllers, models e views, e define os devidos parâmetros.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.8
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @param $type (string) - 'controllers', 'models' ou 'views'.
	 * @param &$singular (string) - A versão singular do diretório a ser analisado.
	 * @param &$app_path (string) - Variável que receberá o caminho correto do diretório na aplicação.
	 * @param &$core_path (string) - Variável que receberá o caminho correto do diretório no core.
	 * @param &$attr (string) - Variável que receberá o nome correto da propriedade estática da classe Loader.
	 */
	private function parseDir($type, &$singular, &$app_path, &$core_path, &$attr = null) {
		$type = strtolower($type);
		$path = constant(strtoupper($type));
		$app_path = $this->config['path'].$path;
		$core_path = CORE.$path;

		switch ($singular) {
			case 'generic':
			case 'controller':
				$singular = '';
				$app_path .= $this->config->dir[$type]['generic'];
				$core_path .= GENERIC;
				$attr = 'controllers';
				break;
			case 'action':
			case 'page':
			case 'pages':
				$singular = 'page';
				$app_path .= $this->config->dir[$type]['pages'];
				$core_path .= PAGES;
				$attr = 'pages';
				break;
			case 'error':
			case 'errors':
				$singular = 'error';
				$app_path .= $this->config->dir[$type]['errors'];
				$core_path .= ERRORS;
				$attr = 'errors';
				break;
			case 'data':
				$app_path .= $this->config->dir[$type]['data'];
				$core_path .= DATA;
				$attr = 'data';
				break;
			case 'module':
			case 'modules':
				$singular = 'module';
				$app_path .= $this->config->dir[$type]['modules'];
				$core_path .= MODULES;
				$attr = 'modules';
				break;
			default:
				$app_path .= isset($this->config->dir[$type][$singular])? $this->config->dir[$type][$singular] : $singular.DS;
				$attr = $singular;
		}
	}

	/**
	 * Armazena um determinado conteúdo no objeto corrente.
	 *
	 * @since 				Neleus 0.1.3
	 * @version 			0.7
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @param $new_name (string) - Novo nome da propriedade.
	 * @param $default_name (string|object) - Nome padrão da propriedade ou objeto que o contenha.
	 * @param $value (mixed) - Valor a ser inserido na propriedade.
	 *
	 * @return TRUE em caso de sucesso ou FALSE em caso de erro.
	 */
	private function set($new_name, $default_name, $value) {
		if ($new_name === false) {
			return false;
		}

		$object = isset($this->object)? $this->object : $this;

		if (is_object($default_name) && isset($default_name->name)) {
			$default_name = $default_name->name;
		}
		elseif (is_object($default_name) && !$new_name) {
			return false;
		}
		elseif (strpos($default_name, '.') !== false) {
			$names = explode('.', $default_name);

			if (isset($object->name) && $object->name == $names[0]) {
				$default_name = $names[1];
			}
			else {
				$default_name = $names[0];
				$old_value = $value;
				$value = new stdClass;
				$value->{$names[1]} = $old_value;
			}
		}

		$attr = $new_name && is_string($new_name)? $new_name : $default_name;

		if (!$attr) return false;

		if ($new_name OR !isset($object->{$attr})) {
			$object->{$attr} = $value;
			return true;
		}
		else return false;
	}

}
endif;
?>