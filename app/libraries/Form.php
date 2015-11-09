<?php
/**
 * Manipula elementos de formulário
 */
class Form extends Library {

	/**
	 * Captura apropriadamente um campo de um modelo
	 */
	public function getField($model_field, $original_model = null) {
		if (!isset($this->inflector)) {
			$this->load->library('Inflector', 'inflector');
		}

		$field = new stdClass;

		if (isset($original_model) && isset($model_field['ref'])) {
			$ref = $original_model->xpath("//*[@name='$model_field[ref]']", true);
			$elements = $ref->attributes()->toArray() + $ref->children()->toArray();

			// Define as propriedades existentes no elemento original
			foreach ($elements as $attr => $value) {
				$field->$attr = $value->toString();
			}
		}

		// Define as propriedades existentes no elemento de substituição
		$elements = $model_field->attributes()->toArray() + $model_field->children()->toArray();

		foreach ($elements as $attr => $value) {
			if (!$value->toString() && isset($field->$attr) && $field->$attr)
				unset($field->$attr);
			else
				$field->$attr = $value->toString();
		}

		// Define propriedades específicas
		$field->tag = ($tag = $model_field->getName()) != 'field'? $tag : $ref->getName();
		$field->type = isset($field->type)? $field->type : 'text';
		$field->id = isset($field->id)? $field->id : $this->inflector->id($field->name);
		$field->value = isset($field->value)? $field->value : null;
		$field->class = array();

		if (isset($field->required)) {
			$field->class[] = $field->required;
		}

		if (isset($model_field->info)) {
			$field->info = new stdClass;
			$field->info->class = isset($model_field->info['nojs'])? ' '.$model_field->info['nojs']->toString() : '';
			$field->info->msg = $model_field->info->toString();
		}

		if ($field->type == 'checkbox') {
			$field->checked = '';
		}

		// Define os modificadores
		$modifiers = array();

		if (isset($field->register)) $modifiers[] =& $field->register;
		if (isset($field->display)) $modifiers[] =& $field->display;

		foreach ($modifiers as &$modifier) {
			if (strpos($modifier, ';'))
				$modifier = explode(';', $modifier, 2);
			else
				$modifier = array($field->mask, $modifier);
		}

		return $field;
	}

	/**
	 * Captura vários campos de um modelo
	 */
	public function getFields($model_fields, $original_model = null) {
		$fields = array();

		foreach ($model_fields as $field) {
			$field = $this->getField($field, $original_model);
			$fields[$field->name] = $field;
		}

		return $fields;
	}

	/**
	 * Valida campos retornados por Form::getField()
	 */
	public function validate($fields) {
		$has_error = false;

		// Faz um looping pelos campos realizando a verificação
		foreach ($fields as $field) {
			$empty = $does_not_match = $invalid = null;

			if (($empty = (isset($field->required) && (empty($field->value) || (isset($field->label) && $field->value == $field->label))))
			OR ($does_not_match = (isset($field->matches) && $field->value != $fields[$field->matches]->value))
			OR ($invalid = (!empty($field->value) && isset($field->mask) && !preg_match($field->mask, $field->value)))) {
				$field->class[] = 'error';
				$has_error = true;

				if ($empty)
					$field->error = isset($field->empty)? $field->empty : null;
				elseif ($does_not_match)
					$field->error = isset($field->doesNotMatch)? $field->doesNotMatch : null;
				elseif ($invalid)
					$field->error = isset($field->invalid)? $field->invalid : null;
			}
		}

		return !$has_error;
	}

}
?>