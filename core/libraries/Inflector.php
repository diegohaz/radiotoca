<?php
if (!parse_class('Inflector')):
/**
 * Inflector Class
 *
 * Classe responsável pela conversão de strings em determinados formatos.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.3
 * @version 			0.3.2
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	15/09/2010
 */

class Inflector extends Library {

	public $libraries = array('content');

	/**
	 * Converte a string passada em uma slug.
	 *
	 * @since 				Neleus 0.2.3
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	15/09/2010
	 *
	 * @uses content::remove_accents
	 *
	 * @param $string (string) - A string que será convertida.
	 * @param $separator (string) - A string delimitadora de separação de palavras.
	 *
	 * @return String com o slug.
	 */
	public function slug($string, $separator = '-') {
		$string = strtolower(remove_accents($string));
		$map = array(
			'/\/|\s/' => $separator,
			"/[^a-z\d\\$separator]/" => '',
			"/\\$separator\\$separator/" => $separator
		);

		$string = preg_replace(array_keys($map), array_values($map), $string);

		return $string;
	}

	/**
	 * Converte a string passada no formato CamelCase.
	 *
	 * @since 				Neleus 0.2.3
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	18/07/2010
	 *
	 * @uses content::remove_accents
	 *
	 * @param $string (string) - A string que será convertida.
	 *
	 * @return String no formato CamelCase.
	 */
	public function camelize($string) {
		$string = remove_accents($string);
		$string = strtolower($string);
		$string = preg_replace('/[^a-z0-9]/', ' ', $string);
		$string = ucwords($string);
		$string = str_replace(' ', '', $string);

		return $string;
	}

	/**
	 * Insere underlines na string.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @uses Inflector::slug
	 *
	 * @param $string (string) - A string que será convertida.
	 */
	public function underscore($string) {
		return $this->slug($string, '_');
	}

	/**
	 * Converte a string no plural.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	15/09/2010
	 *
	 * @param $string (string) - A string que será convertida.
	 */
	public function pluralize($string) {
		if (preg_match('/s$/', $string)) {
			$string .= 'es';
		}
		else {
			$string .= 's';
		}

		return $string;
	}

	/**
	 * Converte a string no singular.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @param $string (string) - A string que será convertida.
	 */
	public function singularize($string) {
		return preg_replace('/s$/', '', $string);
	}

	/**
	 * Converte a string no formato de ID, camelCase.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @param $string (string) - A string que será convertida.
	 */
	public function id($string) {
		$string = $this->camelize($string);
		$string = strtolower($string[0]).substr($string, 1);

		return $string;
	}

	/**
	 * Converte a string no formato de nomes de tabela de bancos de dados.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	21/08/2010
	 *
	 * @param $string (string) - A string que será convertida.
	 */
	public function tableize($string) {
		return $this->pluralize($this->underscore($string));
	}

}
endif;
?>