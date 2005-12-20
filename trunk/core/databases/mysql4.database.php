<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005 MorgOS
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
/** \file mysql4.database.php
 * File that take care of the database (MySQL) SubSystem
 *
 * \author Nathan Samson
*/

if (function_exists ('mysql_connect')) {
	$supported['MySQL 3.x'] = 'MySQLDatabase';
	$supported['MySQL 4.x'] = 'MySQLDatabase';
}
if (function_exists ('mysqli_connect')) {
	//$supported['MySQLi 4.1'] = 'Database_mysqli';
	//$supported['MySQLi 5.x'] = 'Database_mysqli';
}

if ((array_search ('MySQLDatabase', $supported, true) and  (! class_exists ('MySQLDatabase')))) {
gettype ($supported); // this is here only to trick Doxygen
/** \class MySQLDatabase
 * class that take care of the database (MySQL) SubSystem.
 *
 * \author Nathan Samson
 * \version 0.1svn
 * \todo if no database connection exists at query-time, try to reconnect and try to redo the query
*/
class MySQLDatabase /*implements iDatabase*/ {
	function __construct () {
	}

	/*public*/ function connect ( $host,  $user,  $password) {
		$this->connection = mysql_connect ($host, $user, $password);
		if ($this->connection == false) {
			trigger_error ($this->error (), E_USER_NOTICE);
			trigger_error ('ERROR: Couldn\'t connect with database', E_USER_ERROR);
		} else {
			return true;
		}
	}
	
	/*public*/ function select_db ($database) {
		$succeed = mysql_select_db ($database, $this->connection);
		if ($succeed == true) {
			return true;
		} else {
			trigger_error ($this->error (), E_USER_NOTICE);
			trigger_error ('ERROR: Couldn\'t open database', E_USER_ERROR);
		}
	}

	/*public*/ function close () {
		mysql_close ($this->connection);
	}

	/*public*/ function query ( $sql,  $fatal = true) {
		if (! isset ($this->connection)) {
			trigger_error ('ERROR: No Database connection', E_USER_WARNING);
		} else {
			$sql = $this->sql2mysql ($sql);
			$result = mysql_query ($sql, $this->connection);
			if ($result == false) {
				if ($fatal == true) {
					trigger_error ($this->error (), E_USER_NOTICE);
					trigger_error ('ERROR: Query not executed', E_USER_ERROR);
				} else {
					trigger_error ($this->error (), E_USER_NOTICE);					
					trigger_error ('ERROR: Query not executed', E_USER_WARNING);
				}
			} else {
				return $result;
			}
		}
	}

	/*public*/ function fetch_array ( $result) {
		return mysql_fetch_array ($result);
	}

	/*public*/ function get_all_tables ( $DBName) {
		$tables_list = $this->list_tables ($DBName);
		$tables = NULL;
		$i = 0;
		while ($row = $this->fetch_array ($tables_list)) {
			$tables .= $this->table_name ($tables_list,$i) . ',';
			$i++;
		}
		$tables = explode (',',$tables);
		return $tables;
	}

	/*public*/ function num_rows ( $result) {
		return mysql_num_rows ($result);
	}
	
	/*private*/ function sql2mysql ( $sql) {
		if (ereg ('( serial )',$sql)) {
			$sql = ereg_replace ('( serial )', ' int AUTO_INCREMENT ', $sql);
		}
		return $sql;
	}
	
	/*private*/ function table_name ( $result,$i) {
		return mysql_table_name ($result, $i);
	}
	
	/*private*/ function error () {
		return 'ERROR: ' . mysql_errno () . ': ' . mysql_error () . ' ';
	}
	
	/*private*/ function list_tables ( $DBName) {
		
	}
}
}
?>
