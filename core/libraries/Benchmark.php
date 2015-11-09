<?php
if (!parse_class('Benchmark')):
/**
 * Benchmark Class
 *
 * Classe responsável pela análise de desempenho da aplicação.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1.4
 * @version 			0.3
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

class Benchmark {

	public $marker = array();

	/**
	 * Marca um ou mais pontos para análise.
	 *
	 * @since 				Neleus 0.1.4
	 * @version 			0.3
	 * @author 				CodeIgniter <http://codeigniter.org>
	 * @modifiedby 		Diego Haz <http://diegohaz.com>
	 * @lastmodified	07/08/2010
	 */
	public function mark() {
		$marks = func_get_args();

		foreach ($marks as $mark) {
			if (is_array($mark)) {
				$point = key($mark);
				$time = current($mark);
			}
			elseif (is_string($mark)) {
				$point = $mark;
				$time = microtime(true);
			}
			else continue;

			$this->marker[$point] = $time;
		}
	}

	/**
	 * Retorna o resultado da análise.
	 *
	 * @since 				Neleus 0.1.4
	 * @version 			0.2
	 * @author 				CodeIgniter <http://codeigniter.org>
	 * @modifiedby 		Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/05/2010
	 *
	 * @param $point1 (string) - Nome do marcador inicial.
	 * @param $point2 (string) - Nome do marcador final.
	 * @param $decimals (integer) - Quantidade de decimais a ser mostrada no output.
	 *
	 * @return Float com o resultado da análise ou FALSE, em caso de erro.
	 */
	public function get($point1 = null, $point2 = null, $decimals = 4) {
		if (!$point1) {
			end($this->marker);
			$point1 = key($this->marker);
			reset($this->marker);
		}

		if (!isset($this->marker[$point1])) {
			return false;
		}

		if (!isset($this->marker[$point2])) {
			$this->marker[$point2] = microtime(true);
		}

		return number_format($this->marker[$point2] - $this->marker[$point1], $decimals);
	}
}
endif;
?>