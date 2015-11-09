<?php
if (!parse_class('PageController')):
/**
 * PageController Class
 *
 * Classe responsável pela definição das páginas da aplicação.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1
 * @version 			1.4.5
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 */

class PageController extends Controller {

	public $type = 'page';

	public $slug;
	public $parent;
	public $parents = array();
	public $children = array();

	public $method;
	public $index;
	public $action;
	public $lastAction;
	public $actions = array();

	public $maxParams;

	public $visible = true;

	protected $passedParent;

	private $pagination;
	private $tests = array();

	/**
	 * Configura as propriedades da página.
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.8
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Controller::__construct
	 * @uses PageController::_getParents
	 * @uses PageController::_getSlug
	 * @uses PageController::_getActions
	 */
	public function __construct() {
		parent::__construct();

		if (get_class($this) != __CLASS__) {
			$this->_getParents();
			$this->_getSlug();
			$this->_getActions();
		}
		else {
			$this->type = 'action';
			$this->index = $this;
		}
	}

	/**
	 * Configura múltiplas páginas mãe.
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			0.9
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	04/07/2010
	 *
	 * @uses Loader::page
	 */
	private function _getParents() {
		$parent_class = $this->load->page(get_parent_class($this), false);
		$parents = array();

		$this->passedParent = $this->parent;

		// Prevent to inherit the same parents of its parent
		if ($parent_class && isset($this->parent) && $this->parent == $parent_class->passedParent) {
			$this->parent = null;
		}

		if (!is_array($this->parent) || (count($this->parent) == 1 && is_string(key($this->parent)))) {
			$parents[] = $this->parent;
		}
		else {
			foreach ($this->parent as $parent) {
				$parents[] = $parent;
			}
		}

		foreach ($parents as $parent) {
			if (!is_object($parent) && !empty($parent)) {
				$parent = $this->load->page($parent);
			}
			elseif ($parent_class) {
				$parent = $parent_class;
			}
			else {
				$parent = null;
			}

			if ($parent) {
				$parent->children[$this->id] = $this;
				$this->parents[$parent->id] = $parent;
			}
		}

		if (count($this->parents) == 1) {
			$this->parent = current($this->parents);
		}
		else {
			$this->parent = null;
		}

		return $this->parents;
	}

	/**
	 * Configura o slug da página.
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	04/07/2010
	 *
	 * @uses Loader::page
	 */
	private function _getSlug() {
		$parent_class = $this->load->page(get_parent_class($this), false);

		if (!isset($this->slug) || ($parent_class && $parent_class->slug == $this->slug))
			$slug = strtolower($this->name);
		else
			$slug = strtolower($this->slug);

		if (strtolower($this->name) == strtolower($this->config['main_page'])) {
			$slug = '';
		}

		$this->slug = $slug;
	}

	/**
	 * Configura as actions da página.
	 *
	 * @since 				Neleus 0.1.4
	 * @version 			1.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses PageController::__construct
	 *
	 * @return Array com os objetos das actions.
	 */
	private function _getActions() {
		// Getting the public methods
		$reflection = new ReflectionClass($this);
		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

		// Parsing the public methods
		foreach ($methods as $method) {
			if (preg_match('/^_/', $method->name) || (method_exists(__CLASS__, $method->name) && strtolower($method->name) != 'index')) {
				continue;
			}

			if (!isset($this->actions[$method->name])) {
				$this->actions[] = $method->name;
			}
		}
		unset($methods, $method);

		// Back uping actions
		$actions = $this->actions;
		$this->actions = array();

		// Parsing actions
		foreach ($actions as $key => $options) {
			// Checks if action was passed as array('action1', 'action2'); or array('action1' => false);
			if (!is_array($options)) {
				if (is_numeric($key)) { # array('action1'); to array('action1' => '');
					$key = $options;
					$options = null;
				}

				settype($options, 'array');
			}

			// If it's really a method
			if (method_exists($this, $key)) {
				$is_index = strtolower($key) == 'index';
				$file = $is_index? '' : '.'.$key;

				// Checks if action was passed as 'action1' => false (converted into array(false) above)
				if (count($options) == 1 && isset($options[0])) {
					if (is_bool($options[0])) {
						$options['visible'] = $options[0];
					}
					elseif (is_string($options[0])) {
						$options['slug'] = $options[0];
					}

					unset($options[0]);
				}

				// Default options
				$options['name'] = $key;
				$options['parent'] = $this;
				$options['id'] = $this->id.'.'.$key;

				// Visible
				if (!isset($options['visible']))
					$options['visible'] = $this->visible;

				// AutoRender
				if (!isset($options['autoRender']))
					$options['autoRender'] = $this->autoRender;

				// Slug
				if (!isset($options['slug']))
					$options['slug'] = $is_index? '' : strtolower($key);
				else
					$options['slug'] = strtolower($options['slug']);

				// View
				if (isset($options['view']))
					$options['viewSource'] = $options['view'];
				elseif ($this->viewSource)
					$options['viewSource'] = dirname($this->viewSource).'/'.preg_replace('/\.php$/', '', basename($this->viewSource)).$file;
				else
					$options['viewSource'] = strtolower($this->name).$file;

				// Model
				if (isset($options['model']))
					$options['modelSource'] = $options['model'];
				elseif ($this->modelSource && is_string($this->modelSource))
					$options['modelSource'] = dirname($this->modelSource).'/'.preg_replace('/\.xml$/', '', basename($this->modelSource)).$file;
				elseif ($this->modelSource)
					$options['modelSource'] = $this->modelSource;
				else
					$options['modelSource'] = strtolower($this->name).$file;

				// Like controller constructor
				unset($options['view'], $options['model']);

				// Finally, create the action object
				$class = parse_class('PageController');
				$action = new $class;

				foreach ($options as $attr => $value) {
					$action->$attr = $value;
				}

				$action->method = $reflection->getMethod($action->name);
				$this->actions[$key] = $action;
			}
			else continue;
		}

		$this->index = $this->actions['index'];

		return $this->actions;
	}

	/**
	 * Action index.
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	04/06/2010
	 */
	public function index() {}

	/**
	 * Roda a página.
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			1.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	26/07/2010
	 *
	 * @uses Controller::parseParams
	 * @uses PageController::init
	 * @uses PageController::render
	 *
	 * @param $params (string|array) - Parâmetros a serem passados para a action.
	 * @param $get (boolean) - Determina o output do método, se ele vai mostrar ou simplesmente
	 * capturar a view.
	 */
	public function run($params = array(), $get = false) {
		$this->parseParams(func_get_args(), $params, $get);

		$action = $this->index;
		$action->running = true;
		$page = $action->parent;

		$page->lastAction = $page->action;
		$page->action = $action;

		if ($params) {
			$action->params = $params;
		}
		elseif ($action->params) {
			$params = $action->params;
		}

		ob_start();

		$page->init();
		call_user_func_array(array($page, $action->name), $action->params);

		$output = ob_get_contents();
		ob_end_clean();

		if (!$action->rendering) {
			if ((isset($action->autoRender) && $action->autoRender)
			OR (!isset($action->autoRender) && empty($output))) {
				$output = $action->render($params, $get);
			}
		}

		$action->running = false;
		$page->action = $page->lastAction;
		$page->lastAction = $action;

		if (isset($output)) {
			if ($get) {
				return $output;
			}
			else {
				if (!is_bool($output)) echo $output;
				return true;
			}
		}
		else return false;
	}

	/**
	 * Mostra a view do controller na tela.
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			1.6
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/07/2010
	 *
	 * @uses Controller::parseParams
	 * @uses PageController::flushViews
	 * @uses PageController::test
	 * @uses Loader::error
	 * @uses Controller::render
	 * @uses Controller::run
	 * @uses View::render
	 *
	 * @param $params (string|array) - Parâmetros a serem passados para a action.
	 * @param $get (boolean) - Determina o output do método, se ele vai mostrar ou simplesmente
	 * capturar a view.
	 *
	 * @return Caso o parâmetro $get seja true, retorna o contéudo gerado pela requisição da view.
	 */
	public function render($params = array(), $get = false) {
		$this->parseParams(func_get_args(), $params, $get, $view);

		$action = $this->type == 'page'? $this->action : $this;
		$action->rendering = true;
		$render = true;

		if ($params) {
			$action->params = $params;
		}
		elseif ($action->params) {
			$params = $action->params;
		}

		if ($view) {
			$this->load->view($view);
		}

		// Searches a previous loaded view in the page context
		// Because it'll be used as $this->load->view() and not $this->action->load->view()
		if ($action->parent->currentView) {
			$view = $action->parent->currentView;
			$action->parent->flushViews();
		}
		elseif ($action->view) {
			$view = $action->view;
		}
		else return false;

		if ($action->test($params, $output)) {
			if (!$action->running) {
				$action->run($params, $get);
			}

			$render = $view->render($get);
		}
		else {
			@header('HTTP/1.1 404 Not Found');

			if (empty($output) && ($notfound = $this->load->error($this->config['404_error'], false))) {
				$render = $notfound->run($get);
			}
			elseif (!empty($output)) {
				$render = $output;
			}
		}

		$action->rendering = false;

		if ($get) {
			return $render;
		}
		else {
			if (!is_bool($render)) echo $render;

			return true;
		}
	}

	/**
	 * Método para retornar a relação das páginas existentes em determinada action.
	 *
	 * @usage
	 * $this->load->action('Main.index')->paginate();
	 * $this->load->page('Main')->paginate();
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			0.8
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @param $key (string|integer) - Chave da array resultante que deve ser retornada.
	 *
	 * @return Array contendo todas as combinações de parâmetros aceitos na action, NULL
	 * caso o método de paginação não exista e o método não possua parâmetros e não sejam
	 * aceitos parâmetros adicionais ou FALSE caso o método de paginação não exista.
	 */
	public function paginate($key = null) {
		$action = $this->index;

		if (isset($action->paginate)) {
			$paginate = $action->paginate;
		}

		// Paginate method (e.g. _paginateIndex)
		$method = '_paginate'.$action->name;

		// Execute and return
		if (isset($paginate) || (method_exists($action->parent, $method)
		&& is_array($paginate = $action->parent->$method($key)))) {
			if (!isset($key)) {
				$action->paginate = $paginate;
			}
			elseif (is_string($key) || is_int($key)) {
				if (isset($paginate[$key])) {
					return $paginate[$key];
				}
				else return false;
			}

			return $paginate;
		}
		elseif ($action->method->getNumberOfParameters() == 0 && !isset($action->maxParams)) {
			return null;
		}
		else return false;
	}

	/**
	 * Realiza o teste em uma determinada action.
	 *
	 * @usage
	 * $this->load->action('index')->test('param1', 'param2');
	 * $this->load->action('create')->test(array('param1', 'param2'));
	 * $this->load->page('Main')->test('param1', $output);
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			0.9
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Controller::parseParams
	 *
	 * @param $params (array) - Parâmetros a serem passados para testar a action.
	 * @param $output (mixed) - Parâmetro passado por referência para receber a saída do método de
	 * teste.
	 *
	 * @return TRUE caso o teste seja positivo ou FALSE caso contrário.
	 */
	public function test($params = array(), &$output = null) {
		$action = $this->index;
		$page = $action->parent;

		if (!is_array($params)) {
			$args = func_get_args();

			if (is_null(end($args))) array_pop($args);

			$this->parseParams($args, $params);
		}

		// $p is the string full version of $params
		$p = implode('/', $params);

		// Verify caching tests
		if (isset($action->tests[$p])) {
			$output = $action->tests[$p]->output;

			return $action->tests[$p]->result;
		}
		else {
			$output = null;

			// Test parameter count
			$param_total = $action->method->getNumberOfParameters();
			$param_required_total = $action->method->getNumberOfRequiredParameters();
			$max_params = isset($action->maxParams)? $action->maxParams : $param_total;

			if (count($params) < $param_required_total || ($max_params >= 0 && count($params) > $max_params)) {
				$result = false;
			}
			else {
				// Parsing and executing test method
				$method = '_test'.$action->name;

				if (method_exists($action->parent, $method)) {
					ob_start();
					$result = call_user_func_array(array($action->parent, $method), $params);
					$output = ob_get_contents();
					ob_end_clean();
				}
				elseif (!$param_required_total && !$params) {
					$result = true;
				}
				elseif (is_array($paginate = $action->paginate())) {
					if (in_array($params, $paginate) || (count($params) == 1 && in_array(current($params), $paginate)))
						$result = true;
					else
						$result = false;
				}
				elseif (is_null($paginate)) {
					if (empty($params) || $max_params < 0)
						$result = true;
					else
						$result = false;
				}
				else {
					$result = true;
				}
			}
		}

		$action->tests[$p]->output = $output;
		$action->tests[$p]->result = $result;

		return $result;
	}

	/**
	 * Configura uma página mãe para a página temporariamente, caso a mesma possua mais de uma.
	 *
	 * @usage
	 * $this->load->controller('Foo')->setParent(3)->url(array('foo', 'bar'));
	 * $this->load->controller('Bar')->setParent('Foo')->url();
	 * $this->parents['main.index']->setParent('Foo');
	 * $this->setParent('Main.index')->url();
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			0.7
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @param $parent (string|integer) - O nome da página mãe ou seu índice.
	 *
	 * @return O objeto da página com a página mãe configurada.
	 */
	public function setParent($parent) {
		if (!$this->parents) {
			return $this;
		}

		if (is_string($parent)) {
			if (isset($this->parents[$parent])) {
				$this->parent = $this->parents[$parent];
			}
			else {
				$parent = strtolower($parent);

				foreach ($this->parents as $name => $page) {
					if ($parent = strtolower($name)) {
						$this->parent = $page;
						break;
					}
				}

				// If it's still without a parent, try to find an action parent using only its parent
				// In case, we assume setParent('Main') to get parents['main.index'] or parents['main.edit']
				// But only if it has just ONE action of this parent
				// There's no risc to get parents['main'], because 'foreach' block above should get it
				if (!$this->parent && count($match = preg_grep("/^$parent\./i", array_keys($this->parents))) == 1) {
					$this->parent = $this->parents[current($match)];
				}
			}
		}
		elseif (($class = parse_class('PageController')) && $parent instanceof $class) {
			if (isset($this->parents[$parent->id])) {
				$this->parent = $this->parents[$parent->id];
			}
		}
		elseif (is_int($parent)) {
			if ($parent < count($this->parents)) {
				reset($this->parents);

				for ($i = 0; $i <= $parent; $i++) {
					if ($i == $parent) {
						$this->parent = current($this->parents);
						break;
					}

					next($this->parents);
				}

				reset($this->parents);
			}
		}

		return $this;
	}

	/**
	 * Limpa a página mãe atual carregada por setParent().
	 *
	 * @since 				Neleus 0.2.1
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	07/06/2010
	 *
	 * @return O objeto da página com a página mãe limpa.
	 */
	public function flushParents() {
		if (isset($this->parent) && count($this->parents) > 1) {
			$this->parent = null;
		}

		return $this;
	}

	/**
	 * Retorna a url da página.
	 *
	 * @since 				Neleus 0.1.4
	 * @version 			0.8
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @param $absolute_or_params (boolean|array) - Parâmetros a serem passados pra action ou parâmetro absolute.
	 * @param $absolute (boolean) - Determina se a URL será retornada de forma absoluta ou não.
	 *
	 * @return String com a URL da página ou FALSE em caso de erro;
	 */
	public function url($absolute_or_params = true, $absolute = true, $page_uri = '', $count = 0) {
		$params = array();

		// Parsing args
		if (is_bool($absolute_or_params) && $absolute_or_params != $absolute) {
			$absolute = $absolute_or_params;
		}
		elseif (is_array($absolute_or_params)) {
			$params = $absolute_or_params;
		}
		elseif (is_string($absolute_or_params) || is_numeric($absolute_or_params)) {
			$params = $this->paginate($absolute_or_params);

			if (!is_array($params)) $params = array($params);
		}

		// If it has a single parent or none parents
		if ($this->parent || count($this->parents) <= 1) {
			// It has effect only if it's an action
			if (!$params && $this->params) {
				$params = $this->params;
			}

			$page_slug = $this->slug? $this->slug.'/' : '';

			// Isn't it the first child ($count > 0) and processed params got a red card in test?
			if ($count && $this->type == 'action' && !$this->test($params)) {
				if (is_array($paginate = $this->paginate())) {
					$page_uris = array();
					$count++;

					foreach ($paginate as $params) {
						$page_uris[] = $this->url($params, $absolute, $page_uri, 0);
					}

					$this->flushParents();

					return $page_uris;
				}
				elseif (is_null($paginate)) {
					$params = array();
				}
			}

			// At this point, $page_uri can be an array if some of its children got the condition block above
			if (is_array($page_uri)) {
				$page_uris = array();

				foreach ($page_uri as $uri) {
					$page_uris[] = $this->url($params, $absolute, $uri, $count);
				}

				$this->flushParents();

				return $page_uris;
			}

			$params = $params? implode('/', $params).'/' : '';
			$page_uri = $page_slug.$params.$page_uri;

			if ($this->parent) {
				$page_uri = $this->parent->url($absolute, $absolute, $page_uri, ++$count);
			}
			elseif ($absolute && is_string($page_uri)) {
				$page_uri = $this->config['url'].'/'.$page_uri;
			}

			if (is_string($page_uri)) {
				$page_uri = preg_replace('@/$@', '', $page_uri);
			}

			$this->flushParents();
		}
		else { # Has multiple parents
			$page_uris = array();

			foreach ($this->parents as $parent) {
				$this->setParent($parent->name);
				$page_uris[] = $this->url($absolute_or_params, $absolute, $page_uri);
			}

			return $page_uris;
		}

		return $page_uri;
	}

}
endif;
?>