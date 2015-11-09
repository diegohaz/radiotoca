<?php
if (!parse_class('View')):
/**
 * View Class
 *
 * Classe responsável pelas views da aplicação.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1
 * @version 			0.3.4
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

class View extends Library {

	public $source;
	public $type;
	public $encoding;
	public $vars = array();

	private $controller;
	private $content;

	/**
	 * Cria uma nova view.
	 *
	 * @since 				Neleus 0.2
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @uses Library::__construct
	 *
	 * @param $source (string) - O caminho do arquivo da view.
	 * @param $controller (object) - O controller pelo qual a view foi requisitada.
	 */
	public function __construct($source, $controller) {
		parent::__construct();

		$this->controller = $controller;
		$this->source = $source;
		$this->type = 'text/html';
		$this->encoding = $this->config['encoding'];
	}

	/**
	 * Exibe o conteúdo da view.
	 *
	 * @since 				Neleus 0.2
	 * @version 			1.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @uses View::setHeader
	 * @uses Controller::flushViews
	 * @uses Loader::error
	 * @uses Controller::run
	 *
	 * @param $get (boolean) - Determina se a view será exibida ou retornada.
	 *
	 * @return String com o conteúdo da view caso $get seja TRUE.
	 */
	public function render($get = false) {
		if (isset($this->content)) {
			$this->setHeader();

			if ($get) {
				return $this->content;
			}
			else {
				echo $this->content;
			}

			return true;
		}
		elseif (!$this->config['display_errors'] || file_exists($this->source)) {
			if (method_exists($this->controller, 'flushViews')) {
				$this->controller->flushViews();
			}

			if (isset($this->controller->index->parent->vars)) {
				$this->vars = array_merge($this->controller->index->parent->vars, $this->vars);
			}
			elseif (isset($this->controller->vars)) {
				$this->vars = array_merge($this->controller->vars, $this->vars);
			}

			// Set vars
			foreach ($this->vars as $nls_var => $nls_value) {
				${$nls_var} = $nls_value;
			}
			unset($nls_var, $nls_value);

			// Finally, include the view
			$this->setHeader();

			ob_start();
			include($this->source);
			$this->content = ob_get_contents();
			ob_end_clean();

			if ($get) {
				return $this->content;
			}
			else {
				echo $this->content;
			}

			return true;
		}
		elseif (($this->config['display_errors'])
				&& (!isset($this->controller->name) || $this->controller->name != 'NoView')
				&& ($noview = $this->load->error('NoView'))) {
			return $noview->run(array('controller' => $this->controller), $get);
		}
		else return false;
	}

	/**
	 * Define o header da view.
	 *
	 * @since 				Neleus 0.2.7
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 */
	private function setHeader() {
		@header("Content-Type: $this->type; charset=$this->encoding");
	}

}
endif;
?>