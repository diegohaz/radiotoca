<?php
/**
 * Módulo Top
 */
class Top extends ModuleController {

	/**
	 * Renderiza o TopSongs
	 */
	public function songs($class = null, $quantity = 5) {
		$this->load->library('Router', 'route');
		$this->load->page('Schedule');

		if (is_array($class)) {
			$class = implode(' ', $class);
		}

		$this->set('class', $class);
		$this->set('labels', $this->route->controller->vars['labels']);
		$this->set('songs', $this->Schedule->getSongs($quantity));

		return $this->render('top.songs', true);
	}

	/**
	 * Renderiza o TopListeners
	 */
	public function listeners($class = null, $special = true, $quantity = 10) {
		$this->load->library('Router', 'route');
		$this->load->data('Listener');

		if (is_array($class)) {
			$class = implode(' ', $class);
		}

		// Captura os ouvintes
		$listeners = array();
		$this->Listener->find(null, 'tweets_count DESC', $quantity);

		while ($this->Listener->fetch()) {
			$listeners[] = $this->Listener->get();
		}

		$this->set(compact('class', 'special', 'listeners'));
		$this->set('labels', $this->route->controller->vars['labels']);

		return $this->render('top.listeners', true);
	}

}
?>