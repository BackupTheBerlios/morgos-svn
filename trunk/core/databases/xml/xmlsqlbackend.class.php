<?php

if (version_compare (PHP_VERSION,'5','>=')) {
	include_once ('core/databases/xml/xml5.class.php');
}

class SQLQuery {
	var $_numRows;
	
	function numRows () {
		return $this->_numRows;
	}
	
}

class SQLActionQuery extends SQLQuery {
}

class SQLSelectQuery extends SQLQuery {
	var $_from;
	var $_fieldNames;
	var $_rows;
	
	function SQLSelectQuery ($from, $fieldNames, $rows) {
		$this->_from = $fieldNames;
		$this->_fieldNames = $fieldNames;
		$this->_rows = $rows;
	}
	
	function fetchArray () {
		$a = current ($this->_rows);
		next ($this->_rows);
		return $a;
	}
	
	function numRows () {
		return count ($this->_rows);
	}
}

class row {
	var $_fields;
	var $_ID;
	
	function row (&$fields, $values, $ID) {
		$this->_values = $values;
		$this->_fields = $fields;
		$this->_ID = $ID;
	}
	
	function areConditionsTrue ($conditions) {
		return true;
	}
	
	function getID () {return $this->_ID;}
	function getValue ($fieldName) {return $this->_values[$fieldName];}
}

class field {
	var $_name;
	var $_type;
	var $_extras;

	function field ($name, $type, $extras = null) {
		$this->_name = $name;
		$this->_type = $type;
		$this->_extras = $extras;
	} 
	
	function getDefaultValue () {return null;}
	function getName () {return $this->_name;}
}

class table {
	var $_name;
	var $_fields;
	var $_rows;
	var $_data;
	
	function table ($name) {
		$this->_name = $name;
		$this->_fields = array ();
		$this->_rows = array ();
		$this->_data = array ();
	}
	
	function addField ($field) {
		if (! $this->existsField ($field->getName ())) {
			$this->_fields[$field->getName ()] = $field;
			$this->_data[$field->getName ()] = array ();
			foreach ($this->getRows () as  $key=>$r) {
				$this->_data[$field->getName ()][$key] = $field->getDefaultValue ();
			}
		} else {
		}
	}
	
	function addRow ($row) {
		if (! $this->existsRow ($row->getID ())) {
			$this->_rows[$row->getID ()] = $row;
			foreach ($this->getAllFieldNames () as $fieldName) {
				$this->_data[$fieldName][$row->getID ()] = $row->getValue ($fieldName);
			}
		} else {
		}
	}
	
	function getRows () {return $this->_rows;}
	function getFields () {return $this->_fields;}
	
	function selectRows ($fieldNames, $conditions, $order, $limit) {
		$rows = array ();
		foreach ($this->getRows () as $row) {
			if ($row->areConditionsTrue ($conditions)) {
				$rowToAdd = array ();
				foreach ($fieldNames as $fieldName) {
					$rowToAdd[$fieldName] = $this->_data[$fieldName][$row->getID ()];
				}
				$rows[] = $rowToAdd;
			}
		}
		
		//order rows
		if (($limit != 0) and (count ($rows) > $limit )) {
			$rows = array_slice ($rows, 0, $limit);
		}
		
		return $rows;
	}
	
	function getAllFieldNames () {
		$res = array ();
		foreach ($this->getFields () as $field) {
			$res[] = $field->getName ();
		}
		return $res;
	}

	function existsField ($fieldName) {
		return array_key_exists ($fieldName, $this->_fields);
	}
	
	function existsRow ($rowID) {
		return array_key_exists ($rowID, $this->_rows);
	}
	
	function getName () {return $this->_name;}
}

class XMLSQLBackend {
	var $_XMLBackend;
	
	function XMLSQLBackend () {
		$this->_XMLBackend = new XMLBackend ();
	}
	
	function load ($database, $user, $password) {
		$this->_XMLBackend->load ($database, $user, $password);
	}

	function parseCommand ($sqlCommand) {
		$sqlCommand = trim ($sqlCommand);
		$command = substr ($sqlCommand, 0, $this->firstSpace ($sqlCommand)); // split the $sql after first space
		$nextSequence = substr ($sqlCommand, $this->firstSpace ($sqlCommand));
		switch ($command) {
			case 'SELECT':
				$query = $this->parseSelect ($nextSequence);
				break;
			default:
				return "ERROR_XMLSQLBACKEND_PARSE_ERROR";
		}
		return $query;
	}
	
	function parseSelect ($sqlSequence) {
		$from = $this->getDataAfterKeyword ($sqlSequence, 'FROM');
		$fieldData = trim ($this->findDataUntilNextKeyword ($sqlSequence));
		$where = "";
		$order = "";
		$limit = $this->getDataAfterKeyword ($sqlSequence, 'LIMIT');
		return $this->_XMLBackend->select ($from, $fieldData, $where, $order, $limit);
	}
	

	function parseFrom ($sqlSequence) {
		$afterFrom = substr ($sqlSequence, stripos ($sqlSequence, ' FROM ')+6);
		$afterFrom = trim ($afterFrom);
		if ($this->firstSpace ($afterFrom)) {
			$fromTable = substr ($afterFrom, 0, $this->firstSpace ($afterFrom));
		} else {
			$fromTable = substr ($afterFrom, 0);
		} 
		return $fromTable;
	}
	
	function firstSpace ($string) {
		return stripos ($string, ' ');
	}
	
	function getDataAfterKeyword ($sqlSequence, $keyword) {
		$afterKeyword = substr ($sqlSequence, stripos ($sqlSequence, " $keyword ")+strlen($keyword)+2);
		$afterKeyword = trim ($afterKeyword);
		if ($this->firstSpace ($afterKeyword)) {
			$data = substr ($afterKeyword, 0, $this->firstSpace ($afterKeyword));
		} else {
			$data = substr ($afterKeyword, 0);
		}
		return $data;
	}
	
	function findDataUntilNextKeyword ($sqlSequence) {
		$found = false;
		$inString = false;
		$allKeywords = array ('INSERT', 'SELECT', 'FROM', 'ORDER', 'BY', 'WHERE', 'OR', 'AND');
		$latestWord = '';
		$data = '';
		$i = 0;
		while ($i <= strlen ($sqlSequence)) {
			if ($i != 0) {
				if ($sqlSequence[$i-1] == ' ') {
					$data .= $latestWord;
					$latestWord = '';
				}
			}
			
			if ($sqlSequence[$i] == "'"){
				if ($i != 0) {
					if ($sqlSequence[$i-1] !== '\\') { 
						if ($inString) {
							$inString = false;
						} else {
							$inString = true;
						}
					} else {
						$latestWord .= $sqlSequence[$i];
					}
				} else {
					$latestWord .= $sqlSequence[$i];
				}
			} else {
				$latestWord .= $sqlSequence[$i];
			}
			if ((in_array ($latestWord, $allKeywords)) and ($inString == false)) {
				return $data;
			}
			$i++;
		}
		var_dump ('E?D');
		return $data;
	}
}
?>