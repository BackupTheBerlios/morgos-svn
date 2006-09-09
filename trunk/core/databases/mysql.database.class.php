<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2006 MorgOS
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Library General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
*/
/** @file mysql.database.class.php
 * MySQL database module.
 *
 * @since 0.2
 * @author Sam Heijens
 * @author Nathan Samson
*/

$allModules['MySQL'] = 'mysqlDatabaseActions';
if (function_exists ('mysql_connect')) {
	$availableModules['MySQL'] = 'mysqlDatabaseActions';
}

// for the tests
$allModules['EXISTINGBUTNOTWORKING'] = 'EMPTY';

if (! class_exists ('mysqlDatabaseActions')) {
	isset ($t); // trick documentor
	
	class mysqlDatabaseActions extends databaseActions {
		var $dbName;	
	
		function mysqlDatabaseActions () {
			$this->setType ('MySQL');
		}
	
		function connect($host,$userName,$password) {
			$this->connection = @mysql_connect ($host,$userName,$password);
			if ($this->connection == false) {
				return new Error ('DATABASE_CONNECTION_FAILED', mysql_error ());
			}
		}
		
		function disconnect () {
			mysql_close ($this->connection);
		}
	
		function selectDatabase ($dbName) {
			$result = @mysql_select_db ($dbName, $this->connection);
			if ($result == false) {
				return new Error ('DATABASE_SELECTDB_FAILED', mysql_error ());
			}
			$this->dbName = $dbName;
		}
	        
		function query ($sql) {
			$result = mysql_query ($sql, $this->connection);
			if ($result !== false) {
				return $result;
			} else {
				return new Error ('DATABASE_QUERY_FAILED', mysql_error ());
			}
		}
	        
		function numRows ($query) {
			return mysql_num_rows ($query);
		}
	        
		function fetchArray ($query) {
			$var = mysql_fetch_array ($query);
			return $var;
		}
	        
		function latestInsertID ($q) { 
			return mysql_insert_id ($this->connection);
		}
		
		function getAllFields ($tableName) {
			$tableName = $this->escapeString ($tableName);
			$q = $this->query ("SHOW COLUMNS FROM $tableName");
			if (! isError ($q)) {
				$allFields = array ();
				if (mysql_num_rows ($q) > 0) {
					while ($row = mysql_fetch_assoc ($q)) {
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
			return $alldbFields;
		}
		
		function getAllTables () {
			$q = $this->query ("SHOW TABLES FROM {$this->dbName}");
			if (! isError ($q)) {
				$allTables = array ();
				while ($row = mysql_fetch_assoc ($q)) {
					$allTables[] = $row['Tables_in_'.$this->dbName];
				}
				return $allTables;
			} else {
				return $q;
			}
		}
		
		function escapeString ($value) {
			if (get_magic_quotes_gpc ()) {
				$value = stripslashes ($value);
			}
			return mysql_real_escape_string ($value, $this->connection);
		}
	
	}
}

?>
