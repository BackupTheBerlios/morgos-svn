<?php

$allModules['XML'] = 'XMLDatabase';
$availableModules['XML'] = 'XMLDatabase';

if (! class_exists ('XMLDatabase')) {

class XMLDatabase extends databaseActions {

	var $_XMLSQLBackend;
	
	function XMLDatabase () {
		include_once ('core/databases/xml/xmlsqlbackend.class.php');
		$this->_XMLSQLBackend = new XMLSQLBackend ();
		$this->setType ('XMLSQL');
	}
	
	function connect ($db, $user, $pass) {
		$this->_XMLSQLBackend->connect ($db, $user, $pass);
	}
	
	function disconnect () {
		$this->_XMLSQLBackend->disconnect ();
	}
	
	function selectDatabase ($dbName) {
		$this->_XMLSQLBackend->load ($dbName);
	}
	
	function query ($sql) {
		return $this->_XMLSQLBackend->parseCommand ($sql);
	}
	
	function fetchArray ($query) {
		return $query->fetchArray ();
		
	}
	
	function numRows ($query) {
		return $query->numRows ();
	}
	
	function getAllFields ($tableName) {
		$tableName = $this->escapeString ($tableName);
		$q = $this->query ("SHOW COLUMNS FROM $tableName");
		if (! isError ($q)) {
			$allFields = array ();
			if ($q->numRows () > 0) {
				while ($row = $q->fetchArray ()) {
					$allFields[] = $row;
				}
			}
			return $allFields;
		} else {
			return $q;
		}
	}
	
	function getAlldbFields ($tableName, $filter = array ()) {
		$allFields = $this->getAllFields ($tableName);
		$alldbFields = array ();
		foreach ($allFields as $field) {
			$dbField = new dbField ();
			$dbField->name = $field['Field'];
			$dbField->type = $field['Type'];
			if ($field['Null']) {
				$dbField->canBeNull = true;
			}
			if (! in_array ($dbField->name, $filter)) {
				$alldbFields[$dbField->name] = $dbField;
			}
		}
		//return $alldbFields;
		return array ();
	}
	
	function getAllTables () {
		/*$q = $this->query ("SHOW TABLES FROM {$this->dbName}");
		if (! isError ($q)) {
			$allTables = array ();
			while ($row = $this->fetchArray ($q)) {
				$allTables[] = $row['Tables_in_'.$this->dbName];
			}
			return $allTables;
		} else {
			return $q;
		}*/
		return array ();
	}
	
	function latestInsertID () {
		return 1;
	}
}
}

?>