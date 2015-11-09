<?php
/**
 * Controlador de paginações
 */
class Pagination extends ModuleController {

	public $view = 'pagination';
	public $model = array('generic' => 'general', 'pagination');

	public $page;
	public $rowsPerPage;
	public $numbersPerPage;

	/**
	 * Assmila algumas propriedades
	 */
	public function __construct() {
		parent::__construct();

		$this->page = isset($_GET['p'])? floor(abs($_GET['p'])) : 1;
		$this->rowsPerPage = $this->config->pagination['rows_per_page'];
		$this->numbersPerPage = $this->config->pagination['numbers_per_page'];
	}

	/**
	 * Carrega a barra de paginação
	 */
	public function render($num_rows, $page = null, $rows_per_page = null, $numbers_per_page = null) {
		// Atribui os valores padrão
		if (!isset($page)) $page = $this->page;
		if (!isset($rows_per_page)) $rows_per_page = $this->rowsPerPage;
		if (!isset($numbers_per_page)) $numbers_per_page = $this->numbersPerPage;

		// Realiza as contas
		$total_pages = ceil($num_rows/$rows_per_page);
		$numbers_before = round(($numbers_per_page - 1)/2);

		if ($page <= $numbers_before) {
			while ($page <= $numbers_before) --$numbers_before;
		}

		$numbers_after = $numbers_per_page - $numbers_before - 1;

		if ($page + $numbers_after > $total_pages) {
			while ($page + $numbers_after > $total_pages) {
				--$numbers_after;

				if ($page > $numbers_before + 1) {
					++$numbers_before;
				}
			}
		}

		$first_number = $page - $numbers_before;
		$last_number = $page + $numbers_after;

		// Se a página atual for maior que o número total de páginas, retornar falso
		if ($page > $total_pages) {
			return false;
		}
		// Se a quantidade de linhas totais for menor ou igual a quantidade de linhas que devem ser mostradas
		// por página, não é necessário mostrar a paginação, então retornar nulo
		elseif ($num_rows <= $rows_per_page) {
			return null;
		}

		// Carrega algumas coisas
		$this->load->library('Router', 'route');

		$controller = $this->route->controller;

		// Carrega as informações gerais e as informações do módulo na página, se existirem
		$page_info = $controller->model? $controller->model->xpath('pagination', true) : false;
		$info = new stdClass;

		// Atribui as informações
		foreach ($this->model->children() as $attr => $value) $info->$attr = $value;

		if ($page_info) {
			foreach ($page_info->children() as $attr => $value) $info->$attr = $value;
		}

		$url = $controller->action->url($controller->action->params).'?';
		$pages = array();

		if (isset($_GET)) {
			unset($_GET['p']);

			foreach ($_GET as $attr => $value) {
				$url .= "$attr=".urlencode($value)."&";
			}
		}

		// Assimila os números das páginas a serem exibidos
		for ($i = $first_number; $i <= $last_number; $i++) {
			$number = new stdClass;
			$number->url = $url."p=$i";
			$number->active = $i == $page? 'active' : null;
			$number->count = $i;

			$pages[] = $number;
		}

		// Assimila os outros botões (first, previous, next e last) no formato array(condição, url)
		$buttons = array(
			'first' => array($page > 2, rtrim($url, '?')),
			'previous' => array($page > 1, $url.'p='.($page - 1)),
			'next' => array($page < $total_pages, $url.'p='.($page + 1)),
			'last' => array($page + 1 < $total_pages, $url.'p='.$total_pages)
		);

		$vars = array('previous' => $page - 1, 'next' => $page + 1, 'last' => $total_pages);

		foreach ($buttons as $name => $button) {
			if (isset($vars[$name])) {
				$info->$name = $info->$name->vars(array($name => $vars[$name]));
			}

			$_button = new stdClass;
			$_button->href = $button[0]? "href=\"$button[1]\"" : null;
			$_button->tip = $button[0]? $info->$name : null;
			$_button->title = $button[0]? "title=\"$_button->tip\"" : null;
			$_button->disabled = !$button[0]? 'disabled' : null;

			$this->set($name, $_button);
		}

		$this->set('pages', $pages);

		return parent::render(true);
	}

}

?>