<?php
class XMLBackend {
	private $_tables;
	private $_xml;
	
	function __construct () {
		$this->_tables = array ();
		$this->_xml = new DOMDocument ();
	}

	public function load ($database, $user, $pass) {
		$this->_xml->load ($database.'.xml');
		$allTablesList = $this->_xml->getElementsByTagName ('table');
		for ($i = 0; $i<$allTablesList->length; $i++) {
			//var_dump ($i);
			$tableNode = $allTablesList->item ($i);
			$this->addTable ($this->parseTable ($tableNode));
		}
	}

	public function addTable (table $table) {
		if (! $this->existsTable ($table->getName ())) {
			$this->_tables[$table->getName ()] = $table;
		} else {
		}
	}
	
	public function dropTable ($table) {
	}
	
	public function select ($from, $fields, $where, $order, $limit) {
		if ($this->existsTable ($from)) {
			$table = $this->_tables[$from];
			if ($fields == '*') {
				$fields = $table->getAllFieldNames ();
			} else {
				$fields = explode (',', $fields);
				foreach ($fields as &$field) {
					$field = trim ($field);
				}
			}
			return new SQLSelectQuery ($from, $fields, $table->selectRows ($fields, $where, $order, $limit));
		} else {
			return "ERROR_TABLE_NOT_FOUND $from";
		}
	}
	
	public function existsTable ($tableName) {
		return array_key_exists ($tableName, $this->_tables);
	}
	
	private function parseTable (DOMNode $tableNode) {
		$table = new table ($tableNode->getAttribute ('name'));
		$allFieldsList = $tableNode->getElementsByTagName ('field');
		for ($i = 0; $i<$allFieldsList->length; $i++) {
			$fieldNode = $allFieldsList->item ($i);
			$table->addField ($this->parseField ($fieldNode));
		}
		
		$allRowsList = $tableNode->getElementsByTagName ('row');
		for ($i = 0; $i<$allRowsList->length; $i++) {
			$rowNode = $allRowsList->item ($i);
			$table->addRow ($this->parseRow ($rowNode, $table->getFields (), $i));
		}
		return $table;
	}
	
	private function parseField (DOMNode $fieldNode) {
		return new field ($fieldNode->nodeValue, $fieldNode->getAttribute ('type'));
	}
	
	private function parseRow (DOMNode $rowNode, $allFields, $ID) {
		$allValuesList = $rowNode->getElementsByTagName ('value');
		$values = array ();
		for ($i = 0; $i<$allValuesList->length; $i++) {
			$node = $allValuesList->item ($i);
			$values[$node->getAttribute ('field')] = $node->nodeValue;
		}
		return new row ($allFields, $values, $ID);
	}
}
?>