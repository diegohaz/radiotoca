<?php
/**
 * Página principal
 */
class Begin extends PageController {

	public $page;
	public $shoutcast;

	/**
	 * Inicia a página principal
	 * Este método também é herdado pelas outras páginas, portanto todas as páginas executam estes processos
	 */
	public function init() {
		unset($_GET['page']);

		// Carrega algumas coisas
		$this->load->library('Inflector', 'inflector');
		$this->load->library('Router', 'route');
		$this->load->library('bmShoutCastInfo');
		$this->load->model('general');
		$this->load->modules(array('iSchedule', 'Player'));
		$this->load->data('ApiRequest');

		// Realiza a requisição na API do Twitter
		$this->ApiRequest->request();

		// Faz a conexão com a rádio
		try {
			$this->shoutcast = new bmShoutCastInfo(
				$this->config->shoutcast['host'],
				$this->config->shoutcast['port'],
				$this->config->shoutcast['password']
			);

			$this->config->shoutcast['status'] = $this->shoutcast->getStreamStatus();
		}
		catch (bmShoutCastException $e) {
			trigger_error($e->getMessage());
		}

		// Define o título
		$this->set('title', $this->general->info['title']->toString());
		$this->set('browser_title', $this->vars['title']);
		$this->general = $this->general->vars(array(
			'title' => $this->vars['title'],
			'twitter_tag' => $this->general->info['twitter_tag']
		));

		// Define as labels
		$labels = new stdClass;
		$model_labels = $this->general->labels->attributes();

		foreach ($model_labels as $attr => $value) {
			$labels->$attr = $value->toString();
		}

		$this->set('labels', $labels);

		// Define as páginas
		$this->page = $this->general->pages->xpath(sprintf('page[@id="%s"]', $this->id), true);
		$this->action->page = $this->page->xpath(sprintf('page[@id="%s"]', $this->action->name), true);
		$this->set('page_title', $this->page['title']->toString());
		$pages = array();

		foreach ($this->general->xpath('pages/*') as $page) {
			if (!$controller = $this->load->page($page['id']->toString())) {
				trigger_error("Page $page[id] was not found");
				continue;
			}

			$_page = new stdClass;
			$_page->title = $page['title']->toString();
			$_page->url = $controller->url();
			$pages[$controller->id] = $_page;
		}

		$this->set('pages', $pages);

		// Define as actions
		$actions = array();

		foreach ($this->general->xpath('actions/*') as $action) {
			if (!isset($pages[$action['id']->toString()])) {
				trigger_error("Page $action[id] was not found");
				continue;
			}

			$page = $pages[$action['id']->toString()];

			$_action = new stdClass;
			$_action->id = $this->inflector->slug($action['id']);
			$_action->url = $page->url;
			$_action->title = $action['title'];
			$_action->current = $this->route->controller->url() == $page->url? 'current' : null;
			$_action->currentTitle = $_action->current? $action['current_title'] : $action['title'];
			$_action->desc = $action['desc'];

			$actions[] = $_action;
		}

		$this->set('actions', $actions);

		// Define as metatags
		if (isset($this->page['meta_description'])) {
			$this->set('meta_description', $this->page['meta_description']->toString());
		}
		if (isset($this->page['meta_keywords'])) {
			$this->set('meta_keywords', $this->page['meta_keywords']->toString());
		}

		// Define o módulo iSchedule
		$n = $this->name == __CLASS__? 3 : 1;
		$this->set('ischedule', $this->iSchedule->render($n));

		// Define o copyright
		$copyright = new stdClass;
		$copyright->desc = $this->general->copyright->desc->vars(array('year' => date('Y')));
		$this->set('copyright', $copyright);

		// Define os créditos
		$credits = new stdClass;
		$credits->website = $this->general->credits->website;
		$credits->desc = $this->general->credits->desc;
		$this->set('credits', $credits);

		// Define o patrocínio
		$sponsorship = new stdClass;
		$sponsorship->website = $this->general->sponsorship->website;
		$sponsorship->name = $this->general->sponsorship->name;
		$sponsorship->desc = $this->general->sponsorship->desc;
		$this->set('sponsorship', $sponsorship);

		// Define o player
		$player = $this->Player->render();
		$this->set('player', $player);
	}

	/**
	 * Index da página principal
	 * As outras páginas só executarão estes processos se não implementarem sua própria index()
	 */
	public function index() {
		// Carrega algumas coisas
		$this->load->library('Twitter', 'twitter');
		$this->load->modules(array('Top', 'iTwitter'));
		$this->load->page('Schedule');

		// Define o título do browser
		if ($this->config->shoutcast['status']) {
			$song = htmlspecialchars($this->shoutcast->getSongTitle());
			$this->set('browser_title', $this->page->vars(array('song' => $song))->attributes()->browser_title);
		}

		// Define o iTwitter
		$this->set('itwitter', $this->iTwitter->render());

		// Define os tops
		$tops = new stdClass;
		$tops->listeners = $this->Top->listeners('sub-section two three');
		$tops->songs = $this->Top->songs('sub-section');
		$this->set('tops', $tops);

		$this->render('layout');
	}

}
?>