<?php
/**
 * Controlador de requests no twitter
 */
class ApiRequest extends DataController {

	public $table = 'api_requests';

	/**
	 * Realiza a requisição, verificando o intervalo
	 */
	public function request() {
		$this->load->library('Twitter', 'twitter');
		$this->load->model('general');
		$this->load->data(array('Listener', 'Tweet'));

		$now = mktime();

		// Verifica se o registro de requisição existe e se já bateu o intervalo especificado
		if ($this->find(1)) {
			if ($now - strtotime($this['date']) >= $this->config->twitter['request_interval']) {
				$this->update(array('date' => date('Y-m-d H:i:s', $now)));
			}
			else return false;
		}
		else {
			$this->create(array('date' => date('Y-m-d H:i:s', $now)));
			$this->find(1);
		}

		// Formata os parâmetros para buscar os tweets
		$q = urlencode($this->general->info['twitter_tag']);
		$result_type = 'recent';
		$rpp = 100;

		// Captura o último tweet para formar um $since_id (assim não corre o risco de ter tweets duplicados)
		if ($this->Tweet->find(null, 'created_at DESC', 1)) {
			$since_id = $this->Tweet['TWEET_ID'];
		}

		// Realiza a busca através da API do Twitter
		$search = $this->twitter->search('search', compact('q', 'result_type', 'rpp', 'since_id'));

		// Se obtiver resultados
		if (isset($search->results) && $search->results) {
			$tweets = array();
			$users = array();

			// Varre os resultados formando os tweets
			foreach ($search->results as $tweet) {
				$tweets[$tweet->id_str] = $tweet;
			}

			// Varre os tweets buscando seus usuários
			foreach ($tweets as $id => &$tweet) {
				// Se o usuário já foi definido, apenas passa o valor e continua
				if (isset($users[$tweet->from_user])) {
					$tweet->from_user_id_str = $users[$tweet->from_user];
					continue;
				}
				// Senão, vamos ter que fazer alguns testes
				else {
					// Se o usuário não existe no banco de dados OU (existe e há tempo para atualizar seus dados)
					// Então iremos buscá-lo na API do Twitter para, no primeiro caso, criá-lo, e, no segundo, atualizá-lo
					if (!$this->Listener->find(array('screen_name' => $tweet->from_user)) || $now >= strtotime($this['date'])) {
						$user = $this->twitter->call('users/show', array('screen_name' => $tweet->from_user));

						// Precisamos aumentar o limite para uma nova requisição
						$ubber_limit = strtotime('+'.$this->config->twitter['request_interval'].' seconds', strtotime($this['date']));
						$this['date'] = date('Y-m-d H:i:s', $ubber_limit);

						// Caso não haja ID do usuário. Isso pode acontecer se a requisição der problema
						if (!isset($user->id_str)) {
							unset($tweets[$id]);
							continue;
						}

						if ($this->Listener->find($user->id_str)) {
							$this->Listener->update($user);
						}
						else {
							$this->Listener->create($user);
						}

						$tweet->from_user_id_str = $users[$tweet->from_user] = $user->id_str;
					}
					// Se o usuário existe no banco de dados e não há tempo para atualizá-lo, só passar seu ID
					else {
						$tweet->from_user_id_str = $users[$tweet->from_user] = $this->Listener['LISTENER_ID'];
					}
				}
			}

			// Faz o update da data (se ela foi alterada no looping acima)
			$this->update();

			// Cria os novos tweets
			foreach ($tweets as $_tweet) {
				$this->Tweet->create($_tweet);
			}
		}
	}

}
?>