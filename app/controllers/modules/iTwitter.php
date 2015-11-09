<?php
/**
 * Módulo iTwitter
 */
class iTwitter extends ModuleController {

	public $model = array('generic' => 'general', 'iTwitter');
	public $view = 'itwitter';

	/**
	 * Renderiza o módulo
	 */
	public function render($form = true, $num_tweets = 2) {
		$this->load->library('Router', 'route');
		$this->load->data('Tweet');

		// Define o form
		if ($form) {
			$this->set('form', $this->form());
		}

		// Define as labels
		$this->set('labels', $this->route->controller->vars['labels']);

		// Começa a definir os tweets
		$tweets = array();
		$this->Tweet->find(null, 'created_at DESC', $num_tweets);

		while ($this->Tweet->fetch()) {
			$tweets[] = $this->Tweet->get();
		}

		$this->set('tweets', $tweets);

		return parent::render(true);
	}

	/**
	 * Captura o formulário
	 */
	public function form() {
		$this->load->library('Router', 'route');
		$this->load->model('general');

		$controller = $this->route->controller;

		if ($controller->model && isset($controller->model->iTwitter)) {
			$this->model->merge($controller->model->iTwitter);
		}

		$legend = $this->model->form->legend->toString();
		$desc = $this->model->form->desc->vars(array('twitter_tag' => $this->general->info['twitter_tag']))->toString();
		$button = $this->general->labels['tweet_button']->toString();

		$this->set(compact('legend', 'desc', 'button'));

		return parent::render('itwitter.form', true);
	}
}
?>