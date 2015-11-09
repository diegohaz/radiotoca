<?php
if (!parse_class('Database')):
/**
 * Database Class
 *
 * Classe responsável pelo banco de dados.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.5
 * @version 			0.4.5
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 */

class Database {

	private $config;

	private $connections = array();
	private $connection;
	private $driver;

	public $lastQuery;

	/**
	 * Inicia o objeto Database.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	25/07/2010
	 */
	public function __construct() {
		$this->config = import('Config', true);
	}

	/**
	 * Destrói a database.
	 *
	 * @since 				Neleus 0.2.9
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 */
	public function __destruct() {
		if ($this->connections) {
			$this->disconnect();
		}
	}

	/**
	 * Cria uma conexão com o banco de dados.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.7
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @param $profile (string) - Nome do perfil da conexão.
	 *
	 * @return O recurso mysql da conexão ou FALSE em caso de erro.
	 */
	public function connect($profile = null) {
		if (!isset($profile)) {
			if (isset($this->config->db['default_profile'])) {
				$profile = $this->config->db['default_profile'];
			}
			else return false;
		}

		$db = $this->config->db[$profile];
		$driver = $this->config->db[$profile]['dbdriver'] = strtolower($db['dbdriver']);

		// Profile is not configured
		if (!isset($this->config->db[$profile])) {
			return false;
		}
		// Profile is already on connection
		elseif (isset($this->connections[$profile])) {
			$connection = $this->connections[$profile];
		}
		// Profile isn't connected and has 'mysqli' as driver
		elseif ($driver == 'mysqli') {
			$connection = mysqli_connect($db['hostname'], $db['username'], $db['password'], $db['database']);

			if (mysqli_connect_errno()) {
				if ($this->config['display_errors']) {
					throw new Exception(mysqli_connect_error());
				}

				return false;
			}
		}
		// Profile has 'mysql' as driver
		elseif ($driver == 'mysql') {
			$connection = mysql_connect($db['hostname'], $db['username'], $db['password']);

			if (!$connection) {
				if ($this->config['display_errors']) {
					throw new Exception(mysql_error());
				}

				return false;
			}
			else {
				$select_db = mysql_select_db($db['database'], $connection);

				if (!$select_db) {
					if ($this->config['display_errors']) {
						throw new Exception(mysql_error());
					}

					return false;
				}
			}
		}
		else {
			if ($this->config['display_errors']) {
				throw new Exception('Could not connect the database');
			}

			return false;
		}

		$this->connections[$profile] = $connection;
		$this->connection = $connection;
		$this->driver = $driver;

		return $connection;
	}

	/**
	 * Define uma conexão como a conexão atual do banco de dados.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	15/09/2010
	 *
	 * @param $profile (string) - Nome do perfil da conexão.
	 *
	 * @return O objeto Database ou FALSE em caso de erro.
	 */
	public function setConnection($profile) {
		if (isset($this->connections[$profile]) || $this->connect($profile)) {
			$this->connection = $this->connections[$profile];
			$this->driver = $this->config->db[$profile]['dbdriver'];

			return $this;
		}
		else return false;
	}

	/**
	 * Reseta as conexões atuais, retornando para a padrão.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	15/09/2010
	 */
	public function flush() {
		if ($this->connections) {
			if (count($this->connections) == 1) {
				$this->connection = current($this->connections);
				$this->driver = $this->config->db[key($this->connections)]['dbdriver'];
			}
			else {
				$default_profile = $this->config->db['default_profile'];

				if (isset($this->connections[$default_profile])) {
					$this->connection = $this->connections[$default_profile];
					$this->driver = $this->config->db[$default_profile]['dbdriver'];
				}
			}
		}

		return $this;
	}

	/**
	 * Fecha e remove a conexão ao banco de dados.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.5
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Database::__call
	 *
	 * @param $profile (string) - Perfil a ser conectado.
	 */
	public function disconnect($profile = null) {
		if (isset($profile)) {
			if (isset($this->connections[$profile])) {
				$this->close($this->connections[$profile]);

				unset($this->connections[$profile]);
			}
			else return false;
		}
		else { # Disconnect all
			foreach ($this->connections as $profile => $connection) {
				$this->disconnect($profile);
			}

			$this->connection = null;
			$this->driver = null;
		}
	}

	/**
	 * Realiza uma requisição no banco de dados.
	 *
	 * @since 				Neleus 0.2.8
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Database::__call
	 * @uses DatabaseResult::__construct
	 *
	 * @param $query (string) - Requisição.
	 *
	 * @return Objeto DatabaseResult caso este retorne um recurso ou valor BOOLEAN, caso o mesmo o retorne desta forma.
	 */
	public function query($query) {
		$this->lastQuery = $query;

		if (!$this->connection) {
			if ($this->connect()) {
				if ($this->config['display_errors']) {
					trigger_error('Query was sent, but there was no connection activated. Default connection was setup!', E_USER_NOTICE);
				}
			}
			else return false;
		}

		$result = $this->__call('query', array($query));

		if ($this->error && $this->config['display_errors']) {
			throw new Exception($this->error);
		}

		if (is_bool($result)) {
			return $result;
		}
		else return new DatabaseResult($result, $this->driver);
	}

	/**
	 * Escapa os caracteres especiais da string.
	 *
	 * @since 				Neleus 0.2.8
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 *
	 * @uses Database::__call
	 *
	 * @param $string (string) - String a ser tratada.
	 *
	 * @return String com os caracteres devidamente tratados.
	 */
	public function escape($string) {
		return $this->real_escape_string($string);
	}

	/**
	 * Repassa os acessos a propriedades inexistentes para o recurso mysql da conexão atual.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 *
	 * @uses Database::__call
	 *
	 * @param $property (string) - Propriedade requisitada.
	 *
	 * @return Retorno da função cujo nome é sufixado por $property.
	 */
	public function __get($property) {
		return $this->$property();
	}

	/**
	 * Executa uma função da database.
	 *
	 * @since 				Neleus 0.2.9
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 *
	 * @param $function (string) - Nome da função chamada.
	 * @param $args (array) - Argumentos passados para a função.
	 *
	 * @return Retorno da função {$driver}_{$function} chamada.
	 */
	private function __call($function, $args) {
		if ($this->connection) {
			if ($this->driver == 'mysql' && !is_resource(end($args)))
				$args[] = $this->connection;

			elseif ($this->driver == 'mysqli'  && !is_object(reset($args)))
				array_unshift($args, $this->connection);
		}

		$func = $this->driver.'_'.$function;

		return call_user_func_array($func, $args);
	}

}
endif;


if (!parse_class('DatabaseResult')):
/**
 * DatabaseResult Class
 *
 * Classe responsável pelos resultados do banco de dados.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.9
 * @version 			0.1.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	23/10/2010
 */

class DatabaseResult {

	private $result;
	private $driver;

	/**
	 * Inicia o objeto DatabaseResult.
	 *
	 * @since 				Neleus 0.2.9
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 *
	 * @param $result (resource|object) - Objeto ou recurso Mysql result.
	 * @param $driver (string) - Driver do banco de dados.
	 */
	public function __construct($result, $driver) {
		$this->result = $result;
		$this->driver = $driver;
	}

	/**
	 * Repassa os acessos a propriedades inexistentes para o recurso mysql result da
	 * conexão atual.
	 *
	 * @since 				Neleus 0.2.9
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses DatabaseResult::__call
	 *
	 * @param $property (string) - Propriedade requisitada.
	 *
	 * @return Retorno da função cujo nome é sufixado por $property.
	 */
	public function __get($property) {
		$this->$property = $this->$property();

		return $this->$property;
	}

	/**
	 * Executa uma função de resultado.
	 *
	 * @since 				Neleus 0.2.9
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 *
	 * @param $function (string) - Nome da função chamada.
	 * @param $args (array) - Argumentos passados para a função.
	 *
	 * @return Retorno da função {$driver}_{$function} chamada.
	 */
	public function __call($function, $args) {
		array_unshift($args, $this->result);
		$func = $this->driver.'_'.$function;

		return call_user_func_array($func, $args);
	}

}
endif;
?>