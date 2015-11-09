<?php
if (!class_exists('Config')):
/**
 * Config Class
 *
 * Classe responsável por configurar a aplicação em tempo de execução.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1.2
 * @version 			0.3
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	21/08/2010
 */

class Config implements ArrayAccess {

	public function offsetSet($offset, $value) { $this->app[$offset] = $value; }
	public function offsetExists($offset) { return isset($this->app[$offset]); }
	public function offsetUnset($offset) { unset($this->app[$offset]); }
	public function offsetGet($offset) { return isset($this->app[$offset])? $this->app[$offset] : null; }

}
endif;
?>