<?php
include_once 'Begin.php';

/**
 * Página tweets
 */
class Tweets extends Begin {

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
	}

	/**
	 * Action index
	 */
	public function index() {
		$this->load->modules(array('iTwitter', 'Pagination'));
		$this->load->data('Meta', 'meta');
		$this->load->data('Tweet');

		// Define o título do browser
		$this->set('browser_title', $this->page['browser_title']->toString());

		$tweets = array();
		$this->Tweet->find(null, 'created_at DESC', $this->Pagination->rowsPerPage * ($this->p - 1).','.$this->Pagination->rowsPerPage);

		while ($this->Tweet->fetch()) {
			$tweets[] = $this->Tweet->get();
		}

		$this->Meta->find(array('attribute' => 'tweets_count'));

		if ($this->Meta['value'] > $this->Pagination->rowsPerPage) {
			$this->set('pagination', $this->Pagination->render($this->Meta['value']));
		}

		$this->set('form', $this->iTwitter->form());
		$this->set('tweets', $tweets);
		$this->set('page', $this->render('tweets', true));

		$this->render('layout');
	}

	/**
	 * Action listener, exibe os tweets de determinado ouvinte
	 */
	public function listener($listener) {
		$this->load->module('Pagination');
		$this->load->data(array('Listener', 'Tweet'));

		$tweets = array();
		$this->Listener->find(array('screen_name' => $listener));
		$listener = $this->Listener->get();
		
		$this->Tweet->find(
			array('LISTENER_ID' => $this->Listener['LISTENER_ID']), 'created_at DESC',
			$this->Pagination->rowsPerPage * ($this->p - 1).','.$this->Pagination->rowsPerPage
		);

		while ($this->Tweet->fetch()) {
			$tweets[] = $this->Tweet->get();
		}

		if ($listener->tweetsCount > $this->Pagination->rowsPerPage) {
			$this->set('pagination', $this->Pagination->render($listener->tweetsCount));
		}

		// Define o título do browser e as metatags
		$this->set('browser_title', $this->action->page->vars(array('user' => $listener->name))->attributes()->browser_title);
		$this->set('meta_description', strip_tags($tweets[array_rand($tweets)]->text));

		$this->set('page_subtitle', $this->action->page['title']->toString());
		$this->set('listener', $listener);
		$this->set('tweets', $tweets);
		$this->set('page', $this->render('tweets', true));

		$this->vars['sidebar_modules'][] = $this->load->module('iTwitter')->render(true, 0);

		$this->render('layout');
	}

	/**
	 * Realiza o teste da action listener
	 */
	public function _testListener($listener) {
		return $this->load->page('Listeners')->_testListener($listener);
	}

	/**
	 * Realiza a paginação da action listener
	 */
	public function _paginateListener($id = null) {
		return $this->load->page('Listeners')->_paginateListener($id);
	}

}
?>