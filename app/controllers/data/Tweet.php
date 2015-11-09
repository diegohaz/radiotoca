<?php
/**
 * Controlador de tweets
 */
class Tweet extends DataController {

	public $listener;
	public $model = array('generic' => 'tweets', 'tweet');

	/**
	 * Construtor
	 */
	public function __construct() {
		parent::__construct();

		$this->load->data('Listener');
	}

	/**
	 * Tweet create
	 */
	public function create() {
		$values = func_get_args();

		if (!isset($values[0])) {
			return false;
		}
		elseif (is_object($values[0])) {
			$this->load->library('Html', 'html');

			$tweet = $values[0];
			$values = array();
			$values['TWEET_ID'] = $tweet->id_str;
			$values['LISTENER_ID'] = $tweet->from_user_id_str;
			$values['created_at'] = date('Y-m-d H:i:s', strtotime($tweet->created_at));

			$to_replace = array();

			// Captura os www.links
			if (preg_match_all('/((?:http\:\/\/|www\.)[^ ]+[^ .,;])/', $tweet->text, $links, PREG_SET_ORDER)) {
				foreach ($links as $link) {
					$link = $link[0];
					$href = strpos($link, 'http://') === 0? $link : 'http://'.$link;
					$to_replace[$link] = $this->html->tag('a', $link, array('href' => $href, 'target' => '_blank'));
				}
			}

			// Captura as #hashtags
			if (preg_match_all('/(\#[a-z0-9áéíóúàâêôãõ]+)/i', $tweet->text, $hashtags, PREG_SET_ORDER)) {
				foreach ($hashtags as $hashtag) {
					$hashtag = $hashtag[0];
					$href = $this->config->twitter['url'].'search?q='.urlencode($hashtag);
					$to_replace[$hashtag] = $this->html->tag('a', $hashtag, array('href' => $href, 'target' => '_blank'));
				}
			}

			// Captura as @mentions
			if (preg_match_all('/(\@[-_a-z0-9]+)/i', $tweet->text, $mentions, PREG_SET_ORDER)) {
				foreach ($mentions as $mention) {
					$mention = $mention[0];
					$href = $this->config->twitter['url'].str_replace('@', '', $mention);
					$to_replace[$mention] = $this->html->tag('a', $mention, array('href' => $href, 'target' => '_blank'));
				}
			}

			$values['text'] = $to_replace? str_replace(array_keys($to_replace), array_values($to_replace), $tweet->text) : $tweet->text;
		}
		elseif (is_array($values[0])) {
			return parent::create($values[0]);
		}

		if ($values && parent::create($values)) {
			$this->load->data('Meta', 'meta');
			$this->Meta->find(array('attribute' => 'tweets_count'));
			$this->Meta['value'] = $this->Meta['value'] + 1;
			$this->Meta->update();

			$this->Listener->find($values['LISTENER_ID']);
			$this->Listener['tweets_count'] = $this->Listener['tweets_count'] + 1;
			$this->Listener->update();

			return true;
		}
		else return false;
	}

	/**
	 * Tweet delete
	 */
	public function delete($where = null, $limit = null) {
		if (!isset($where)) {
			if ($this->activeRecord) {
				$where = array(key($this->activeRecord) => current($this->activeRecord));
				$this->activeRecord = null;
			}
			else return false;
		}

		$this->load->data('Listener');
		$this->load->data('Meta', 'meta');

		if (!$this->find($where, $limit)) {
			return false;
		}

		// Precisamos alterar o contador no ouvinte
		while ($this->fetch()) {
			$this->Listener->find(array('LISTENER_ID' => $this['LISTENER_ID']));
			$this->Listener->update(array('tweets_count' => $this->Listener['tweets_count'] - 1));
		}

		// Agora alterar o contador global de tweets
		$this->Meta->find(array('attribute' => 'tweets_count'));

		if ($deleted = parent::delete($where, $limit)) {
			$this->Meta->update(array('value' => $this->Meta['value'] - $this->db->affected_rows));
		}

		return $deleted;
	}

	/**
	 * Captura um tweet de forma apropriada
	 */
	public function get($id = null) {
		if ((!isset($id) || !$this->find($id)) && !$this->activeRecord) {
			return false;
		}

		// Formata as datas
		$today = strtotime($this->config['hour_diff']);
		$tweet_date = strtotime($this['created_at']);


		// Começa a capturar o tweet
		$tweet = new stdClass;
		$tweet->listener = $this->listener;
		$tweet->text = $this['text'];
		$tweet->timestamp = $tweet_date;

		// Tenta capturar a data do modo '12 minutes ago'
		if ((($seconds = $today - $tweet_date) < 60) || (($minutes = round($seconds/60)) < 60)
		OR	(($hours = round($minutes/60)) < 24) || (($days = round($hours/24)) < 4)) {
			if (isset($days)) $time = 'days';
			elseif (isset($hours)) $time = 'hours';
			elseif (isset($minutes)) $time = 'minutes';
			elseif (isset($seconds)) $time = 'seconds';

			$s = ${$time} > 1? 's' : '';

			if ($time == 'days') {
				$hour = date('H:i', $tweet_date);
				$tweet->date = $this->model->date->days->vars(array('n' => $days, 's' => $s, 'hour' => $hour))->toString();
			}
			else {
				$tweet->date = $this->model->date->$time->vars(array('n' => ${$time}, 's' => $s))->toString();
			}
		}
		// Tenta capturar a data do modo '12 Nov 2009, at 02:50'
		else {
			$tweet_date = date('j n Y H:i', $tweet_date);
			list($day, $month, $year, $hour) = explode(' ', $tweet_date);
			$month = $this->model->months->xpath("month[@number='$month']", true)->toString();

			$tweet->date = $this->model->date->default->vars(compact('day', 'month', 'year', 'hour'))->toString();
		}

		// Captura o restante das informações
		$tweet->url = $this->listener->twitterUrl.'/statuses/'.$this['TWEET_ID'];
		$tweet->footer = $this->model->footer->vars(array(
			'tweet_url' => $tweet->url,
			'date' => $tweet->date,
			'listener_url' => $tweet->listener->twitterUrl,
			'listener' => '@'.$tweet->listener->screenName
		))->toString();

		return $tweet;
	}

	/**
	 * Define o tweet atual
	 */
	protected function setCurrent() {
		parent::setCurrent();

		$this->Listener->find($this['LISTENER_ID']);
		$this->listener = $this->Listener->get();
	}

}
?>