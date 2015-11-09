<?php
if (!parse_class('Html')):
/**
 * Html Class
 *
 * Classe responsável pela manipulação de HTML, disponibilizando métodos para uso preferencial de
 * controllers e funções. Nas views é recomendável o uso do HTML puro, caso não haja dinâmica, para
 * agilizar o processamento.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1
 * @version 			0.4.3
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 */

class Html extends Library {

	public $language = 'xhtml11';

	public $doctypes = array(
		'xhtml11'				=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
		'xhtml1-strict'	=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
		'xhtml1-trans'	=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
		'xhtml1-frame'	=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
		'html5'					=> '<!DOCTYPE html>',
		'html4-strict'	=> '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
		'html4-trans'		=> '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
		'html4-frame'		=> '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'
	);

	public $selfClosingTags = array('area', 'base', 'br', 'hr', 'img', 'input', 'link', 'meta', 'param');

	/**
	 * Cria uma tag HTML.
	 *
	 * @usage
	 * // Create a paragraph <p> with content
	 * echo $html->tag('p', 'This is a paragraph!');
	 *
	 * // Create <div id="content"><p>This is a paragraph inside a DIV with ID</p></div>
	 * echo $html->tag('div[id="content"]', '<p>This is a paragraph inside a DIV with ID</p>');
	 * echo $html->tag('<div id="content">', '<p>This is a paragraph inside a DIV with ID</p>');
	 * echo $html->tag('div', '<p>This is a paragraph inside a DIV with ID</p>', array('id'=> 'content'));
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.7
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/07/2010
	 *
	 * @uses Html::parseTag
	 * @uses Html::formatAttributes
	 *
	 * @param $tag (string) - Nome da tag ou formato de tag a ser criado.
	 * @param $content (string) - Conteúdo a ser inserido na tag.
	 * @param $attrs (array) - Array contendo conjuntos atributo => valor a serem inseridos na tag.
	 *
	 * @return String contendo a tag formatada.
	 */
	public function tag($tag, $content = null, $attrs = null) {
		$this->parseTag($tag, $attrs);
		$this->formatAttributes(&$attrs);

		if (isset($content) && preg_match('/^\s*\</', $content) && (strlen($tag.$attrs.$content) > 70) ) {
			$content = "\r\n$content\r\n";
		}

		if (!isset($content) && in_array($tag, $this->selfClosingTags)) {
			$tag = "<$tag$attrs />\r\n";
		}
		else {
			$tag = "<$tag$attrs>$content</$tag>";
		}

		return $tag;
	}

	/**
	 * Configura o DOCTYPE da página.
	 *
	 * @usage
	 * echo $html->doctype(); // Shows the default doctype and default version to this on the page
	 * echo $html->doctype('html'); // Shows the HTML doctype with the last oficial version
	 * echo $html->doctype('xhtml', '1.0 transitional'); // Shows the specified doctype
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Html::parseLanguage
	 *
	 * @param $language (string) - Nome da linguagem a ser usada no DOCTYPE.
	 *
	 * @return String contendo o DOCTYPE.
	 */
	public function doctype($language = null) {
		$this->parseLanguage($language);
		$doctype = $this->doctypes[$language];

		return $doctype."\r\n";
	}

	/**
	 * Cria metatags HTML.
	 *
	 * @usage
	 * echo $html->meta('Content-Type'); // Shows the Content-Type metatag with the default content
	 * echo $html->meta('Description', 'This is a descrition!'); // Specific metatag
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	12/07/2010
	 *
	 * @uses Html::tag
	 *
	 * @param $name (string) - Nome da metatag a ser usado no atributo 'name' ou 'http-equiv'.
	 * @param $content (string) - Conteúdo da metatag a ser usado no atributo 'content'.
	 *
	 * @return String com a metatag formatada.
	 */
	public function meta($name, $content = null) {
		$lower_name = strtolower($name);
		$type = 'name';

		switch ($lower_name) {
			case 'content-type':
				$name = 'Content-Type';
				$type = 'http-equiv';

				if (!isset($content)) {
					$content = 'text/html; charset='.$this->config['encoding'];
				}
				break;
			case 'refresh':
				$name = 'Refresh';
				$type = 'http-equiv';
		}

		$attributes = array($type => $name, 'content' => $content);
		$meta = $this->tag('meta', null, $attributes);

		return $meta;
	}

	/**
	 * Cria um link HTML.
	 *
	 * @usage
	 * echo $html->link('Home', 'Main');
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	17/07/2010
	 *
	 * @uses Loader::page
	 * @uses PageController::url
	 * @uses Html::tag
	 *
	 * @param $label (string) - Rótulo do link.
	 * @param $page (string|object) - Nome da página ou objeto que a define.
	 * @param $params_or_attrs (array) - Parâmetros a serem passados para a página ou atributos do link.
	 * @param $attrs (array) - Array contendo conjuntos atributo => valor a serem inseridos no link.
	 *
	 * @return String contendo o link formatado ou Array com os links, caso a URL seja uma array.
	 */
	public function link($label, $page, $params_or_attrs = true, $attrs = null) {
		if (is_array($params_or_attrs) && !is_numeric(key($params_or_attrs))) {
			$attrs = $params_or_attrs;
			$params_or_attrs = true;
		}

		if ($page = $this->load->page($page)) {
			$url = $page->url($params_or_attrs);
		}

		if ($page && $url) {
			if (is_array($url)) {
				$link = array();

				foreach ($url as $uri) {
					$attrs['href'] = $uri;
					$link[] = $this->tag('a', $label, $attrs);
				}

				return $link;
			}
			else {
				$attrs['href'] = $url;
			}
		}
		else {
			$attrs['href'] = '#';
		}

		$link = $this->tag('a', $label, $attrs);

		return $link;
	}

	/**
	 * Método coringa para nome reservado 'list', o qual renderiza uma lista HTML 'ul' ou 'ol'.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	17/07/2010
	 *
	 * @uses Html::tag
	 *
	 * @param $items (array) - Items a serem inseridos na lista.
	 * @param $list (string) - Tipo de lista, 'ul' ou 'ol'.
	 * @param $list_attrs (array) - Array contendo conjuntos atributo => valor a serem inseridos na lista (ul/ol).
	 * @param $item_attrs (array) - Array contendo conjuntos atributo => valor a serem inseridos nos items (li).
	 *
	 * @return String contendo a lista formatada.
	 */
	public function __call($name, $args) {
		if (strtolower($name) != 'list' || !is_array($args[0])) {
			return false;
		}

		$items = $args[0];
		$list = isset($args[1])? $args[1] : 'ul';
		$list_attrs = isset($args[2])? $args[2] : null;
		$item_attrs = isset($args[3])? $args[3] : null;

		$lis = array();

		foreach ($items as $item) {
			$lis[] = $this->tag('li', $item, $item_attrs);
		}

		$lis = implode('', $lis);
		$list = $this->tag($list, $lis, $list_attrs);

		return $list;
	}

	/**
	 * Insere uma tag wrapper sobre o conteúdo.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	17/07/2010
	 *
	 * @uses Html::tag
	 *
	 * @param $content (string) - Conteúdo a ser manipulado.
	 * @param $tag (string) - Tag a ser inserida como wrapper.
	 * @param $attrs (array) - Array contendo conjuntos atributo => valor a serem inseridos no link.
	 *
	 * @return String com a tag inserida como wrapper do conteúdo.
	 */
	public function wrap($content, $tag, $attrs = null) {
		return $this->tag($tag, $content, $attrs);
	}

	/**
	 * Remove o wrapper do conteúdo passado.
	 * Nota: este método não verifica se o conteúdo possui um elemento root, ou seja, se começa e
	 * termina com a mesma tag.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 *
	 * @param $content (string) - Conteúdo a ser manipulado.
	 *
	 * @return String com a tag wrapper removida do conteúdo.
	 */
	public function unwrap($content, $remove_xml = false) {
		if (preg_match('/<\?xml[^>]*>/', $content, $parts)) {
			$content = preg_replace('/<\?xml[^>]*>/', '', $content);
		}

		$content = preg_replace('@^(\s*)<[^>]+>(.*)</[^>]+>(\s*)$@s', '$1$2$3', $content);
		$content = isset($parts[0]) && !$remove_xml? $parts[0].$content : $content;

		return $content;
	}

	/**
	 * Adiciona um atributo em uma string html
	 */
	public function setAttribute($attr, $value, $content) {
		$attr = $this->formatAttributes(array($attr => $value));
		$content = preg_replace('/^(\s*<[a-z\d_:-]+)/i', "$1$attr", $content);

		return $content;
	}

	/**
	 * Captura um atributo de uma string html
	 */
	public function getAttribute($attr, $content) {
		if (preg_match("/^\s*<[a-z\d_:-]+[^>]*$attr=[\"\']([^>]+?)[\"\']/i", $content, $match)) {
			return $match[1];
		}

		return false;
	}

	/**
	 * Remove um atributo de uma string html
	 */
	public function removeAttribute($attr, $content) {
		$content = preg_replace("/^(\s*<[a-z\d_:-]+[^>]*) $attr=[\"\'][^>]+?[\"\']/i", '$1', $content);

		return $content;
	}

	/**
	 * Formata os atributos de modo que possam ser adicionado a uma tag HTML.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/07/2010
	 *
	 * @uses Html::parseTag
	 *
	 * @param $attrs (array) - Atributos a serem formatados.
	 *
	 * @return String com a sequência de atributos.
	 */
	public function formatAttributes($attrs) {
		$attributes = '';

		if (!is_array($attrs)) {
			$tag = $attrs;
			$this->parseTag($tag, $attrs);
		}

		foreach ($attrs as $attr => $value) {
			$q = strpos('"', $value) === false? '"' : "'";
			$attributes .= " $attr=$q$value$q";
		}

		$attrs = $attributes;

		return $attributes;
	}

	/**
	 * Analisa o nome da linguagem e versão e converte nos valores corretos.
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	11/07/2010
	 *
	 * @param $language (string) - Nome da linguagem a ser analisado.
	 */
	private function parseLanguage(&$language) {
		if (is_null($language)) {
			$language = $this->language;
		}
		else {
			$language = strtolower($language);

			if (!isset($this->doctypes[$language]))
				$language = $this->language;
		}

		$this->language = $language;

		return $language;
	}

	/**
	 * Analisa a tag passada e identifica seus parâmetros.
	 *
	 * @since 				Neleus 0.1
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	11/07/2010
	 *
	 * @param $tag (string) - Tag a ser analisada.
	 * @param $attrs (array) - Variável que armazenará os atributos identificados.
	 *
	 * @return String com o nome da tag.
	 */
	private function parseTag(&$tag, &$attrs) {
		if (preg_match('/^([^\[]+)\[(.*)$/', $tag, $parts)) {
			$tag = $parts[1];
			$attributes = str_replace(']', '', $parts[2]);
			$attributes = explode('[', $attributes);
		}
		elseif (preg_match('/^<([^> ]+)([^>]*)>/', $tag, $parts)) {
			$tag = $parts[1];
			$attributes = $parts[2];
			$attributes = $attributes? explode(' ', trim($attributes)) : null;
		}

		if (!is_array($attrs)) {
			settype($attrs, 'array');
		}

		if (isset($attributes)) {
			foreach ($attributes as $attr) {
				$attr = explode('=', $attr);
				$attrs[$attr[0]] = preg_replace('/^[\'"](.*)[\'"]$/', '$1', $attr[1]);
			}
		}

		return $tag;
	}

}
endif;
?>