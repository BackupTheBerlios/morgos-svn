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
/** @file pgsql.database.class.php
 * Postgre SQL database module.
 *
 * @ingroup database core
 * @since 0.3
 * @author Nathan Samson
*/

$allModules['PostgreSQL'] = 'pgsqlDatabaseActions';
if (function_exists ('pg_connect')) {
	$availableModules['PostgreSQL'] = 'pgsqlDatabaseActions';
}

if (! class_exists ('pgsqlDatabaseActions')) {
	isset ($t); // trick documentor
	
	class pgsqlDatabaseActions extends databaseActions {
		var $_dbName;
		var $_connection;
		var $_connectionString;	
	
		function pgsqlDatabaseActions () {
			$this->setType ('PostgreSQL');
			$this->_connectionString = '';
			$this->_connection = null;
		}
	
		function connect($host,$userName,$password) {
			$this->_connectionString = "host=$host user=$userName password=$password";
		}
		
		function disconnect () {
			$this->_connectionString = '';
			if ($this->_connection) {
				@pg_close ($this->_connection);
				$this->_connection = null;
			} else {
				return new Error ('DBDRIVER_NOT_CONNECTED');
			}
		}
	
		function selectDatabase ($dbName) {
			$this->_connection = pg_connect (
					$this->_connectionString." dbname=$dbName");
			$this->_connectionString == '';
			if ($this->_connection == false) {
				return new Error ('DB_DRIVER_CANT_CONNECT', pg_last_error ());
			}
			$this->_dbName = $dbName;
		}
	        
		function query ($sql) {
			$result = @pg_query ($this->_connection, $sql);
			if ($result !== false) {
				return $result;
			} else {
				return new Error ('SQL_QUERY_FAILED', $sql, pg_last_error ());
			}
		}
	        
		function numRows ($query) {
			return pg_num_rows ($query);
		}
		
		function affectedRows ($query) {
			return pg_affected_rows ($query);
		}
	        
		function fetchArray ($query) {
			$var = pg_fetch_array ($query);
			return $var;
		}
	        
		function latestInsertID ($q) {
			/*Not sure if this works on all servers, guess it works only from 8.1 on*/
			$c = $this->query ("SELECT lastval() AS lastinsertid");
			$d = $this->fetchArray ($c);
			return $d['lastinsertid'];
		}
		
		function getAllFields ($tableName) {
			$tableName = $this->escapeString ($tableName);
			$q = $this->query ("SELECT * FROM information_schema.columns WHERE table_name='$tableName'");
			if (! isError ($q)) {
				$allFields = array ();
				if (pg_num_rows ($q) > 0) {
					while ($row = pg_fetch_assoc ($q)) {
						$type = $row['data_type'];
						$default = $row['column_default'];
						if (substr ($type, 0, 3) == 'int') {
							$maxlength = 11;
							$type = 'int';
							if (substr ($default, 0, 7) == 'nextval') {
								$default = null;
							}
						} elseif (substr ($type, 0, 8) == 'smallint') {
							$maxlength = 1;
							$type = 'int';
						} elseif (substr ($type, 0, 17) == 'character varying') {
							$maxlength = (int)$row['character_maximum_length'];
							$type = 'string';
						} else {
							$maxlength = null;
						}
						if ($row['is_nullable'] == 'YES') {
							$row['Null'] = true;
						} else {
							$row['Null'] = false;
						}
						
						$field = array (
								'Field'=>$row['column_name'],
								'Type'=>$type,
								'Null'=>$row['Null'],
								'MaxLength'=>$maxlength,
								'Default'=>$default
								);
						$allFields[$row['ordinal_position']] = $field;
					}	
				}
				
				/*Very unclean method to sort the array*/
				$allFieldsOrdered = array ();
				$i = 1;
				while ($field = current ($allFields)) {
					if (key ($allFields) == $i) {
						$allFieldsOrdered[] = $field;
						unset ($allFields[$i]);
						$i++;
					}
					$a = next ($allFields);
					if ($a == false) {
						reset ($allFields);
					}
				}
				return $allFieldsOrdered;
			} else {
				return $q;
			}
		}
		
		function getAlldbFields ($tableName, $filter = array ()) {
			$allFields = $this->getAllFields ($tableName);
			$alldbFields = array ();
			if (! isError ($allFields)) {
				foreach ($allFields as $field) {
					$type = $field['Type'];
					if (substr ($type, 0, 3) == 'int') {
						$dbtype = DB_TYPE_INT;
					} elseif (substr ($type, 0, 6) == 'string') {
						$dbtype = DB_TYPE_STRING;
					} elseif (substr ($type, 0,5) == 'text') {
						$dbtype = DB_TYPE_TEXT;
					} else {
						$dbtype = DB_TYPE_STRING;
					}
					
					$maxSize = $field['MaxLength'];			
	
					$dbField = new dbField ($field['Field'], $dbtype, (int) $maxSize);
					$dbField->canBeNull = $field['Null'];
					if (! in_array ($dbField->getName (), $filter)) {
						//var_dump ($dbField);
						$alldbFields[] = $dbField;
					}
				}
				return $alldbFields;
			} else {
				return $allFields;
			}
		}
		
		function getAllTables () {
			$q = $this->query ("SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
			if (! isError ($q)) {
				$allTables = array ();
				while ($row = pg_fetch_assoc ($q)) {
					$allTables[] = $row['table_name'];
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
			return pg_escape_string ($value);
		}
		
		function tableExists ($tableName) {
			$allTables = $this->getAllTables ();
			if (in_array ($this->getPrefix().$tableName, $allTables)) {
				return true;
			} else {
				return in_array (strtolower ($this->getPrefix().$tableName), $allTables);
			}
		}
	
	}
}

?>
