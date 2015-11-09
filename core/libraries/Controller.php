<?php
if (!class_exists('Controller')):
/**
 * Controller Class
 *
 * Classe responsável pela definição dos controllers da aplicação.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1
 * @version 			0.7.4
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 */

class Controller extends Library {

	public $id;
	public $type = 'generic';

	public $view;
	public $model;

	public $viewSource;
	public $modelSource;
	public $currentView;
	public $currentViewSource;

	public $params = array();
	public $vars = array();

	protected $autoRender;
	protected $rendering;
	protected $running;

	/**
	 * Inicia o controller, configurando suas propriedades.
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.6
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Library::__construct
	 */
	public function __construct() {
		parent::__construct();

		$this->id = $this->name;
		$this->viewSource = $this->view;
		$this->modelSource = $this->model;

		unset($this->view, $this->model);
	}

	/**
	 * Faz o autoload de propriedades não declaradas, caso exista um método para tanto.
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	15/09/2010
	 *
	 * @param $property (string) - Propriedade requisitada.
	 *
	 * @return O retorno do método de load ou NULL caso o mesmo não exista.
	 */
	public function __get($property) {
		if (method_exists($this->load, $property)) {
			return call_user_func(array($this->load, $property));
		}
		else {
			return null;
		}
	}

	/**
	 * Inicia o controller.
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	06/06/2010
	 */
	 public function init() {}

	/**
	 * Roda o controller.
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			1.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	15/09/2010
	 *
	 * @uses Controller::parseParams
	 * @uses Controller::init
	 * @uses Controller::render
	 *
	 * @param $params (string|array) - Parâmetros a serem passados para o controller.
	 * @param $get (boolean) - Determina o output do método, se ele vai mostrar ou simplesmente
	 * capturar a view.
	 */
	public function run($params = array(), $get = false) {
		$this->parseParams(func_get_args(), $params, $get);

		if ($params) {
			$this->params = $params;
		}
		elseif ($this->params) {
			$params = $this->params;
		}

		$this->running = true;

		ob_start();
		$this->init();
		$output = ob_get_contents();
		ob_end_clean();

		if (!$this->rendering) {
			if ((isset($this->autoRender) && $this->autoRender)
			OR (!isset($this->autoRender) && empty($output))) {
				$output = $this->render(true);
			}
		}

		$this->running = false;

		if (!empty($output) && $get) {
			return $output;
		}
		elseif (!empty($output)) {
			echo $output;
		}

		return true;
	}

	/**
	 * Mostra a view do controller na tela.
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.8
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	26/07/2010
	 *
	 * @uses Controller::parseParams
	 * @uses Loader::view
	 * @uses Controller::flushViews
	 * @uses Controller::run
	 * @uses View::render
	 *
	 * @param $params (string|array) - Parâmetros a serem passados para o controller.
	 * @param $get (boolean) - Determina o output do método, se ele vai mostrar ou simplesmente
	 * capturar a view.
	 *
	 * @return Caso o parâmetro $get seja true, retorna o contéudo gerado pela requisição da view.
	 */
	public function render($params = array(), $get = false) {
		$this->parseParams(func_get_args(), $params, $get, $view);
		$this->rendering = true;

		if ($params) {
			$this->params = $params;
		}
		elseif ($this->params) {
			$params = $this->params;
		}

		if ($view) {
			$this->load->view($view);
		}

		if ($this->currentView) {
			$view = $this->currentView;
			$this->flushViews();
		}
		elseif ($this->view) {
			$view = $this->view;
		}
		else return false;

		if (!$this->running) {
			$this->run();
		}

		$view = $view->render($get);
		$this->rendering = false;

		return $view;
	}

	/**
	 * Limpa a última view carregada.
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	31/05/2010
	 */
	public function flushViews() {
		if ($this->currentView) {
			$this->currentView = null;
		}

		return true;
	}

	/**
	 * Envia variáveis para a view.
	 *
	 * @usage
	 * $this->set('title', 'Main Page');
	 * $params['title'] = 'Main Page';
	 * $params['intro'] = 'Welcome to my page';
	 * $this->set($params);
	 *
	 * // Inside the view
	 * echo $title; // Output: 'Main Page'
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	15/09/2010
	 *
	 * @uses basics::parse_class
	 *
	 * @param $var (string|array) - Nome da variável a ser enviada.
	 * @param $value (mixed) - Valor da variável.
	 *
	 * @return TRUE caso a variável seja enviada com sucesso ou FALSE caso contrário.
	 */
	protected function set($var, $value = null) {
		if (is_null($value)) {
			if (is_array($var) || is_object($var)) {
				foreach ($var as $attr => $val) {
					$model_class = parse_class('Model');

					if (is_numeric($attr) && $val instanceof $model_class) {
						$attr = $val->getName();
					}

					$this->set((string)$attr, $val);
				}
			}
			else return false;
		}

		if (!is_string($var)) return false;

		$this->vars[$var] = $value;

		return true;
	}

	/**
	 * Analisa os parâmetros, que podem ser passados de diversas formas, como
	 * method('param1', 'param2', 'param3', true) e method(array('param1', 'param2'), true).
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.5
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	18/07/2010
	 *
	 * @param $args (array) - Lista de argumentos passados para o método.
	 * @param &$params (array) - Parâmetros a serem passados.
	 * @param &$get (boolean) - Determina o output do método.
	 * @param &$view (string) - Recebe a string que determina a view a ser renderizada.
	 */
	protected function parseParams($args, &$params, &$get = false, &$view = null) {
		$params = array();

		if (!is_array($args) || !isset($args[0])) {
			return false;
		}

		// arguments can be passed like:
		// method('view'); method('view', true); method('view', array('param')); method('view', array('param'), true);
		// method(array('param')); method(true); method(array('param'), true)

		if (is_string($args[0])) {
			$view = array_shift($args);
		}

		if (isset($args[0]) && is_array($args[0])) {
			$params = array_shift($args);
		}

		if (isset($args[0]) && is_bool($args[0])) {
			$get = array_shift($args);
		}

		return true;
	}

}
endif;
?>