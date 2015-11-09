<?php
/**
 * Content Functions
 *
 * Funções de conteúdo.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1.1
 * @version 			0.3.5
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/10/2010
 */

if (!function_exists('excerpt')):
/**
 * Retorna um resumo de um texto.
 *
 * @since 				Neleus 0.2.4
 * @version 			0.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/07/2010
 *
 * @param $text (string) - Texto a ser resumido.
 * @param $limit (integer) - Quantidade de caracteres que o resumo deverá conter.
 * @param $etc (string) - Sequência de caracteres pelos quais o resumo será sucedido.
 *
 * @return String com o resumo.
 */
function excerpt($text, $limit = 160, $etc = '...') {
	$text = preg_replace('/[\r\n]/', '', $text);
	$text = strip_tags($text);
	$excerpt = wordwrap($text, $limit);
	$excerpt = explode("\n", $excerpt);

	return $excerpt[0].$etc;
}
endif;

if (!function_exists('is_prefix')):
/**
 * Verifica se determinado valor possui o prefixo passado.
 *
 * @usage
 * if (is_prefix('day', 'daybase')) echo "DAYBASE has prefix DAY";
 *
 * @since 				Neleus 0.1.1
 * @version 			0.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/05/2010
 *
 * @param $prefix (string) - Prefixo a ser buscado.
 * @param $value (string) - Valor a ser analisado.
 * @param $ignore_case (boolean) - Determina se a verificação irá diferenciar maiúsculas e minúsculas.
 *
 * @return TRUE se o prefixo for encontrado no valor ou FALSE, caso contrário.
 */
function is_prefix($prefix, $value, $ignore_case = false) {
	if ($ignore_case) {
		$prefix = strtolower($prefix);
		$value = strtolower($value);
	}

	if (strpos($value, $prefix) !== 0)
		return false;
	else
		return true;
}
endif;

if (!function_exists('is_sufix')):
/**
 * Verifica se determinado valor possui o sufixo passado.
 *
 * @usage
 * if (is_sufix('BASE', 'daybase', true)) echo "DAYBASE has sufix BASE";
 *
 * @since 				Neleus 0.1.1
 * @version 			0.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/05/2010
 *
 * @param $sufix (string) - Sufixo a ser buscado.
 * @param $value (string) - Valor a ser analisado.
 * @param $ignore_case (boolean) - Determina se a verificação irá diferenciar maiúsculas e minúsculas.
 *
 * @return TRUE se o sufixo for encontrado no valor ou FALSE, caso contrário.
 */
function is_sufix($sufix, $value, $ignore_case = false) {
	if ($ignore_case) {
		$sufix = strtolower($sufix);
		$value = strtolower($value);
	}

	if ($sufix != substr($value, '-'.strlen($sufix)))
		return false;
	else
		return true;
}
endif;

if (!function_exists('search_afix')):
/**
 * Analisa uma série de valores, buscando prefixos e sufixos nos mesmos.
 *
 * @usage
 * search_afix(array('prefix' => 'a prefix', 'another', 'etc'), $values, true);
 *
 * @since 				Neleus 0.1.2
 * @version 			0.4
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/10/2010
 *
 * @uses content::is_prefix
 * @uses content::is_sufix
 *
 * @param $search (array) - Busca a ser feita.
 * @param $values (array) - Valores a serem analisados.
 * @param $ignore_case (boolean) - Determina se a verificação irá diferenciar maiúsculas e minúsculas.
 *
 * @return Array com os valores encontrados.
 */
function search_afix($search, $values, $ignore_case = false) {
	$results = array();

	if (!is_array($search)) {
		return $results;
	}
	elseif (count($search) == 1 && !function_exists('is_'.key($search))) {
		return $results;
	}
	elseif (count($search) == 1 && is_array(current($search))) {
		$multiple_search = current($search);
	}
	elseif (count($search) > 1) {
		$multiple_search = $search;
	}

	if (isset($multiple_search)) {
		foreach ($multiple_search as $type => $single_search) {
			if (count($search) == 1)
				$single_search = array(key($search) => $single_search);
			else
				$single_search = array($type => $single_search);

			$new_results = search_afix($single_search, $values, $ignore_case);
			$results = array_merge($results, $new_results);
		}

		$results = array_flip(array_flip($results));

		return $results;
	}

	if (!is_array($values)) settype($values, 'array');
	$is = 'is_'.key($search);

	foreach ($values as $value) {
		if ($is(current($search), $value, $ignore_case))
			$results[] = $value;
	}

	return $results;
}
endif;

if (!function_exists('search_prefix')):
/**
 * Analisa uma série de valores, verificando se dado prefixo consta em algum deles.
 *
 * @usage
 * search_prefix('this', array('This is it!', 'That is it!'), true);
 * search_prefix(array('this', 'that'), array('This is it!', 'That is it!'), true);
 *
 * @since 				Neleus 0.1.2
 * @version 			0.3
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 *
 * @uses content::search_afix
 *
 * @param $prefix (string|array) - Prefixo a ser buscado.
 * @param $values (array) - Valores a serem analisados.
 * @param $ignore_case (boolean) - Determina se a verificação irá diferenciar maiúsculas e minúsculas.
 *
 * @return Array com os valores encontrados com o prefixo passado.
 */
function search_prefix($prefix, $values, $ignore_case = false) {
	return search_afix(array('prefix' => $prefix), $values, $ignore_case);
}
endif;

if (!function_exists('search_sufix')):
/**
 * Analisa uma série de valores, verificando se dado sufixo consta em algum deles.
 *
 * @usage
 * search_sufix('this?', array('What's this?', 'What's that?'));
 * search_sufix(array('this?', 'that?'), array('What's this?', 'What's that?'));
 *
 * @since 				Neleus 0.1.2
 * @version 			0.2
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 *
 * @uses content::search_afix
 *
 * @param $prefix (string|array) - Sufixo a ser buscado.
 * @param $values (array) - Valores a serem analisados.
 * @param $ignore_case (boolean) - Determina se a verificação irá diferenciar maiúsculas e minúsculas.
 *
 * @return Array com os valores encontrados com o sufixo passado.
 */
function search_sufix($sufix, $values, $ignore_case = false) {
	return search_afix(array('sufix' => $sufix), $values, $ignore_case);
}
endif;

if (!function_exists('seems_utf8')):
/**
 * Analisa uma string e verifica se ela está no formato utf-8.
 *
 * @since 				Neleus 0.1.1
 * @version 			0.1
 * @author 				Wordpress <http://wordpress.org>
 * @modifiedby 		Diego Haz <http://diegohaz.com>
 * @lastmodified	23/05/2010
 *
 * @param $str (string) - String a ser analisada.
 *
 * @return TRUE se for utf-8 ou FALSE, caso contrário.
 */
function seems_utf8($str) {
	$str = (string) $str;
	$length = strlen($str);

	for ($i = 0; $i < $length; $i++) {
		$c = ord($str[$i]);

		if ($c < 0x80) $n = 0; # 0bbbbbbb
		elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model

		for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
				return false;
		}
	}

	return true;
}
endif;

if (!function_exists('remove_accents')):
/**
 * Analisa uma string e converte os caracteres acentuados em equivalentes sem acentuação.
 *
 * @since 				Neleus 0.1.1
 * @version 			0.2
 * @author 				Wordpress <http://wordpress.org>
 * @modifiedby 		Diego Haz <http://diegohaz.com>
 * @lastmodified	26/07/2010
 *
 * @uses content::seems_utf8
 *
 * @param $string (string) - String a ser analisada e convertida.
 *
 * @return String com as devidas conversões feitas.
 */
function remove_accents($string) {
	if (!preg_match('/[\x80-\xff]/', $string))
		return $string;

	if (seems_utf8($string)) {
		$chars = array(
		// Decompositions for Latin-1 Supplement
		chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
		chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
		chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
		chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
		chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
		chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
		chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
		chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
		chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
		chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
		chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
		chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
		chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
		chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
		chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
		chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
		chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
		chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
		chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
		chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
		chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
		chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
		chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
		chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
		chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
		chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
		chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
		chr(195).chr(191) => 'y',
		// Decompositions for Latin Extended-A
		chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
		chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
		chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
		chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
		chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
		chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
		chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
		chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
		chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
		chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
		chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
		chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
		chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
		chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
		chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
		chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
		chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
		chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
		chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
		chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
		chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
		chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
		chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
		chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
		chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
		chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
		chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
		chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
		chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
		chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
		chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
		chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
		chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
		chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
		chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
		chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
		chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
		chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
		chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
		chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
		chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
		chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
		chr(197).chr(148) => 'R', chr(197).chr(149) => 'r',
		chr(197).chr(150) => 'R', chr(197).chr(151) => 'r',
		chr(197).chr(152) => 'R', chr(197).chr(153) => 'r',
		chr(197).chr(154) => 'S', chr(197).chr(155) => 's',
		chr(197).chr(156) => 'S', chr(197).chr(157) => 's',
		chr(197).chr(158) => 'S', chr(197).chr(159) => 's',
		chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
		chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
		chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
		chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
		chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
		chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
		chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
		chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
		chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
		chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
		chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
		chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
		chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
		chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
		chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
		chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
		// Euro Sign
		chr(226).chr(130).chr(172) => 'E',
		// GBP (Pound) Sign
		chr(194).chr(163) => '');

		$string = strtr($string, $chars);
	} else {
		// Assume ISO-8859-1 if not UTF-8
		$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158).chr(159)
									.chr(162).chr(165).chr(181).chr(192).chr(193).chr(194).chr(195)
									.chr(196).chr(197).chr(199).chr(200).chr(201).chr(202).chr(203)
									.chr(204).chr(205).chr(206).chr(207).chr(209).chr(210).chr(211)
									.chr(212).chr(213).chr(214).chr(216).chr(217).chr(218).chr(219)
									.chr(220).chr(221).chr(224).chr(225).chr(226).chr(227).chr(228)
									.chr(229).chr(231).chr(232).chr(233).chr(234).chr(235).chr(236)
									.chr(237).chr(238).chr(239).chr(241).chr(242).chr(243).chr(244)
									.chr(245).chr(246).chr(248).chr(249).chr(250).chr(251).chr(252)
									.chr(253).chr(255);

		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

		$string = strtr($string, $chars['in'], $chars['out']);
		$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
		$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
		$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	}

	return $string;
}
endif;

?>