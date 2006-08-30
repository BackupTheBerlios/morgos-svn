<?php

$allModules['XML'] = 'XMLDatabase';
$availableModules['XML'] = 'XMLDatabase';

if (! class_exists ('XMLDatabase')) {

class XMLDatabase extends databaseActions {

	var $_XMLSQLBackend;
	
	function XMLDatabase () {
		include_once ('core/databases/XML/xmlsqlbackend.class.php');
		$this->_XMLSQLBackend = new XMLSQLBackend ();
	}
	
	function connect ($db, $user, $pass) {
		$this->_XMLSQLBackend->load ($db, $user, $pass);
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
}
}

?>