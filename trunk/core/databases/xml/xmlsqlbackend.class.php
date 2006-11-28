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
	
	function SQLActionQuery ($numRowsAffected) {
		$this->_numRows = $numRowsAffected;
	}
	
}

class SQLInsertQuery extends SQLActionQuery {
	var $_ID;
	
	function SQLInsertQuery ($ID) {
		parent::SQLActionQuery (1);
		$this->_ID = $ID;
	}
	
	function getID () {return $this->_ID;}
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
		$this->_fields = $fields;
		print_r ($fields);
		$this->_ID = $ID;
		foreach ($values as $k=>$v) {
			$this->setValue ($k, $v);
		}
	}
	
	function areConditionsTrue ($conditions) {
		$curCheck = true;
		$prevCond = 'AND';
		foreach ($conditions as $condition) {
			if (is_string ($condition)) {
				$prevCond = $condition;
			} else {
				$condition = cloneob ($condition);
				$fieldName = $condition->getFieldName ();
				$val = trim ($condition->_value2);
				if ($val[0] == '\'') {
					$val = substr ($val, 1, strlen ($val)-2);
				} else {
					$val = $this->_values[$val];
				}
				$condition->_value2 = $val; //TODO: fix hack
				$this->_values[$fieldName];
				if ($prevCond == 'AND') {
					if ($condition->isTrue ($this->_values[$fieldName])) {
						// no change
					} else {
						$curCheck = false;
					}
					
				} else {
					if ($condition->isTrue ($this->_values[$fieldName])) {
						$curCheck = true;
					} else {
						// no change
					}
				}
			}
		}
		return $curCheck;
	}
	
	function getID () {return $this->_ID;}
	function getValue ($fieldName) {
		if (array_key_exists ($fieldName, $this->_values)) {
			return $this->_values[$fieldName];
		} else {
			print_r ($this->_values);
		}
	}
	function setValue ($fieldName, $v) {
		if (substr ($this->_fields[$fieldName]->getType (), 0, 3) == 'int') {
			$v = (int) $v;
		}
		$this->_values[$fieldName] = $v;
	}
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
	function getType () {return $this->_type;}
}

class table {
	var $_name;
	var $_fields;
	var $_rows;
	var $_data;
	var $_uniqueKey;
	var $_autocountKey;
	
	var $_newID;
	
	function table ($name) {
		$this->_name = $name;
		$this->_fields = array ();
		$this->_rows = array ();
		$this->_data = array ();
		$this->_uniqueKey = array ();
		$this->_autocountKey = '';
		$this->_newID = 1;
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
		var_dump ($field);
	}
	
	function addRow ($row) {
		if (! $this->existsRow ($row->getID ())) {
			if ($this->_uniqueKey != array ()) {
				$allConds = array ();
				foreach ($this->_uniqueKey as $unique) {
					if (count ($allConds) != 0) {
						$allConds[] = 'AND';
					}
					$allConds[] = new SQLCondition ($unique, '=', '\''.$row->getValue ($unique).'\'');
				}
				
				$allRows = $this->selectRows (array (), array (), $allConds, null, 0, 0);
				if (count ($allRows) > 0) {
					return new Error ('XMLSQL_INSERT_ERROR', 'Duplicate entry for unique key');
				}
			}
			if (! empty ($this->_autocountKey)) {
				$a = $row->getValue ($this->_autocountKey);
				if (empty ($a)) {
					$row->setValue ($this->_autocountKey, $this->_newID); 
					$this->_newID++;
				}
			}

			$this->_rows[$row->getID ()] = $row;
			foreach ($this->getAllFieldNames () as $fieldName) {
				$this->_data[$fieldName][$row->getID ()] = $row->getValue ($fieldName);
			}
			return $row->getValue ($this->_autocountKey);
		} else {
		}
	}
	
	function getRows () {return $this->_rows;}
	function getFields () {return $this->_fields;}
	
	function selectRows ($fieldNames, $functions, $conditions, $order,  $startlimit, $limitlength) {
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
		
		if ($limitlength !== 0) {
			if (count ($rows) > $startlimit + $limitlength) {
				$rows = array_slice ($rows, $startlimit, $limitlength);
			} elseif (count ($rows) > $startlimit) {
				$rows = array_slice ($rows, $startlimit);
			} else {
				return (array ());
			} 
		}
		
				
		foreach ($functions as $key=>$func) {
			switch ($func->getFunction ()) {
				case 'COUNT':
					$func->setResult (count ($rows));
					break; 
				default:
					return "ERROR_XMLSQL_PARSE_ERRROR";
			}
			$functions[$key] = $func;
		}
		
		foreach ($rows as $key=>$row) {
			foreach ($functions as $func) {
				$row[$func->getAlias ()] = $func->getResult ();
			}
			$rows[$key] = $row;
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
	
	function setPrimaryAutocountKey ($fields) {
		$this->_autocountKey = $fields;
	}
	
	function getNewInternalID () {
		return $this->getNumRows ()+1;
	}
	
	function setNewID ($ID) {
		$this->_newID = $ID;
	}
	
	function addUniqueKeys ($keys) {
		$this->_uniqueKey = $keys;
	} 
}

class XMLSQLBackend {
	var $_XMLBackend;
	var $_HostDir;
	var $_User;
	var $_Password;
	var $_latestQurey;
	
	function XMLSQLBackend () {
		$this->_XMLBackend = new XMLBackend ();
	}
	
	function connect ($dir, $user, $password) {
		$this->_HostDir = $dir;
		$this->_User = $user;
		$this->_Password = $password;
	}
	
	function disconnect () {
		$this->_XMLBackend->save ();
	}
	
	function load ($databaseName) {
		$this->_XMLBackend->load ($this->_HostDir.'/'.$databaseName, $this->_User, $this->_Password);
	}
	
	function latestInsertID () {
		if (get_class ($this->_latestQuery) == 'SQLInsertQuery') {
			return $this->_latestQuery->getID ();
		} else {
			return new Error ('No ID');
		}
	}

	function parseCommand ($sqlCommand) {
		//die ($sqlCommand);
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
		$this->_latestQuery = $query;
		//var_dump ($sqlCommand);
		return $query;
	}
	
	function parseSelect ($sqlSequence) {
		$from = $this->getDataAfterKeyword ($sqlSequence, 'FROM');
		$fieldData = trim ($this->findDataUntilNextKeyword ($sqlSequence));
		$where = $this->parseWhere ($sqlSequence);
		$order = "";
		list ($startlimit, $lengthlimit) = $this->parseLimit ($sqlSequence);
		return $this->_XMLBackend->select ($from, $fieldData, $where, $order, $startlimit, $lengthlimit);
	}
	
	function parseLimit ($sqlSequence) {
		$data = $this->getDataAfterKeyword ($sqlSequence, 'LIMIT');
		if (strlen (trim ($data)) == 0) {
			$start = 0;
			$length = 0;
		} elseif (strpos ($data, ',') === false){
			$start = 0;
			$length = trim ($data);
		} else {
			list ($start, $length) = explode (',', $data);
		}
		return (array ($start, $length));
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
				$data = ' '.$data; // AllDataAfterKeyWord seems to need this
				$fields = $this->parseBetweenTwoChars ('(', ')', $this->getAllDataAfterKeyword ($data, 'KEY'));
				$table->setPrimaryAutocountKey ($fields);
			} elseif ($fieldDatas[0] == 'UNIQUE') {
				$data = implode (' ', array_slice ($fieldDatas, 1));
				$data = ' '.$data; // AllDataAfterKeyWord seems to need this
				$fields = $this->parseBetweenTwoChars ('(', ')', $this->getAllDataAfterKeyword ($data, 'KEY'));	
				$table->addUniqueKeys (explode (',',$fields));
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
		foreach ($orderOfFields as $key => $field) {
			$field = trim ($field);
			$orderOfFields[$key] = $field;
		}
		
		
		
		$valuesString = $this->getAllDataAfterKeyword ($sqlSequence, 'VALUES');
		$valuesString = trim ($valuesString);
		$valuesString{0} = ' ';
		$valuesString{strlen ($valuesString)-1} = ' ';
		$valuesString = trim ($valuesString);
		$valuesFields = $this->splitData (',', $valuesString);
		//var_dump ($valuesFields);

		if (count ($orderOfFields) != count ($valuesFields)) {
			return "ERROR_XMLSQL_PARSE_ERROR";
		}
		$table = $this->_XMLBackend->getTable ($tableName);
		$tableFields = $table->getFields ();
		foreach ($tableFields as $field) {
			$key = array_search ($field->getName (), $orderOfFields);
			if ($key !== false) {
				$value = $this->parseBetweenTwoChars ('\'', '\'', $valuesFields[$key]);
			} else {
				$value = NULL;
			}
			$values[$field->getName ()] = $value;
		}
		
		$row = new row ($tableFields, $values, $table->getNewInternalID ());
		$a = $table->addRow ($row);
		if (! isError ($a)) {
			return new SQLInsertQuery ($a);
		} else {
			return $a;
		}
	}
	
	function parseShow () {
		return new SQLQuery ();
	}
	
	function parseWhere ($sqlSequence) {
		$data = $this->getAllDataAfterKeyword ($sqlSequence, 'WHERE');
		if (strlen (trim ($data)) == 0) {
			return array ();
		}
		$allConditions = array ();
		$i = 0;
		while (true) {
			$nextkeyword = trim ($this->getNextKeyword ($data));
			$datacond = trim ($this->findDataUntilNextKeyword ($data));
			$data = $this->getAllDataAfterKeyword ($data, $nextkeyword);
			list ($fieldName, $value2) = $this->splitData ('=', $datacond);
			$operator = '=';
			$condition = new SQLCondition ($fieldName, $operator, $value2);
			$allConditions[] = $condition;
			if ($nextkeyword == 'AND' or $nextkeyword == 'OR' or count ($allConditions) == 0) {
				$allConditions[] = $nextkeyword;
			} else {		
				break;
			}
		}
		return $allConditions;
	}
	
	function firstSpace ($string) {
		return stripos ($string, ' ');
	}
	
	function getNextKeyword ($sqlSequence) {
		$inString = false;
		$allKeywords = array ('INSERT', 'INTO', 'VALUES', 'SELECT', 'FROM', 'ORDER', 'BY', 'WHERE', 'OR', 'AND');
		$latestWord = '';
		$i = 0;
		while ($i < strlen ($sqlSequence)) {
			if ($i != 0) {
				if (($sqlSequence[$i-1] == ' ') and ($inString == false)) {
					$latestWord = '';
				}
			}
			$latestWord .= $sqlSequence[$i];
			if ($sqlSequence[$i] == "'"){
				if ($i != 0) {
					if ($sqlSequence[$i-1] !== '\\') { 
						if ($inString) {
							$inString = false;
						} else {
							$inString = true;
						}
					}
				}
			}

			if ((in_array ($latestWord, $allKeywords)) and ($inString == false)) {
				return $latestWord;
			}
			$i++;
		}
		return '';
	}
	
	function getDataAfterKeyword ($sqlSequence, $keyword) {
		if (stripos ($sqlSequence, " $keyword ") !== false) {
			$afterKeyword = substr ($sqlSequence, stripos ($sqlSequence, " $keyword ")+strlen($keyword)+2);
			$afterKeyword = trim ($afterKeyword);
			if ($this->firstSpace ($afterKeyword)) {
				$data = substr ($afterKeyword, 0, $this->firstSpace ($afterKeyword));
			} else {
				$data = substr ($afterKeyword, 0);
			}
			return $data;
		} else {
			return '';
		} 
	}
	
	function getAllDataAfterKeyword ($sqlSequence, $keyword) {
		if (stripos ($sqlSequence, ' '.$keyword) !== false) {
			$pos = stripos ($sqlSequence, ' '.$keyword)+strlen($keyword)+1;
			
				$afterKeyword = substr ($sqlSequence, $pos);
				$afterKeyword = trim ($afterKeyword);
				$data = substr ($afterKeyword, 0);
				return $data;
		} else {
			return '';
		}
	}
	
	function findDataUntilNextKeyword ($sqlSequence) {
		$inString = false;
		$allKeywords = array ('INSERT', 'INTO', 'VALUES', 'SELECT', 'FROM', 'ORDER', 'BY', 'WHERE', 'OR', 'AND');
		$latestWord = '';
		$data = '';
		$i = 0;
		while ($i < strlen ($sqlSequence)) {
			if ($i != 0) {
				if (($sqlSequence[$i-1] == ' ') and ($inString == false)) {
					$data .= $latestWord;
					$latestWord = '';
				}
			}
			$latestWord .= $sqlSequence[$i];
			if ($sqlSequence[$i] == "'"){
				if ($i != 0) {
					if ($sqlSequence[$i-1] !== '\\') { 
						if ($inString) {
							$inString = false;
						} else {
							$inString = true;
						}
					}
				}
			}

			if ((in_array ($latestWord, $allKeywords)) and ($inString == false)) {
				return $data;
			}
			$i++;
		}
		$data .= $latestWord;
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

function isAlphaNumeric ($char) {
	$all = array_merge (range ('A','Z'), range ('a','z'), range ('0','9'));
	return in_array ($char, $all);
}

?>