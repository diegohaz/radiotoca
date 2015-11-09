<?php
if (!parse_class('UserAgent')):
/**
 * UserAgent Class
 *
 * Classe responsável pelos processos relacionados ao agente do usuário.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.8
 * @version 			0.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	15/09/2010
 *
 * @todo Implementar a funcionalidade
 */

class UserAgent extends Library {

	public $agent;

	/**
	 * Construtor.
	 *
	 * @since 				Neleus 0.2.8
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	15/09/2010
	 *
	 * @uses Library::__construct
	 */
	public function __construct() {
		parent::__construct();

		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$this->agent = trim($_SERVER['HTTP_USER_AGENT']);
		}
	}

}
endif;
?>