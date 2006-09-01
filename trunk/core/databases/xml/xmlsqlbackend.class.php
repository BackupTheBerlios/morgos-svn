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
	
	function SQLActionQuery ($numRowsDeleted) {
		$this->_numRows = $numRowsDeleted;
	}
	
}

class SQLSelectQuery extends SQLQuery {
	var $_from;
	var $_fieldNames;
	var $_rows;
	
	function SQLSelectQuery ($from, $fieldNames, $rows) {
		reset ($rows);
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

class SQLFunction {
	var $_result;
	var $_function;
	var $_parameters;
	var $_alias;
	
	function SQLFunction ($function, $parameters, $alias) {
		$this->_alias = $alias;
		$this->_function = $function;
		$this->_parameters = $parameters;
		$this->_result = NULL;
	}
	
	function setResult ($result) {
		$this->_result = $result;
	}
	
	
	function getFunction () {return $this->_function;}
	function getAlias () {return $this->_alias;}
	function getResult () {return $this->_result;}
	
}

class SQLCondition {
	var $_fieldName;
	var $_operator;
	var $_value2;
	
	function SQLCondition ($fieldName, $operator, $value2) {
		$this->_value2 = $value2;
		$this->_operator = $operator;
		$this->_fieldName = $fieldName;
	}
	
	function getFieldName () {return $this->_fieldName;}
	
	function isTrue ($value1) {
		switch ($this->_operator) {
			case '=':
				return $value1 == $this->_value2;
				break;
		}
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
		foreach ($conditions as $condition) {
			$fieldName = $condition->getFieldName ();
			var_dump ($fieldName);
			if (! $condition->isTrue ($this->_values[$fieldName])) {
				return false;
			}
		}
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
	var $_uniqueKey;
	var $_primaryKey;
	
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
	
	function selectRows ($fieldNames, $functions, $conditions, $order, $limit) {
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
		
				
		foreach ($functions as &$func) {
			switch ($func->getFunction ()) {
				case 'COUNT':
					$func->setResult (count ($rows));
					break; 
				default:
					return "ERROR_XMLSQL_PARSE_ERRROR";
			}
		}
		
		foreach ($rows as &$row) {
			foreach ($functions as $func) {
				$row[$func->getAlias ()] = $func->getResult ();
			}
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
	
	function getNumRows () {return count ($this->_rows);}
	
	function getName () {return $this->_name;}
	
	function setUniqueKey ($fields) {
		$this->_uniqueKey = $fields;
	}
	
	function setPrimaryKey ($fields) {
		$this->_primaryKey = $fields;
	}
	
	function getNewID () {
		return $this->getNumRows ()+1;
	}
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
			case 'DROP':
				$query = $this->parseDrop ($nextSequence);
				break;
			case 'CREATE':
				$query = $this->parseCreate ($nextSequence);
				break;
			case 'INSERT':
				$query = $this->parseInsert ($nextSequence);
				break;
			case 'SHOW':
				$query = $this->parseShow ($nextSequence);
				break;
			default:
				return "ERROR_XMLSQLBACKEND_PARSE_ERROR";
		}
		return $query;
	}
	
	function parseSelect ($sqlSequence) {
		$from = $this->getDataAfterKeyword ($sqlSequence, 'FROM');
		$fieldData = trim ($this->findDataUntilNextKeyword ($sqlSequence));
		$where = $this->parseWhere ($sqlSequence);
		$order = "";
		$limit = $this->getDataAfterKeyword ($sqlSequence, 'LIMIT');
		return $this->_XMLBackend->select ($from, $fieldData, $where, $order, $limit);
	}
	

	function parseFrom ($sqlSequence) {
		return $this->getDataAfterKeyword ($sqlSequence, 'FROM');
	}
	
	function parseDrop ($sqlSequence) {
		$tableName = $this->getDataAfterKeyword ($sqlSequence, 'TABLE');
		return $this->_XMLBackend->dropTable ($tableName);
	}
	
	function parseCreate ($sqlSequence) {
		$tableName= $this->getDataAfterKeyword ($sqlSequence, 'TABLE');
		$fieldsData = $this->splitData (',', $this->parseBetweenTwoChars ('(', ')', $sqlSequence));
		$table = new table ($tableName);
		foreach ($fieldsData as $fieldData) {
			$fieldData = trim ($fieldData);		
			$fieldDatas = $this->splitData (' ', $fieldData);
			if ($fieldDatas[0] == 'PRIMARY') {
				$data = implode (' ', array_slice ($fieldDatas, 1));
				$fields = $this->parseBetweenTwoChars ('(', ')', $this->getDataAfterKeyword ('KEY', $data));
				$table->setPrimaryKey ($fields);
			} elseif ($fieldDatas[0] == 'UNIQUE') {
				$data = implode (' ', array_slice ($fieldDatas, 1));
				$fields = $this->parseBetweenTwoChars ('(', ')', $this->getDataAfterKeyword ('KEY', $data));
				$table->setUniqueKey ($fields);
			} else {
				$fieldName = $fieldDatas[0];
				$fieldType = $fieldDatas[1];
				$fieldExtras = array_slice ($fieldDatas, 2);
				$field = new field ($fieldName, $fieldType, $fieldExtras);
				$table->addField ($field);
			}
		}
		$this->_XMLBackend->addTable ($table);
		return new SQLActionQuery (0);
	}
	
	function parseInsert ($sqlSequence) {
		$tableName = $this->getDataAfterKeyword ($sqlSequence, 'INTO');
		$orderOfFields = explode (',', substr ($sqlSequence, strpos ($sqlSequence, '(')+1, strpos ($sqlSequence, ')')-strpos ($sqlSequence, '(')-1));
		$valuesString = $this->getAllDataAfterKeyword ($sqlSequence, 'VALUES');
		$valuesString = trim ($valuesString);
		$valuesString[0] = ' ';
		$valuesString[strlen ($valuesString)-1] = ' ';
		$valuesString = trim ($valuesString);
		$valuesFields = $this->splitData (',', $valuesString);
		
		if (count ($orderOfFields) != count ($valuesFields)) {
			return "ERROR_XMLSQL_PARSE_ERROR";
		}
		$table = $this->_XMLBackend->getTable ($tableName);
		$tableFields = $table->getFields ();
		foreach ($tableFields as $field) {
			if ($key = array_search ($field->getName (), $orderOfFields)) {
				//$key = key ($field->getName (), $orderOfFields);
				$value = $valuesFields[$key];
			} else {
				$value = NULL;
			}
			$values[$field->getName ()] = $value;
		}
		$row = new row ($tableFields, $values, $table->getNewID ());
		$table->addRow ($row);
		return new SQLActionQuery (1);
	}
	
	function parseShow () {
		return new SQLQuery ();
	}
	
	function parseWhere ($sqlSequence) {
		var_dump ($sqlSequence);
		
		$data = trim ($this->findDataUntilNextKeyword ($this->getAllDataAfterKeyword ($sqlSequence, 'WHERE')));
		$allConditions = array ();
		if (strlen ($data) !== 0) {
			$fieldName = substr ($data, 0, strpos ($data, '='));
			$value = substr ($data, strpos ($data, '='));
			$condition = new SQLCondition ($fieldName, '=', $value);
			var_dump ($data);
			echo '<br />';
			$allConditions[] = $condition;
		}
		return $allConditions;
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
	
	function getAllDataAfterKeyword ($sqlSequence, $keyword) {
		$afterKeyword = substr ($sqlSequence, stripos ($sqlSequence, " $keyword ")+strlen($keyword)+2);
		$afterKeyword = trim ($afterKeyword);
		$data = substr ($afterKeyword, 0);
		return $data;
	}
	
	function findDataUntilNextKeyword ($sqlSequence) {
		$found = false;
		$inString = false;
		$allKeywords = array ('INSERT', 'INTO', 'VALUES', 'SELECT', 'FROM', 'ORDER', 'BY', 'WHERE', 'OR', 'AND');
		$latestWord = '';
		$data = '';
		$i = 0;
		while ($i < strlen ($sqlSequence)) {
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
		return $data;
	}
	
	function splitData ($splitter, $data) {
		$data = trim ($data);
		$inString = false;
		$inFunction = false;
		$allDatas = array ();
		$lastData = '';
		for ($i = 0; $i < strlen ($data); $i++) {
			$char = $data[$i];
			if (($char == $splitter) and ($inFunction == false) and ($inString == false)) {
				$allDatas[] = $lastData; 
				$lastData = '';
				continue;
			}
			
			if (($char == '(') and ($inString == false)) {
				$inFunction = true;
				$lastData .= $char;
				continue;
			} elseif (($char == ')') and ($inString == false)) {
				$inFunction = false;
				$lastData .= $char;
				continue;
			}
			
			if (($char == '\'')) {
				if ($inString == true) {
					$inString == false;
				} else {
					$inString == true;
				}
				$lastData .= $char;
				continue;
			}
			$lastData .= $char;
		}
		$allDatas[] = $lastData;
		return $allDatas;
	}
	
	function parseBetweenTwoChars ($firstChar, $lastChar, $data) {
		$firstOccurance = stripos ($data, $firstChar);
		$lastOccurance = strrpos ($data, $lastChar);
		return substr ($data, $firstOccurance+1, $lastOccurance - $firstOccurance-1);
	}
}
?>