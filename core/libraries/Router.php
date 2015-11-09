<?php
if (!parse_class('Router')):
/**
 * Router Class
 *
 * Classe responsável por requisitar as páginas.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.2
 * @version 			0.5.7
 * @author 				Diego Haz <http://diegohaz.com>
 * @modifiedby 		Diego Haz
 * @lastmodified	23/10/2010
 */

class Router extends Library {

	private $requests = array();
	public $pages = array();
	public $slugs = array();

	public $controller;
	public $action;

	/**
	 * Realiza a requisição de uma página através da URI.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.5
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Router::page
	 * @uses PageController::run
	 * @uses Loader::error
	 * @uses Controller::run
	 *
	 * @param $uri (string|array) - URI requisitada, uma string separada por '/' ou uma array de argumentos.
	 * @param $get (boolean) - Determina se o output será retornado (true) ou exibido (false).
	 */
	public function request($uri = null, $get = false) {
		if (!$this->pages) {
			$this->pages = $this->load->pages();

			foreach ($this->pages as $name => $page) {
				$this->slugs[$name] = $page->slug;
			}
		}

		if (is_bool($uri)) {
			$get = $uri;
			$uri = null;
		}

		if (!isset($uri)) {
			$uri = isset($_GET['page'])? $_GET['page'] : '';
		}
		elseif (is_array($uri)) {
			$uri = implode('/', $uri);
		}

		$page = $this->page($uri, $output);

		// Page exists and is visible?
		if ($page && $page->visible) {
			$this->controller = $page->parent;
			$this->action = $page;

			$output = $page->run(true);
		}

		// If not, request the Not Found Error
		else {
			@header('HTTP/1.1 404 Not Found');

			if (empty($output)) {
				if ($notfound = $this->load->error($this->config['404_error']))
					$output = $notfound->run(true);
			}
		}

		if ($get) {
			return $output;
		}
		else echo $output;
	}

	/**
	 * Captura uma página através da URI.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.6
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Router::parseURI
	 * @uses Router::controller
	 * @uses Router::action
	 * @uses Router::parseOutput
	 *
	 * @param $uri (string|array) - URI requisitada, uma string separada por '/' ou uma array de argumentos.
	 * @param &$output (string) - Variável que armazenará a saída dos testes.
	 * @param $strict (boolean) - Determina se a verificação da página será realizada no contexto das actions.
	 * @param $cache (boolean) - Determina se o método irá ou não utilizar as requisições armazenadas em cache.
	 *
	 * @return O objeto PageController da página requisitada ou FALSE caso a mesma não seja encontrada.
	 */
	public function page($uri, &$output = null, $strict = true, $cache = true) {
		$outputs = array();
		$uri = $this->parseURI($uri, 'string');

		if ($cache) {
			if (isset($this->requests[$uri][$strict]))	{
				return $this->requests[$uri][$strict];
			}
			else {
				$this->requests[$uri][$strict] = false;
			}
		}

		if (($page = $this->controller($uri, $outputs[]))
		OR	($page = $this->action($uri, $outputs[]))) {

			if (!$strict || $page->type == 'action'
			OR ($this->requests[$uri][false] = $page
			&& ($page = $this->action($uri, $outputs[])))) {

				$this->requests[$uri][$strict] = $page;
				return $page;
			}
		}

		$this->parseOutput($outputs, $output);

		return false;
	}

	/**
	 * Captura uma página estática através da URI.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.5
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Router::parseURI
	 * @uses Router::page
	 * @uses Router::action
	 * @uses PageController::setParent
	 * @uses Router::parseOutput
	 *
	 * @param $uri (string|array) - URI requisitada, uma string separada por '/' ou uma array de argumentos.
	 * @param &$output (string) - Variável que armazenará a saída dos testes.
	 *
	 * @return O objeto PageController da página requisitada ou FALSE caso a mesma não seja encontrada.
	 */
	public function controller($uri, &$output = null) {
		$outputs = array();
		$page_uri = $this->parseURI($uri, 'array');
		$uri = $this->parseURI($uri, 'string');

		if ($page_uri !== false) {
			$parent_uri = $page_uri;
			$current_uri = array_pop($parent_uri); # Remove the last slug from parent uri

			// Is the last slug a holder page?
			if (in_array($current_uri, $this->slugs)) {
				// May exists more than one page with the same slug
				$page_names = array_keys($this->slugs, $current_uri);

				foreach ($page_names as $name) {
					$current_page = $this->pages[$name];

					if ($current_page->parents) {
						if (!isset($parent_page)) {
							$parent_page = $this->page($parent_uri, $outputs[], false);
						}

						// As pages can have more than one parent, we need to loop into it to find the correct parent
						foreach ($current_page->parents as $parent) {
							if ($parent_page && $parent->type == 'action' && $parent_page->type != 'action') {
								$pseudo_parent = $this->action($parent_uri, $outputs[]);
							}

							if ($parent_page && $parent->id == $parent_page->id
							OR (isset($pseudo_parent) && $pseudo_parent && $parent->id == $pseudo_parent->id)) {
								$page_name = $name;
								break 2;
							}

							unset($pseudo_parent);
						}
					}
					elseif (!$parent_uri) {
						$page_name = $name;
						break;
					}
				}

				// Finally, return the page
				if (isset($page_name)) {
					$page = $this->pages[$page_name];

					if (isset($parent_page) && $parent_page) {
						$page->setParent($parent_page->id);
					}

					return $page;
				}
			}
		}

		$this->parseOutput($outputs, $output);

		return false;
	}

	/**
	 * Captura uma action através da URI.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.6
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Router::parseURI
	 * @uses Router::controller
	 * @uses PageController::test
	 * @uses Router::parseOutput
	 *
	 * @param $uri (string|array) - URI requisitada, uma string separada por '/' ou uma array de argumentos.
	 * @param &$output (string) - Variável que armazenará a saída dos testes.
	 *
	 * @return O objeto PageController da action requisitada ou FALSE caso a mesma não seja encontrada.
	 */
	public function action($uri, &$output = null) {
		$outputs = array();
		$page_uri = $this->parseURI($uri, 'array');
		$uri = $this->parseURI($uri, 'string');

		if ($page_uri !== false) {
			$reverse_uri = array_reverse($page_uri);
			$parent_uri = $page_uri;
			$params = array();

			do {
				if (in_array($current_uri = current($reverse_uri), $this->slugs)
				&& ($parent = $this->controller($parent_uri, $outputs[]))) {
					break;
				}

				array_unshift($params, array_pop($parent_uri));
				next($reverse_uri);
			}
			while ($current_uri !== false);

			// Probabelly $app['main_page'], in the config, doesn't exist
			if (!isset($parent)) {
				return false;
			}

			$actions = $parent->actions;

			// Put index actions in the end of list, to be parsed after slug'd actions
			foreach ($actions as $i => $action) {
				if (empty($action->slug)) {
					unset($actions[$i]);
					$actions[] = $action;
				}
			}

			$has_slug = false;

			foreach ($actions as $action) {
				if ((isset($params[0]) && ($has_slug = strtolower($action->slug) == $params[0]))
				OR	empty($action->slug)) {
					$action_params = $params;

					if ($has_slug) {
						// Remove the first param, because it's the action slug
						$action_slug = array_shift($action_params);
					}

					if ($action->test($action_params, $outputs[])) {
						// Yes, we got it
						$action->params = $action_params;
						$parent->action = $action;

						return $action;
					}
				}
			}
		}

		$this->parseOutput($outputs, $output);

		return false;
	}

	/**
	 * Analisa o conjunto de strings de saída dos testes e retorna a última saída preenchida.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	18/07/2010
	 *
	 * @param $outputs (string|array) - Saídas a serem analisadas.
	 * @param &$output (string) - Variável que armazenará o saída correta.
	 */
	private function parseOutput($outputs, &$output) {
		if (is_array($outputs)) {
			$outputs = array_reverse($outputs);

			foreach ($outputs as $content) {
				if (!empty($content)) {
					$output = $content;

					return true;
				}
			}
		}
		elseif (is_string($outputs)) {
			$output = $outputs;

			return true;
		}

		return false;
	}

	/**
	 * Analisa e retorna a URI formatada corretamente.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	10/07/2010
	 *
	 * @param $uri (string|array) - URI a ser analisada.
	 * @param $output (string) - Formato de saída do método: string ou array.
	 *
	 * @return A URI formatada no $output escolhido ou FALSE em caso de erro.
	 */
	private function parseURI($uri, $output = 'array') {
		if (is_array($uri)) {
			if ($output != 'array') {
				$uri = '/'.implode('/', $uri).'/';
			}
			else return $uri;
		}

		if (is_string($uri)) {
			$uri = strtolower($uri);
			$uri = preg_replace('@(^/|/$)@', '', $uri);

			if ($output != 'string') {
				$uri = explode('/', $uri);

				return $uri;
			}
			else return $uri;
		}
		else return false;
	}

}
endif;
?>