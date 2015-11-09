<?php
include_once 'Begin.php';

/**
 * Página fale conosco
 */
class Contact extends Begin {

	public $slug = 'faleconosco';
	public $actions = array(
		'send' => false,
		'reset' => false
	);

	public $sessionFields = array('name', 'email');
	public $fields = array();

	/**
	 * Action index
	 */
	public function index() {
		// Carrega algumas coisas
		$this->load->library('Form', 'form');

		// Carrega os campos
		$this->fields = $this->form->getFields($this->model->xpath('fields/*'));
		$this->parseValues();

		// Envia?
		if (isset($_POST['submit'])) {
			if ($this->form->validate($this->fields)) {
				// Se o formulário é válido, envia
				$this->send();
			}
			else {
				// Se o formulário não é válido, exibe o feedback de erro
				$feedback = new stdClass;
				$feedback->type = 'error';
				$feedback->text = $this->model->feedback->error;
				$this->set('feedback', $feedback);
			}
		}
		// Reseta?
		elseif (isset($_POST['reset'])) {
			$this->reset();
		}

		// Define as variáveis da view
		$this->set('browser_title', $this->page['browser_title']->toString());
		$this->set('fields', $this->fields);
		$this->set('legend', $this->model->info['legend']->toString());
		$this->set('page', $this->render('contact', true));

		$this->render('layout');
	}

	/**
	 * Envia o e-mail
	 */
	public function send() {
		$this->load->library('Mail');

		// Já inicia o feedback, alguma coisa ele vai exibir
		$feedback = new stdClass;

		// Prepara o e-mail
		$mail = new Mail;
		$mail->subject = $this->model->info->vars(array('name' => $this->fields['name']->value))->attributes()->subject;
		$mail->to = $this->model->info->vars(array('email' => $this->general->info['email']->toString()))->attributes()->to;
		$mail->from = array($this->fields['name']->value => $this->fields['email']->value);

		// Insere os campos 'Campo: valor' no corpo da mensagem
		foreach ($this->fields as $field) {
			$mail->msg .= $field->label.': '.$field->value."\r\n";
		}

		// Insere informações adicionais no corpo da mensagem
		$mail->msg .= "\r\n----------------------------------------\r\n\r\n";
		$mail->msg .= "IP: $_SERVER[REMOTE_ADDR]\r\n";
		$mail->msg .= "Browser: $_SERVER[HTTP_USER_AGENT]\r\n";

		// Envia?
		if ($sent = $mail->send()) {
			$feedback->type = 'success';
			$feedback->text = $this->model->feedback->success;
		}
		else {
			$feedback->type = 'error';
			$feedback->text = $this->model->feedback->undefined;
		}

		$this->set('feedback', $feedback);

		return $sent;
	}

	/**
	 * Reseta o formulário
	 */
	public function reset() {
		if (!isset($_SESSION)) {
			session_start();
		}

		// Limpa os valores dos campos
		foreach ($this->fields as $field) {
			// Se existe rótulo, insere o mesmo como valor padrão
			if (isset($field->label)) {
				$field->value = $field->label;

				if (!in_array('sample', $field->class)) {
					$field->class[] = 'sample';
				}
			}
			else {
				$field->value = '';
			}
		}

		// Limpa a sessão
		foreach ($this->sessionFields as $name) {
			unset($_SESSION[$name]);
		}
	}

	/**
	 * Passa os valores para os campos
	 */
	private function parseValues() {
		if (!isset($_SESSION)) {
			session_start();
		}
		
		if (isset($this->config['ajax']) && $this->config['ajax']) {
			foreach ($_GET as $attr => $value) {
				$_POST[$attr] = $value;
			}
		}

		foreach ($this->fields as $name => $field) {
			// Se o formulário foi submetido e o campo passado...
			if (isset($_POST[$name])) {
				$field->value = $_POST[$name];

				// Se o campo deve ser armazenado na sessão, armazenar
				if (in_array($name, $this->sessionFields)) {
					$_SESSION[$name] = $field->value;
				}
			}
			// Se o campo deve ser resgatado da sessão e este existe na mesma, aproveitar
			elseif (in_array($name, $this->sessionFields) && isset($_SESSION[$name])) {
				$field->value = $_SESSION[$name];
			}
			// Se não há valor e existe um rótulo para o campo, este rótulo deve ser o valor default
			elseif (isset($field->label)) {
				$field->value = $field->label;
			}

			// Caso o valor do campo seja igual ao valor do rótulo, inserir classe 'sample'
			if (isset($field->label) && $field->value == $field->label) {
				$field->class[] = 'sample';
			}
		}
	}

}
?>