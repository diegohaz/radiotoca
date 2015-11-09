<?php
if (!parse_class('DataController')):
/**
 * DataController Class
 *
 * Classe responsável pela manipulação de dados da aplicação.
 *
 * @package				core.libraries
 * @since 				Neleus 0.2.5
 * @version 			0.5.1
 * @author 				Diego Haz <http://diegohaz.com>
 * @lastmodified	24/10/2010
 */

class DataController extends Controller implements ArrayAccess {

	public $type = 'data';

	public $table;
	public $key;

	public $activeResult;
	public $currentRecord;
	public $activeRecord = array();

	private $originalActiveRecord = array();

	/**
	 * Inicia o controlador de dados.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses Controller::__construct
	 * @uses Loader::library
	 * @uses Inflector::tableize
	 *
	 * @param $table (string) - Tabela que será anexada ao controller.
	 * @param $name (string) - Nome do controller.
	 */
	public function __construct($name = null, $table = null) {
		parent::__construct();

		if (isset($name)) {
			$this->name = $name;
		}

		if (isset($table)) {
			$this->table = $table;
		}
		elseif (!isset($this->table)) {
			$this->load->library('Inflector', 'inflector');
			$this->table = $this->inflector->tableize($this->name);
		}

	}

	/**
	 * Cria um novo registro.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses DataController::parseArgs
	 * @uses Database::query
	 *
	 * @return TRUE caso a requisição seja realizada com sucesso ou FALSE caso contrário.
	 */
	public function create() {
		$values = func_get_args();

		if (!isset($values[0])) {
			return false;
		}
		elseif (is_array($values[0])) {
			$values = $values[0];
		}

		$args = array('values' => $values);
		$this->parseArgs($args);

		$query = "INSERT INTO `$this->table` ";
		$query .= isset($args['fields'])? '('.implode(', ', $args['fields']).') VALUES ' : 'VALUES ';
		$query .= '('.implode(', ', $args['values']).')';
		$result = $this->db->query($query);

		return $result;
	}

	/**
	 * Salva os dados anexados ao objeto em um novo registro.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	28/07/2010
	 *
	 * @uses DataController::create
	 *
	 * @return TRUE caso a requisição seja realizada com sucesso ou FALSE caso contrário.
	 */
	public function save() {
		$result = $this->create($this->activeRecord);

		return $result;
	}

	/**
	 * Deleta o registro atual ou o registro especificado pelos parâmetros.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.2
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses DataController::parseArgs
	 * @uses Database::query
	 *
	 * @param $where (array|string|integer) - Valor correspondente ao comando SQL WHERE.
	 * @param $limit (string|integer) - Valor correspondente ao comando SQL LIMIT.
	 *
	 * @return TRUE caso a requisição seja realizada com sucesso ou FALSE caso contrário.
	 */
	public function delete($where = null, $limit = null) {
		if (!isset($where)) {
			if ($this->activeRecord) {
				$where = array(key($this->activeRecord) => current($this->activeRecord));
				$this->activeRecord = null;
			}
			else return false;
		}

		$args = array('where' => $where, 'limit' => $limit);
		$this->parseArgs($args);

		$query = "DELETE FROM `$this->table` $args[where] $args[limit]";
		$result = $this->db->query($query);

		return $result;
	}

	/**
	 * Procura por determinado(s) registro(s). Ao contrário do método select, o find do comando
	 * WHERE (parâmetro $search) e seleciona, automaticamente, todos os campos.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.5
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses DataController::parseArgs
	 * @uses Database::query
	 * @uses DataController::setCurrent
	 *
	 * @param $search (array|string|integer) - Valor correspondente ao comando SQL WHERE.
	 * @param $orderby (string) - Valor correspondente ao comando SQL ORDER BY.
	 * @param $limit (string|integer) - Valor correspondente ao comando SQL LIMIT.
	 * @param $extra (string) - Comandos extras a serem inseridos na query.
	 *
	 * @return O resultado DatabaseResult da requisição em caso de sucesso ou FALSE caso contrário.
	 */
	public function find($search, $orderby = null, $limit = null, $extra = null) {
		$args = array('where' => $search, 'orderby' => $orderby, 'limit' => $limit, 'extra' => $extra);
		$this->parseArgs($args);

		// Generates the query and gets the result
		$query = "SELECT * FROM `$this->table` $args[where] $args[orderby] $args[limit] $args[extra]";
		$result = $this->db->query($query);

		if ($result) {
			// Register the first row on active record
			$this->flush();
			$this->activeResult = $result;
			$this->currentRecord = 0;
			$this->setCurrent();

			if ($result->num_rows) {
				return $result;
			}
			else return false;
		}

		return $result;
	}

	/**
	 * Seleciona determinado(s) campo(s) de determinado(s) registro(s).
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses DataController::parseArgs
	 * @uses Database::query
	 * @uses DataController::flush
	 * @uses DataController::setCurrent
	 *
	 * @param $fields (array|string) - Valor correspondente ao comando SQL SELECT.
	 * @param $where (array|string|integer) - Valor correspondente ao comando SQL WHERE.
	 * @param $orderby (string) - Valor correspondente ao comando SQL ORDER BY.
	 * @param $limit (string|integer) - Valor correspondente ao comando SQL LIMIT.
	 * @param $extra (string) - Comandos extras a serem inseridos na query.
	 *
	 * @return O resultado DatabaseResult da requisição em caso de sucesso ou FALSE caso contrário.
	 */
	public function select($fields, $where = null, $orderby = null, $limit = null, $extra = null) {
		$args = array('fields' => $fields, 'where' => $where, 'orderby' => $orderby, 'limit' => $limit, 'extra' => $extra);
		$this->parseArgs($args);

		$query = 'SELECT '.implode(', ', $args['fields'])." FROM `$this->table` $args[where] $args[orderby] $args[limit] $args[extra]";
		$result = $this->db->query($query);

		if ($result) {
			// Register the first row on active record
			$this->flush();
			$this->activeResult = $result;
			$this->currentRecord = 0;
			$this->setCurrent();
		}

		return $result;
	}

	/**
	 * Atualiza determinado(s) registro(s).
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses DataController::parseArgs
	 * @uses Database::query
	 *
	 * @param $values (array) - Valor correspondente ao comando SQL SET.
	 * @param $where (array|string) - Valor correspondente ao comando SQL WHERE.
	 * @param $limit (string|integer) - Valor correspondente ao comando SQL LIMIT.
	 * @param $extra (string) - Comandos extras a serem inseridos na query.
	 *
	 * @return TRUE caso a requisição seja realizada com sucesso ou FALSE caso contrário.
	 */
	public function update($values = null, $where = null, $limit = null, $extra = null) {
		if (!isset($values)) {
			if ($this->activeRecord) {
				$values = $this->activeRecord;
				$where = array(key($this->activeRecord) => current($this->activeRecord));

				// Remove values with no modifications
				foreach ($values as $field => $value) {
					if (isset($this->originalActiveRecord[$field]) && $this->originalActiveRecord[$field] == $value) {
						unset($values[$field]);
					}
				}
			}
			else return false;
		}
		else {
			if (!isset($where)) {
				if ($this->activeRecord) {
					$where = array(key($this->activeRecord) => current($this->activeRecord));
				}
				else return false;
			}

			// Update also the active record, if $values was sent
			if ($this->activeRecord && isset($this->activeRecord[key($where)])
			&& $this->activeRecord[key($where)] == current($where)) {
				foreach ($values as $field => $value) {
					$this->activeRecord[$field] = $value;
				}
			}
		}

		$args = array('values' => $values, 'where' => $where, 'limit' => $limit, 'extra' => $extra);
		$this->parseArgs($args);

		$values = array();

		foreach ($args['values'] as $field => $value) {
			$values[] = $field.' = '.$value;
		}

		$query = "UPDATE `$this->table` SET ".implode(', ', $values)." $args[where] $args[limit] $args[extra]";
		$result = $this->db->query($query);

		return $result;
	}

	/**
	 * Inicia um looping pelos registros encontrados, movendo o ponteiro a cada execução.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses DataController::setCurrent
	 *
	 * @return Array no formato 'campo' => 'valor' do registro atual ou NULL caso não exista.
	 */
	public function fetch() {
		if ($this->currentRecord < $this->activeResult->num_rows) {
			if ($this->currentRecord) {
				$this->setCurrent();
			}

			$this->currentRecord++;

			return $this->activeRecord;
		}
		else return null;
	}

	/**
	 * Retorna um array com o resultado da última requisição.
	 *
	 * @since 				Neleus 0.2.6
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	08/08/2010
	 *
	 * @uses DataController::fetch
	 *
	 * @return Array no formato 'campo' => 'valor' para cada registro ou NULL caso não exista.
	 */
	public function getResult() {
		$i = 0;

		while ($current = $this->fetch()) {
			foreach ($current as $field => $value) {
				$result[$i][$field] = $value;
			}

			$i++;
		}

		if (isset($result)) {
			return $result;
		}
		else return null;
	}

	/**
	 * Faz a limpa nos dados armazenados no objeto.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.3
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	19/09/2010
	 */
	public function flush() {
		if ($this->activeResult) {
			$this->activeResult->free_result();
			$this->activeResult = null;
		}

		$this->currentRecord = null;
		$this->activeRecord = array();

		return $this;
	}

	/**
	 * Define o registro atual.
	 *
	 * @since 				Neleus 0.3
	 * @version 			0.1
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @uses DatabaseResult::__call
	 */
	protected function setCurrent() {
		$this->originalActiveRecord = $this->activeRecord = $this->activeResult->fetch_assoc();
	}

	/**
	 * Analisa os argumentos passados e os converte de forma concisa nos argumentos esperados.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.7
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/10/2010
	 *
	 * @param $args (array) - Lista de argumentos passados a serem analisados.
	 *
	 * @return Array com os argumentos convertidos.
	 */
	private function parseArgs(&$args) {
		$default_args = array(
			'fields' => null,
			'values' => null,
			'where' => null,
			'orderby' => null,
			'limit' => null,
			'extra' => null
		);

		// Replace default args with passed args
		$args = array_merge($default_args, $args);

		// Parse fields
		if (isset($args['fields'])) {
			if (!is_array($args['fields'])) {
				$args['fields'] = explode(',', $args['fields']);
				$args['fields'] = array_map('trim', $args['fields']);

				foreach ($args['fields'] as $i => $field) {
					if ($field == '*') continue;

					$args['fields'][$i] = "`$field`";
				}
			}
		}

		// Parse values
		if (isset($args['values'])) {
			$values = $args['values'];
			$args['values'] = array();

			if (is_numeric(key($values))) {
				foreach ($values as $value) {
					$args['values'][] = is_null($value)? 'NULL' : $this->parseValue($value);
				}
			}
			else {
				$args['fields'] = array();

				foreach ($values as $field => $value) {
					$args['fields'][] = "`$field`";
					$args['values']["`$field`"] = is_null($value)? 'NULL' : $this->parseValue($value);
				}
			}
		}

		// Parse command WHERE
		if (isset($args['where'])) {
			if (!is_array($args['where'])) {
				$args['where'] = array($this->getKey() => $args['where']);
			}

			$where = 'WHERE ';

			foreach ($args['where'] as $field => $value) {
				// $value maybe 'AND' or 'OR' in array('foo' => 'bar', 'OR', 'bar' => 'foo')
				if ((!$is_array = is_array($value)) && is_numeric($field)) {
					$where .= " $value ";
					continue;
				}
				// $value complements the 'BETWEEN' keyword, for example: array('id BETWEEN' => array(1, 5))
				elseif ($is_array && is_numeric(key($value))) {
					$value = array_map(array($this, 'parseValue'), $value);
					$value = implode(' AND ', $value); # Result: WHERE `id` BETWEEN '1' AND '5'
				}
				// It was passed like array('foo' => 'bar', array('bar' => 'foo', OR, 'foo >' => 'bar'))
				// Result between (): WHERE `foo` = 'bar' AND (`bar` = 'foo' OR `foo` > `bar`)
				elseif ($is_array) {
					$_args['where'] = $value;

					$this->parseArgs($_args);
					$where .= '('.preg_replace('/^WHERE /', '', $_args['where']).')';

					continue;
				}
				else {
					$value = $this->parseValue($value);
				}

				$symbol = '=';

				// In this case, argument maybe passed as array('field LIKE' => '%value%')
				if (preg_match('/ (LIKE|BETWEEN|>|<|>=|<=|<>|\!=)$/i', $field, $match)) {
					$field = preg_replace("/ $match[1]$/", '', $field);
					$symbol = $match[1];
				}

				$where .= "`$field` $symbol $value";
			}

			$where = preg_replace('/([\)\'])([\(`])/', '$1 AND $2', $where);
			$args['where'] = $where;
		}

		// Parse command ORDER BY
		if (isset($args['orderby'])) {
			// If it doesn't appear to be a valid ORDER BY command, throw it into command LIMIT
			if (is_numeric($args['orderby']) || preg_match('/^(\d\\s*,\s*\d)$/', trim($args['orderby']))) {
				$args['limit'] = $args['orderby'];
				$args['orderby'] = null;
			}
			else {
				$order = null;

				if (preg_match('/( DESC| ASC)$/i', $args['orderby'], $match)) {
					$order = $match[1];
					$args['orderby'] = preg_replace("/$order$/", '', $args['orderby']);
				}

				// $order can be ' DESC' or ' ASC'
				$args['orderby'] = "ORDER BY `$args[orderby]`$order";
			}
		}

		// Parse command LIMIT
		if (isset($args['limit'])) {
			$args['limit'] = "LIMIT $args[limit]";
		}

		return $args;
	}

	/**
	 * Captura a chave da tabela, que servirá para consultas onde o campo for omitido.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.5
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	24/10/2010
	 *
	 * @uses Database::query
	 *
	 * @return String com o nome da chave.
	 */
	private function getKey() {
		if (isset($this->key)) {
			return $this->key;
		}

		$query = $this->db->query("SELECT * FROM `$this->table` LIMIT 1");

		if ($query) {
			while ($field = $query->fetch_field()) {
				if (isset($field->flags) && ($field->flags & MYSQLI_PRI_KEY_FLAG)) {
					$this->key = $field->name;
					break;
				}
				elseif (isset($field->primary_key) && $field->primary_key) {
					$this->key = $field->name;
					break;
				}
			}
		}
		else {
			trigger_error($this->db->error, E_USER_WARNING);
		}

		return $this->key;
	}

	/**
	 * Analisa o valor para determinar se o mesmo terá aspas ou não.
	 *
	 * @since 				Neleus 0.2.5
	 * @version 			0.4
	 * @author 				Diego Haz <http://diegohaz.com>
	 * @lastmodified	23/10/2010
	 *
	 * @param $value (string) - Valor a ser analisado.
	 *
	 * @return String com o valor convertido.
	 */
	private function parseValue($value) {
		$value = $this->db->escape($value);
		$value = "'$value'";

		return $value;
	}

	public function offsetSet($offset, $value) { $this->activeRecord[$offset] = $value; }
	public function offsetExists($offset) { return isset($this->activeRecord[$offset]); }
	public function offsetUnset($offset) { unset($this->activeRecord[$offset]); }
	public function offsetGet($offset) { return isset($this->activeRecord[$offset])? $this->activeRecord[$offset] : null; }

}
endif;
?>