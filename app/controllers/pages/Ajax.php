<?php
/**
 * Página ajax
 */
class Ajax extends PageController {

	public $actions = array(
		'index' => array(
			'maxParams' => -1
		)
	);

	/**
	 * Action index
	 */
	public function index() {
		$this->load->library('Router', 'route');

		$this->config['ajax'] = true;

		$page = $this->route->request(func_get_args(), true);

		ini_set('display_errors', 0);
		error_reporting(0);

		$html = new DOMDocument;
		$html->loadHTML($page);

		$xpath = new DOMXPath($html);
		$title = $xpath->query('//head/title')->item(0)->nodeValue;
		$id = $xpath->query('//body/@id')->item(0)->nodeValue;
		$content = $xpath->query('//div[@id="main"]')->item(0);

		$dom = new DOMDocument;
		$dom->appendChild($dom->importNode($content, true));
		$content = $dom->saveHTML();

		header('Content-type:application/x-javascript');

		echo json_encode(compact('title', 'id', 'content'));
		exit;
	}

	/**
	 * Schedule
	 */
	public function schedule() {
		$this->load->library('Html', 'html');
		$this->load->library('bmShoutCastInfo');
		$this->load->page('Schedule');
		$this->load->modules(array('iSchedule', 'Player'));

		$this->shoutcast = new bmShoutCastInfo(
			$this->config->shoutcast['host'],
			$this->config->shoutcast['port'],
			$this->config->shoutcast['password']
		);

		$this->config->shoutcast['status'] = $this->shoutcast->getStreamStatus();

		$content = $this->html->unwrap($this->html->unwrap(str_replace($this->config['url'], $this->config['url'].'/#', $this->iSchedule->render(1))));
		$info = preg_replace('/^.*?<p>(.*?)<\/p>.*$/si', '$1', $this->Player->render());

		header('Content-type:application/x-javascript');

		echo json_encode(compact('content', 'info'));
		exit;
	}

}
?>