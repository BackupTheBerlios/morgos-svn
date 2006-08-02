<?php

class mysqlDatabaseActions () {
	function connect($host,$userName,$password) {
		$this->connection = @mysql_connect ($host,$userName,$password);
		if ($this->connection == false) {
			return "ERROR_DATABASE_CONNECTION_FAILED " . mysql_error ();
		}
	}

	function selectDatabase ($dbName) {
		@mysql_select_db ($dbName, $this->connection)or return false;
	}
        
	function query ($query) {
		return mysql_query ($query, $this->connection);
	}
        
	function numRows () {
	}
        
	function fetchArray ($query) {
		$var = mysql_fetch_array ($query);
	}
        
	function latestID () { 
	}
}

?>