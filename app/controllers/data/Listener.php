<?php
/**
 * Controlador de ouvintes
 */
class Listener extends DataController {

	public $model = array('generic' => 'listeners', 'listener');

	private $doNotUpdate = array('tweets_count');
	private $apiToDb = array('LISTENER_ID' => 'id_str', 'image_url' => 'profile_image_url');

	/**
	 * Listener create
	 */
	public function create() {
		$values = func_get_args();

		if (!isset($values[0])) {
			return false;
		}
		elseif (is_object($values[0])) {
			$user = $values[0];
			$values = array();
			$fields = array('LISTENER_ID', 'screen_name', 'name', 'description', 'url', 'location', 'image_url');

			// Retira os outros usuários que possuem o mesmo screen name e seus tweets
			$this->delete(array('screen_name' => $user->screen_name));

			foreach ($fields as $field) {
				$values[$field] = isset($this->apiToDb[$field])? $user->{$this->apiToDb[$field]} : $user->$field;
			}
		}
		elseif (is_array($values[0])) {
			$values = $values[0];
		}

		if ($values) {
			$created = parent::create($values);
		}

		if (isset($created) && $created) {
			$this->load->data('Meta', 'meta');
			$this->Meta->find(array('attribute' => 'listeners_count'));
			$this->Meta['value'] = $this->Meta['value'] + 1;
			$this->Meta->update();

			return true;
		}
		else return false;
	}

	/**
	 * Listener update
	 */
	public function update($values = null, $where = null, $limit = null, $extra = null) {
		if (is_object($values)) {
			$user = $values;
			$values = array();

			if (!$this->activeRecord && !$this->find($user->id_str)) {
				return false;
			}

			foreach ($this->activeRecord as $field => $value) {
				if (in_array($field, $this->doNotUpdate)) continue;

				$update_value = isset($this->apiToDb[$field])? $user->{$this->apiToDb[$field]} : $user->$field;

				if ($update_value != $value) {
					if ($field == 'screen_name') {
						// Retira os outros usuários que possuem o mesmo screen name e seus tweets
						$listener = new Listener;
						$listener->delete(array('screen_name' => $value));
					}

					$values[$field] = $update_value;
				}
			}
		}

		if ($values || !isset($values)) {
			return parent::update($values, $where, $limit, $extra);
		}
	}

	/**
	 * Listener delete
	 */
	public function delete($where = null, $limit = null) {
		if (!isset($where)) {
			if ($this->activeRecord) {
				$where = array(key($this->activeRecord) => current($this->activeRecord));
				$this->activeRecord = null;
			}
			else return false;
		}

		$this->load->data('Tweet');
		$this->load->data('Meta', 'meta');

		if (!$this->find($where, $limit)) {
			return false;
		}

		// Precisamos deletar os tweets deste ouvinte
		foreach ($this->getResult() as $listener) {
			$this->Tweet->delete(array('LISTENER_ID' => $listener['LISTENER_ID']));
		}

		// Agora alterar o contador global de ouvintes
		$this->Meta->find(array('attribute' => 'listeners_count'));

		if ($deleted = parent::delete($where, $limit)) {
			$this->Meta->update(array('value' => $this->Meta['value'] - $this->db->affected_rows));
		}

		return $deleted;
	}

	/**
	 * Captura um ouvinte de forma apropriada
	 */
	public function get($id = null) {
		if ((!isset($id) || !$this->find($id)) && !$this->activeRecord) {
			return false;
		}

		// Geral
		$listener = new stdClass;
		$listener->name = $this['name'];
		$listener->screenName = $this['screen_name'];
		$listener->desc = $this['description'];
		$listener->descExcerpt = strlen($listener->desc) > 50? substr($listener->desc, 0, 50).'...' : $listener->desc;
		$listener->url = $this['url'];
		$listener->location = $this['location'];
		$listener->localUrl = $this->load->action('Listeners.listener')->url($this['LISTENER_ID']);
		$listener->twitterUrl = 'http://twitter.com/'.$listener->screenName;

		// Imagens
		$listener->img = $this['image_url'];
		$listener->imgMini = $this->getProfileImage('mini');
		$listener->imgBigger = $this->getProfileImage('bigger');
		$listener->imgProfile = $this->getProfileImage('reasonably_small');

		// Tweets
		$listener->tweetsCount = $this['tweets_count'];
		$listener->tweetsCountText = $this->model->tweetsCount->vars(array('n' => $this['tweets_count'], 's' => ($this['tweets_count'] > 1? 's' : '')))->toString();
		$listener->tweetsUrl = $this->load->action('Tweets.listener')->url($this['LISTENER_ID']);

		return $listener;
	}

	/**
	 * Formata o image pattern
	 */
	private function getProfileImage($sufix = null, $image_url = null) {
		if (!isset($image_url)) {
			if (isset($this['image_url'])) {
				$image_url = $this['image_url'];
			}
			else return false;
		}

		if (!isset($sufix)) {
			return $image_url;
		}

		return preg_replace('/_[a-z]+(\.[a-z]+)$/i', "_$sufix$1", $image_url);
	}

}
?>