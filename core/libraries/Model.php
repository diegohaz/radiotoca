<?php
if (!class_exists('Model')):
/**
 * Model Class
 *
 * Classe responsável pela manipulação dos models da aplicação.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1
 * @version 			0.4.2
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	19/09/2010
 */

class Model extends SimpleXMLElement {

	/**
	 * Substitui método nativo da classe SimpleXMLElement para aceitar um parâmetro adicional que
	 * faz com que o método retorne apenas o primeiro elemento encontrado e não uma array.
	 *
	 * @since 				Neleus 0.1.2
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/05/2010
	 *
	 * @param $xpath (string) - Caminho do nó no formato XPath.
	 * @param $get_the_first (boolean) - Determina a saída do método.
	 *
	 * @return Array com os objetos encontrados (padrão) ou apenas o primeiro objeto encontrado.
	 */
	public function xpath($path, $get_the_first = false) {
		$elements = parent::xpath($path);

		if ($get_the_first && isset($elements[0])) {
			return $elements[0];
		}

		return $elements;
	}

	/**
	 * Insere um wrapper no elemento atual.
	 *
	 * @since 				Neleus 0.1.2
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	16/07/2010
	 *
	 * @uses basics::import
	 * @uses Html::wrap
	 *
	 * @param $tag (string) - Nome da tag que será inserida como wrapper.
	 * @param $attrs (array) - Atributos a serem adicionados na tag wrapper.
	 *
	 * @return String com o elemento com seu wrapper.
	 */
	public function wrap($tag, $attrs = null) {
		$html = import('Html', true);

		return $html->wrap($this->asXML(), $tag, $attrs);
	}

	/**
	 * Remove um wrapper do elemento atual, se o parâmetro tag não for informado, ele remove o
	 * nó root do elemento.
	 *
	 * @since 				Neleus 0.1.2
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 *
	 * @uses basics::import
	 * @uses Html::unwrap
	 *
	 * @param $tag (string) - Wrapper que será removido.
	 *
	 * @return String com o elemento sem seu wrapper.
	 */
	public function unwrap($tag = null) {
		$html = import('Html', true);

		return $html->unwrap($this->asXML(), true);
	}

	/**
	 * Varre o model em busca de chamadas de variáveis passadas ao parâmetro $vars.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	16/07/2010
	 *
	 * @param $vars (array) - Conjunto 'name' => 'value' das variáveis que devem ser substituídas no model.
	 * @param $output (string) - Formato de saída do método: xml ou string.
	 *
	 * @return O objeto Model ou a string com as variáveis alteradas.
	 */
	public function vars($vars, $output = 'xml') {
		if (!is_array($vars) && !is_object($vars)) {
			if ($output == 'xml')
				return $this;
			else
				return $this->asXML();
		}

		$contents = $this->asXML();

		foreach ($vars as $name => $value) {
			$contents = preg_replace("/\{$name}/", $value, $contents);
		}

		if ($output == 'xml') {
			return new Model($contents);
		}
		else {
			return $contents;
		}
	}

	/**
	 * Retorna o Model como array.
	 *
	 * @since 				Neleus 0.2.4
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/07/2010
	 *
	 * @return Array com os elementos do Model.
	 */
	public function toArray() {
		$array = array();
		$j = 0;

		foreach ($this as $i => $element) {
			if (isset($array[$i])) {
				$i = $j;
			}

			$array[$i] = $element;
			$j++;
		}

		return $array;
	}

	/**
	 * Retorna o Model como string.
	 *
	 * @since 				Neleus 0.2.4
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 *
	 * @return String com o model.
	 */
	public function toString() {
		return $this->__toString();
	}

	/**
	 * Determina como o elemento se comportará ao ser convertido em string.
	 *
	 * @since 				Neleus 0.1.2
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	10/07/2010
	 *
	 * @uses Model::unwrap
	 */
	public function __toString() {
		if ($this->children()) {
			return $this->unwrap();
		}
		else {
			return (string)$this;
		}
	}

	/**
	 * Cria um novo model para a aplicação.
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	10/07/2010
	 *
	 * @uses Model::parseAttributes
	 *
	 * @param $path (string) - Caminho do model a ser criado.
	 *
	 * @return O objeto Model ou FALSE, em caso de erro.
	 */
	public static function create($path) {
		if (file_exists($path)) {
			$model = self::parseFunctions($path);

			if (defined('LIBXML_COMPACT'))
				$model = new Model($model, LIBXML_COMPACT);
			else
				$model = new Model($model);

			return $model;
		}
		else return false;
	}

	/**
	 * Analisa o conteúdo do model em busca de chamadas de funções no formato {func(params)}
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	26/07/2010
	 *
	 * @param $path (string) - Caminho do model a ser analisado.
	 *
	 * @return String com o conteúdo do model devidamente adaptado ou FALSE em caso de erro.
	 */
	public static function parseFunctions($path) {
		if (file_exists($path)) {
			$contents = file_get_contents($path);

			while (preg_match('/\{([-_a-z0-9]+)\(([^\}]*)\)}/i', $contents, $parts)) {
				$func = $parts[1];
				$args = $parts[2];

				if (function_exists($func)) {
					eval("\$return = $func($args);");
					$contents = str_replace($parts[0], $return, $contents);
				}
				else {
					$contents = str_replace($parts[0], "\{[_nls_]$func($args)}", $contents);
				}
			}

			$contents = str_replace('{[_nls_]', '{', $contents);

			return $contents;
		}
		else return false;
	}

}
endif;
?>