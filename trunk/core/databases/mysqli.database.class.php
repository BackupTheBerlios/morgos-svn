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
/** @file mysqli.database.class.php
 * MySQL Improved database module.
 *
 * @ingroup database core
 * @since 0.3
 * @author Nathan Samson
*/

$allModules['MySQLI'] = 'mysqliDatabaseActions';
if (class_exists ('mysqli')) {
	$availableModules['MySQLI'] = 'mysqliDatabaseActions';
}

if (! class_exists ('mysqliDatabaseActions')) {
	isset ($t); // trick documentor
	
	class mysqliDatabaseActions extends databaseActions {
		var $_mysqli;
		var $_dbName;
		
		function mysqliDatabaseActions () {
			$this->setType ('MySQLI');
			$this->_mysqli = null;
		}
		
		function connect ($host, $user, $password, $dbName) {
			$this->_dbName = $dbName;
			$this->_mysqli = new mysqli ();
			$res = @$this->_mysqli->connect ($host, $user, $password, $dbName);
			if (mysqli_connect_errno () != null) {
				return new Error ("DBDRIVER_CANT_CONNECT", mysqli_connect_error ());
			}
		}
		
		function disconnect () {
			if ($this->_mysqli) {
				@$this->_mysqli->close ();
				$this->_mysqli = null;
			} else {
				return new Error ('DBDRIVER_NOT_CONNECTED');
			}
		}
	        
		function query ($sql) {
			$result = $this->_mysqli->query ($sql);
			if ($result !== false) {
				return $result;
			} else {
				return new Error ('SQL_QUERY_FAILED', $sql, $this->_mysqli->error);
			}
		}
	        
		function numRows ($query) {
			return $query->num_rows;
		}
		
		function affectedRows ($query) {
			return $this->_mysqli->affected_rows;
		}
	        
		function fetchArray ($query) {
			$var = $query->fetch_array ();
			return $var;
		}
	        
		function latestInsertID ($q) { 
			return $this->_mysqli->insert_id;
		}
		
		function getAllFields ($tableName) {
			$tableName = $this->escapeString ($tableName);
			$q = $this->query ("SHOW COLUMNS FROM $tableName");
			if (! isError ($q)) {
				$allFields = array ();
				while ($row = $this->fetchArray ($q)) {
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
			$q = $this->query ("SHOW TABLES FROM {$this->_dbName}");
			if (! isError ($q)) {
				$allTables = array ();
				while ($row = $this->fetchArray ($q)) {
					$allTables[] = $row[0];
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
			return $this->_mysqli->escape_string ($value);
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