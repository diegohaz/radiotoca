<?php
if (!parse_class('Mail')):
/**
 * Mail Class
 *
 * Classe responsável pelos processos de envio de e-mails.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.6
 * @version 			0.2
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

class Mail extends Library {

	public $to;
	public $cc;
	public $bcc;
	public $subject;
	public $from;
	public $replyTo;
	public $type;
	public $encoding;
	public $headers = array();
	public $msg;

	private $parsed;

	/**
	 * Realiza algumas operações sobre as propriedades do objeto Mail.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	08/08/2010
	 *
	 * @uses Library::__construct
	 */
	public function __construct() {
		parent::__construct();

		$this->type = 'text/plain';
		$this->encoding = $this->config['encoding'];
	}

	/**
	 * Realiza o envio do e-mail.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	08/08/2010
	 *
	 * @return TRUE se o email foi enviado com sucesso ou FALSE caso contrário.
	 */
	public function send() {
		$this->parseParams();

		return mail($this->to, $this->subject, $this->msg, $this->headers);
	}

	/**
	 * Analisa e converte apropriadamente as propriedades do objeto.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	08/08/2010
	 *
	 * @uses Mail::encode
	 * @uses Mail::parseUserEmail
	 */
	private function parseParams() {
		if (!$this->parsed) {
			$this->parsed = true;
		}
		else return;

		$this->subject = $this->encode($this->subject);

		$user_email = array(&$this->to, &$this->cc, &$this->bcc, &$this->from, &$this->replyTo);

		foreach ($user_email as &$email) {
			if (empty($email)) continue;

			if (!is_array($email)) {
				$email = explode(',', $email);
				$email = array_map('trim', $email);
			}

			$email = $this->parseUserEmail($email);
		}

		if (!is_array($this->headers)) {
			$this->headers = explode("\r\n", $this->headers);
		}

		$this->headers[] = "Content-type: $this->type; charset=$this->encoding";

		if ($this->type == 'text/html') {
			array_unshift($this->headers, 'MIME-Version: 1.0');
		}
		else {
			$this->msg = wordwrap($this->msg, 70);
		}

		if ($this->cc) $this->headers[] = "Cc: $this->cc";
		if ($this->bcc) $this->headers[] = "Bcc: $this->bcc";
		if ($this->from) $this->headers[] = "From: $this->from";
		if ($this->replyTo) $this->headers[] = "Reply-To: $this->replyTo";

		$this->headers = implode("\r\n", $this->headers);
	}

	/**
	 * Analisa e converte apropriadamente uma sequência correspondente a um usuário e e-mail.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	08/08/2010
	 *
	 * @uses Mail::encode
	 *
	 * @param $user_email (array|string) - Sequência de usuário e e-mail a ser analisada e convertida.
	 *
	 * @return String com a correta representação da sequência para passagem do parâmetro à função mail.
	 */
	private function parseUserEmail($user_email) {
		if (is_array($user_email)) {
			$emails = array();

			foreach ($user_email as $name => $email) {
				if (!is_string($name) && !is_array($email)) {
					$emails[] = $this->parseUserEmail($email);
				}
				else {
					$name = $this->encode($name);
					$emails[] = "$name <$email>";
				}
			}

			return implode(', ', $emails);
		}
		elseif (preg_match('/^(.+) <([^>]+)>$/', $user_email, $parts)) {
			return $this->parseUserEmail(array($parts[1] => $parts[2]));
		}
		else {
			return $user_email;
		}
	}

	/**
	 * Codifica a string passada no formato especificado na propriedade $encoding. Realiza também o processo
	 * de codificação para base64, afim de evitar problemas com caracteres no e-mail.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	08/08/2010
	 *
	 * @param $string (string) - String a ser convertida.
	 *
	 * @return String convertida.
	 */
	private function encode($string) {
		return "=?$this->encoding?b?".base64_encode($string)."?=";
	}

}
endif;
?>