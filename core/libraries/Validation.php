<?php
if (!parse_class('Validation')):
/**
 * Validation Class
 *
 * Classe responsável pela validação de dados.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.6
 * @version 			0.2
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

class Validation extends Library {

	const VALID_EMAIL = '/^[^!@#$%&\'\"\*\()\[\]\{}\/\; ]+@[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_\.]+$/';

}
endif;
?>