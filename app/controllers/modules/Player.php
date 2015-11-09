<?php
/**
 * Módulo player
 */
class Player extends ModuleController {

	public $model = array('generic' => 'general', 'iSchedule');
	public $view = 'player';

	/**
	 * Renderiza o módulo
	 */
	public function render() {
		// Carrega algumas coisas
		$this->load->library('Router', 'route');
		$this->load->page('Schedule');

		// Define algumas coisas
		$controller = $this->route->controller;

		if ($controller->model && isset($controller->model->iSchedule)) {
			$this->model->merge($controller->model->iSchedule);
		}
		
		if ($this->config->shoutcast['status']) {
			$online = true;
			$song = $controller->shoutcast->getSongTitle();
			$program = $this->Schedule->getCurrentProgram();
		}
		else {
			$online = false;
			$offline_message = $this->model->titles->offline->toString();
		}
		
		$this->set(compact('online', 'song', 'program', 'offline_message'));

		return parent::render(true);
	}

}
?>