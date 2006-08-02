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
 * @license GPL
*/

$allModules['MySQL'] = 'mysqlDatabaseActions';
if (function_exists ('mysql_connect')) {
	$availableModules['MySQL'] = 'mysqlDatabaseActions';
}

// for the tests
$allModules['EXISTINGBUTNOTWORKING'] = 'EMPTY';

if (! class_exists ('mysqlDatabaseActions')) {
	class mysqlDatabaseActions {
		function connect($host,$userName,$password) {
			$this->connection = @mysql_connect ($host,$userName,$password);
			if ($this->connection == false) {
				return "ERROR_DATABASE_CONNECTION_FAILED " . mysql_error ();
			}
		}
	
		function selectDatabase ($dbName) {
			$result = @mysql_select_db ($dbName, $this->connection);
			if ($result == false) {
				return "ERROR_DATABASE_SELECTDB_FAILED " . mysql_error ();
			}
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
}

?>