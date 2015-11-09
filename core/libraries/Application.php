<?php
if (file_exists($app['path'].LIBRARIES.'Application.php')) {
	include $app['path'].LIBRARIES.'Application.php';
}

if (!class_exists('Application')):
/**
 * Application Class
 *
 * Classe da aplicação.
 *
 * @package				core.libraries
 * @since 				Neleus 0.1.3
 * @version 			0.5.5
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 */

class Application {

	public $name;
	public $path;

	public $imported = array();

	public $models = array();
	public $controllers = array();
	public $pages = array();
	public $errors = array();
	public $data = array();
	public $modules = array();

	public static $apps = array();

	/**
	 * Inicia a aplicação.
	 *
	 * @since 				Neleus 0.1.3
	 * @version 			0.8
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @param $path (string) - Caminho da aplicação.
	 */
	public function __construct($path) {
		$this->path = $path;
		$this->name = basename($path);
	}

	/**
	 * Executa a aplicação.
	 *
	 * @since 				Neleus 0.2.2
	 * @version 			1.6
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses basics::import
	 * @uses Loader::__construct
	 * @uses Loader::config
	 * @uses Loader::library
	 * @uses Benchmark::mark
	 * @uses Loader::controller
	 * @uses Loader::pages
	 * @uses Benchmark::get
	 */
	public function run() {
		if (self::$apps && isset(self::$apps[$this->name])) {
			return false;
		}
		else {
			self::$apps[$this->name] = $this;
		}

		// Get the loader
		import('Loader');
		$this->load = new Loader($this);

		// Load configuration
		$this->load->config();

		// Load stuff
		$this->load->library('Library');
		$this->load->library('Config', 'config');
		$this->load->library('Database', 'db');
		$this->load->library('Router', 'router');

		// Set errors behavior
		if ($this->config['display_errors']) {
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
		}
		else {
			ini_set('display_errors', 0);
			error_reporting(0);
		}

		// Start session
		if ($this->config['auto_session'] && !isset($_SESSION)) {
			@session_start();
		}

		// Auto connect database
		if ($this->config->db['auto_connect']) {
			foreach ($this->config->db['auto_connect'] as $connection) {
				$this->db->connect($connection);
			}

			$this->db->flush();
		}

		// Load MVC structure
		$this->load->library('Controller');
		$this->load->library('PageController');
		$this->load->library('Model');
		$this->load->library('View');

		// Autoload
		foreach ($this->config->autoload as $type => $autoloads) {
			foreach ($autoloads as $autoload) {
				$this->load->$type($autoload);
			}
		}

		if (count(self::$apps) == 1) {
			// Request page
			$this->router->request();

			// Get benchmark
			if ($this->config['display_benchmark']) {
				global $app_start_time;

				$this->load->library('Benchmark', true);
				$this->Benchmark->mark(array('app_init' => $app_start_time), 'app_end');

				echo '<br /><hr /><p>Application loaded in '.$this->Benchmark->get('app_init', 'app_end').' seconds</p>';
			}
		}

		// Disconnect database, if there is some active connection
		$this->db->disconnect();
	}

}
endif;
?>