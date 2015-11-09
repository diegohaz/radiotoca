<?php
include_once 'Begin.php';

class Company extends Begin {

	public $slug = 'radio';

	/**
	 * Action index
	 */
	public function index() {
		$this->set('browser_title', $this->page['browser_title']);
		$this->set('page', $this->model->content->toString());

		$this->render('layout');
	}

}
?>