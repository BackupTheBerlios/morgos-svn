<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2007 MorgOS
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
/** @file mysql.dbdriver.class.php
 * MySQL database module.
 *
 * @ingroup database core
 * @since 0.2
 * @since 0.4 adapted to the new driver API
 * @author Sam Heijens
 * @author Nathan Samson
*/

if (! class_exists ('MySQLDatabaseDriver')) {
	isset ($t); // trick documentor
	
	class MySQLDatabaseDriver extends DatabaseDriver {
		var $dbName;	
		var $connection;
	
		function MySQLDatabaseDriver () {
			$this->setType ('MySQL');
			$this->connection = null;
		}
	
		function connect ($host,$userName,$password, $dbName) {
			$this->connection = @mysql_connect ($host,$userName,$password);
			if ($this->connection == false) {
				return new Error ('DBDRIVER_CANT_CONNECT', mysql_error ());
			}
			$result = mysql_select_db ($dbName, $this->connection);
			$this->dbName = $dbName;
			if ($result == false) {
				return new Error ('DBDRIVER_CANT_CONNECT', mysql_error ());
			}
		}
		
		function disconnect () {
			if ($this->connection) {
				mysql_close ($this->connection);
				$this->connection = null;
			} else {
				return new Error ('DBDRIVER_NOT_CONNECTED');
			}
		}
	        
		function query ($sql) {
			$result = mysql_query ($sql, $this->connection);
			if ($result !== false) {
				return $result;
			} else {
				return new Error ('SQL_QUERY_FAILED', $sql, mysql_error ($this->connection));
			}
		}
	        
		function numRows ($query) {
			return mysql_num_rows ($query);
		}
		
		function affectedRows ($query) {
			if (is_bool ($query)) {
				return mysql_affected_rows ();
			} else {
				return mysql_affected_rows ($query);
			}
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
						$type = $row['Type'];
						if (substr ($type, 0, 3) == 'int') {
							$maxlength = substr ($type,
											 strpos ($type, '(')+1, 
											 strpos ($type, ')')-
											 	strpos ($type, '(')-1);
							$type = 'int';
						} elseif (substr ($type, 0, 7) == 'varchar') {
							$maxlength = substr ($type,
											 strpos ($type, '(')+1, 
											 strpos ($type, ')')-
											 	strpos ($type, '(')-1);
							$type = 'string';
						} else {
							$maxlength = null;
						}
						if ($row['Null'] == 'YES') {
							$row['Null'] = true;
						} else {
							$row['Null'] = false;
						}
						
						$field = array (
								'Field'=>$row['Field'],
								'Type'=>$type,
								'Null'=>$row['Null'],
								'MaxLength'=>(int)$maxlength,
								'Default'=>$row['Default']
								);
						$allFields[] = $field;
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
			if (! isError ($allFields)) {
				foreach ($allFields as $field) {
					$type = $field['Type'];
					if (substr ($type, 0, 3) == 'int') {
						$dbtype = DB_TYPE_INT;
					} elseif (substr ($type, 0, 7) == 'varchar') {
						$dbtype = DB_TYPE_STRING;
					} elseif (substr ($type, 0,5) == 'text') {
						$dbtype = DB_TYPE_TEXT;
					} elseif (substr ($type, 0, 5) == 'enum') {
						$dbtype = DB_TYPE_ENUM;
					} else {
						$dbtype = DB_TYPE_STRING;
					}
					
					$maxSize = 0;
					if ($dbtype != DB_TYPE_ENUM) {
						if (strpos ($type, '(') and strpos ($type, '(')) {
							$maxSize = substr ($type, strpos ($type, '(')+1, 
								strpos ($type, ')')-strpos ($type, '(')-1); 
						}
					} 				
	
					$dbField = new dbField ($field['Field'], $dbtype, (int) $maxSize);
					if ($field['Null']) {
						$dbField->canBeNull = true;
					}
					if (! in_array ($dbField->getName (), $filter)) {
						$alldbFields[] = $dbField;
					}
				}
				return $alldbFields;
			} else {
				return $allFields;
			}
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
DatabaseDriverManager::AddDriver ('MySQL', 'MySQLDatabaseDriver', 
							function_exists ('mysql_connect'));
?>
