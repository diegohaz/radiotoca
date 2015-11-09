<?php
if (!parse_class('Security')):
/**
 * Security Class
 *
 * Classe responsável pela segurança da aplicação.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.7
 * @version 			0.1.2
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/10/2010
 */

class Security extends Library {

	/**
	 * Previne ataques Cross Scripting Site.
	 *
	 * @since 				Neleus 0.2.7
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/10/2010
	 *
	 * @param $string (string) - A string a ser tratada.
	 *
	 * @return String devidamente tratada.
	 */
	public function xss($string, $strict = false) {
		if ($strict) {
			return strip_tags($string);
		}
		else {
			return htmlentities($string, ENT_QUOTES, $this->config['encoding']);
		}
	}

}
endif;
?>