<?php
include_once 'Begin.php';

/**
 * Página listeners
 */
class Listeners extends Begin {

	public $slug = 'ouvintes';
	public $actions = array(
		'listener' => ''
	);

	public $p = 1;

	/**
	 * Inicia a página
	 */
	public function init() {
		parent::init();

		$this->p = isset($_GET['p'])? floor(abs($_GET['p'])) : $this->p;
		$this->vars['sidebar_modules'][] = $this->load->module('iTwitter')->render(true, 3);
	}

	/**
	 * Action index
	 */
	public function index() {
		$this->load->module('Pagination');
		$this->load->data('Meta', 'meta');
		$this->load->data('Listener');

		$listeners = array();
		$this->Listener->find(null, 'tweets_count DESC', $this->Pagination->rowsPerPage * ($this->p - 1).','.$this->Pagination->rowsPerPage);

		while ($this->Listener->fetch()) {
			$listeners[] = $this->Listener->get();
		}

		$this->Meta->find(array('attribute' => 'listeners_count'));

		if ($this->Meta['value'] > $this->Pagination->rowsPerPage) {
			$this->set('pagination', $this->Pagination->render($this->Meta['value']));
		}

		// Define o título do browser
		$this->set('browser_title', $this->page->vars(array('user' => '@'.$listeners[array_rand($listeners)]->screenName))->attributes()->browser_title);

		$this->set('listeners', $listeners);
		$this->set('page', $this->render(true));

		$this->render('layout');
	}

	/**
	 * Action listener, exibe o perfil de determinado ouvinte
	 */
	public function listener($listener) {
		$this->load->library('Inflector', 'inflect');
		$this->load->module('Pagination');
		$this->load->data(array('Tweet', 'Listener'));

		$this->Listener->find(array('screen_name' => $listener), 1);

		$listener = $this->Listener->get();
		$listener->profile = array();
		$special_labels = array('last_tweets');

		foreach ($this->model->attributes() as $attr => $label) {
			if (!isset($listener->$attr) && !in_array($attr, $special_labels)) continue;

			$info = new stdClass;
			$info->id = $this->inflect->camelize($attr);
			$info->label = $label->toString();

			if ($attr == 'last_tweets') {
				$tweets = array();
				$this->Tweet->find(array('LISTENER_ID' => $this->Listener['LISTENER_ID']), 'created_at DESC', 3);

				while ($this->Tweet->fetch()) {
					$tweets[] = $this->Tweet->get();
				}

				$view = $this->load->view('tweets');
				$view->vars['tweets'] = $tweets;
				$info->value = $view->render(true);
			}
			elseif (isset($this->model->listener->$attr)) {
				$info->value = $this->model->listener->$attr->vars(array($attr => $listener->$attr))->toString();
			}
			else {
				$info->value = $listener->$attr;
			}

			if (!empty($info->value)) {
				$listener->profile[] = $info;
			}
		}

		// Define o título do browser e as metatags
		$this->set('browser_title', $this->action->page->vars(array('user' => htmlspecialchars($this->Listener['name'], ENT_COMPAT, $this->config['encoding'])))->attributes()->browser_title);
		$this->set('meta_description', $this->action->page->vars(array('user_desc' => htmlspecialchars($this->Listener['description'], ENT_COMPAT, $this->config['encoding'])))->attributes()->meta_description);

		$this->set('page_subtitle', $this->action->page['title']->toString());
		$this->set('listener', $listener);
		$this->set('page', $this->render('listeners', true));

		$this->render('layout');
	}

	/**
	 * Realiza o teste da action listener
	 */
	public function _testListener($listener) {
		if (!preg_match('/^[-_a-z0-9]+$/i', $listener)) return false;

		$this->load->data('Listener');

		if ($this->Listener->find(array('screen_name' => $listener), 1)) {
			return true;
		}
		else return false;
	}

	/**
	 * Realiza a paginação da action listener
	 */
	public function _paginateListener($id = null) {
		$this->load->data('Listener');
		$listeners = false;

		// Backup
		$active_record = $this->Listener->activeRecord;
		$active_result = $this->Listener->activeResult;
		$current_record = $this->Listener->currentRecord;

		$this->Listener->activeResult = null;

		if (isset($id)) {
			if ($this->Listener->select('screen_name', $id)) {
				$listeners[$id] = $this->Listener['screen_name'];
			}
		}
		else {
			$this->Listener->select(array('LISTENER_ID', 'screen_name'));
			$listeners = array();

			while ($this->Listener->fetch()) {
				$listeners[$this->Listener['LISTENER_ID']] = $this->Listener['screen_name'];
			}
		}

		// Repôe o backup
		$this->Listener->activeRecord = $active_record;
		$this->Listener->activeResult = $active_result;
		$this->Listener->currentRecord = $current_record;

		return $listeners;
	}

}
?>