<?php
if (!class_exists('Library')):
/**
 * Library Class
 *
 * Classe que representa uma biblioteca.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.7
 * @version 			0.1.3
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/10/2010
 */

class Library {

	public $name;
	public $load;

	protected $libraries = array();

	/**
	 * Inicia a biblioteca.
	 *
	 * @since 				Neleus 0.2.7
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/10/2010
	 *
	 * @uses basics::import
	 * @uses Loader::__construct
	 * @uses Loader::library
	 */
	public function __construct() {
		$libraries = array(
			'Config' => 'config',
			'Database' => 'db',
			'Application' => 'app'
		);
		$this->libraries = array_merge($this->libraries, $libraries);

		$class = get_class($this);

		// Get the loader
		import('Loader');
		$this->load = new Loader($this);

		// Get libraries
		foreach ($this->libraries as $library => $instantiate) {
			if (is_string($instantiate) && !is_string($library)) {
				$library = $instantiate;
				$instantiate = false;
			}

			if ($class != $library) {
				$this->load->library($library, $instantiate);
			}
		}

		// Set name, don't include prefix
		if ($this->config['prefix']) {
			$this->name = preg_replace('/^'.$this->config['prefix'].'/', '', $class);
		}
		else {
			$this->name = $class;
		}
	}

}
endif;
?>