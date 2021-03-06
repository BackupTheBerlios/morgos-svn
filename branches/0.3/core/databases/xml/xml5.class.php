<?php
class XMLBackend {
	private $_tables;
	private $_xml;
	private $_saveFile;
	
	function __construct () {
		$this->_tables = array ();
		$this->_xml = null;
	}

	public function load ($database, $user, $pass) {
		$this->_xml = new DOMDocument ();
		$this->_xml->load ($database.'.xml');
		$this->_saveFile = $database.'.xml';
		$allTablesList = $this->_xml->getElementsByTagName ('table');
		for ($i = 0; $i<$allTablesList->length; $i++) {
			$tableNode = $allTablesList->item ($i);
			$this->addTable ($this->parseTable ($tableNode));
		}
		$this->_xml = null;
	}
	
	public function save () {
		$this->_xml = new DOMDocument ();
		$rootElement = $this->_xml->createElement ('database');
		
		foreach ($this->_tables as $table) {
			$tEl = $this->_xml->createElement ('table');
			$tEl->setAttribute ('name', $table->getName ());
			$tEl->setAttribute ('newID', $table->getNewInternalID ());
			
			foreach ($table->getFields () as $fields) {
				$fEl = $this->_xml->createElement ('field');
				$fEl->setAttribute ('type', $table->getType ());
				$fVal = $this->_xml->createTextNode ($table->getName ());
				$fEl->appendChild ($fVal);
				$tEl->appendChild ($fEl);
			}
			
			foreach ($table->getRows () as $row) {
			}
			
			$rootElement->appendChild ($tEl);
		}
		$this->_xml->appendChild ($rootElement);
		die ('save');
		$this->_xml->save ($this->_saveFile);
	}

	public function addTable (table $table) {
		if (! $this->existsTable ($table->getName ())) {
			$this->_tables[$table->getName ()] = $table;
		} else {
		}
	}
	
	public function dropTable ($tableName) {
		if ($this->existsTable ($tableName)) {
			$q = new SQLActionQuery ($this->_tables[$tableName]->getNumRows ());
			unset ($this->_tables[$tableName]);
			return $q;
		} else {
			return "ERROR_XMLSQL_TABLE_NOT_FOUND $tableName";
		}
	}
	
	public function select ($from, $fields, $where, $order,  $startlimit, $limitlength) {
		if ($this->existsTable ($from)) {
			$table = $this->_tables[$from];
			if ($fields == '*') {
				$fields = $table->getAllFieldNames ();
				$functions = array ();
			} else {
				$fields = explode (',', $fields);
				foreach ($fields as $key => $field) {
					$field = trim ($field);
					$fields[$key] = $field;
				}
				$functions = array ();
				foreach ($fields as $k => &$field) {
					$field = trim ($field);
					if (strpos ($field, '(') !== false) {
						$function = substr ($field, 0, strpos ($field, '('));
						$parameters = substr ($field, strpos ($field, '(') + 1, strpos ($field, ')') - strpos ($field, '(') - 1);
						unset ($fields[$k]);
						$func = new SQLFunction ($function, $parameters, $field); 
						$functions[] = $func;
					}	
				}
			}
			$rows = $table->selectRows ($fields, $functions, $where, $order, $startlimit, $limitlength);
			return new SQLSelectQuery ($from, $fields, $rows);
		} else {
			return "ERROR_XMLSQL_TABLE_NOT_FOUND $from";
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
		
		$utags = $tableNode->getElementsByTagName ('unique');
		$ftag = $utags->item (0);
		$uniqueKeys = $ftag->nodeValue; //only one unique key supported
		$table->addUniqueKeys (explode (',', $uniqueKeys));
		
		$utags = $tableNode->getElementsByTagName ('primary');
		$ftag = $utags->item (0);
		$pKey = $ftag->nodeValue;
		$table->setPrimaryAutocountKey ($pKey);
		
		$allRowsList = $tableNode->getElementsByTagName ('row');
		for ($i = 0; $i<$allRowsList->length; $i++) {
			$rowNode = $allRowsList->item ($i);
			$table->addRow ($this->parseRow ($rowNode, $table->getFields (), $i));
		}

		$table->setNewID ($tableNode->getAttribute ('newID')); 

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
	
	function getTable ($tableName) {
		return $this->_tables[$tableName];
	}
	
}
?>