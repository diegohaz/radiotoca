<?php
/**
 * Módulo iSchedule
 */
class iSchedule extends ModuleController {

	public $model = array('generic' => 'general', 'iSchedule');
	public $view = 'ischedule';

	/**
	 * Renderiza o módulo
	 */
	public function render($schedule_qt, $get = true) {
		// Carrega algumas coisas
		$this->load->library('Router', 'route');
		$this->load->library('Inflector', 'inflect');
		$this->load->library('Html', 'html');
		$this->load->page('Schedule');

		// Define algumas coisas
		$controller = $this->route->controller;

		if ($controller->model && isset($controller->model->iSchedule)) {
			$this->model->merge($controller->model->iSchedule);
		}

		// Captura os elementos
		$labels = $this->model->xpath('labels/*', $schedule_qt);
		$elements = array();

		foreach ($labels as $i => $label) {
			$schedule = new stdClass;
			$schedule->class = $this->inflect->slug($label->getName());
			$schedule->label = $label->toString();
			$title = 'songProgram'; # Armazena o tipo de título que será mostrado, isso pode mudar nos próximos processos
			$vars = array();

			// Captura o programa
			$program = $i == 0? $this->Schedule->getCurrentProgram() : $this->Schedule->getNextProgram();
			$program_title = $complete_program_title = $program->title;
			$program_url = $program->url;

			$vars['program'] = $this->model->titles->program->vars(compact('complete_program_title', 'program_url'));

			// Captura a música
			if ($i == 0) {
				if ($this->config->shoutcast['status']) {
					$song_title = $complete_song_title = htmlspecialchars($controller->shoutcast->getSongTitle(), ENT_QUOTES, $this->config['encoding']);

					if ($song = $this->Schedule->getSongByTitle($complete_song_title)) {
						$song_url = $song->url;
					}

					$vars['song'] = $this->model->titles->song->vars(compact('complete_song_title', 'song_url'));
				}
				// Se a rádio estiver offline, exibe o título offline
				else {
					$title = 'offline';
				}
			}
			else {
				$song = $this->Schedule->getRandomSongByProgram($program, true);

				if ($song) {
					$model = $this->model->titles->song;

					$song_title = $complete_song_title = isset($model['maxlength']) && strlen($song->label) <= $model['maxlength']? $song->label : $song->title;
					$song_url = $song->url;
					$vars['song'] = $this->model->titles->song->vars(compact('complete_song_title', 'song_url'));
				}
				else {
					$dj_title = $program->dj;
					$vars['dj'] = $this->model->titles->dj;
					$title = 'programDj';
				}
			}

			// Verifica se as variáveis são maiores que o atributo maxlength definido nelas
			foreach ($vars as $key => $content) {
				$element = $this->model->titles->$key;
				$element_title = $key.'_title';

				if (isset($element['maxlength']) && strlen(${$element_title}) > $element['maxlength']) {
					${$element_title} = substr(${$element_title}, 0, $element['maxlength']).'...';
				}

				$vars[$key] = $vars[$key]->vars(compact($element_title))->toString();

				if (!isset(${$key.'_url'})) {
					$vars[$key] = $this->html->removeAttribute('href', $vars[$key]);
				}
			}

			// Define o título e a hora
			$schedule->title = $this->model->titles->$title->vars($vars);
			$schedule->hour = $i == 0? date('H:i', strtotime($this->config['hour_diff'])) : $program->hour;

			$elements[] = $schedule;
		}

		$this->set('elements', $elements);

		return parent::render($get);
	}

}
?>