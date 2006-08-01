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
/** \file mysql4.database.php
 * File that take care of the database (MySQL) SubSystem
 *
 * $Id$
 * \author Nathan Samson
*/

if (function_exists ('mysql_connect')) {
	$supported['MySQL'] = 'MySQLDatabase';
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
	function MySQLDatabase (&$i10nMan) {
		$this->__construct ($i10nMan);
	}

	function __construct (&$i10nMan) {
		$this->i10nMan = &$i10nMan;
	}

	/*public*/ function connect ( $host,  $user,  $password) {
		$this->connection = mysql_connect ($host, $user, $password);
		if ($this->connection == false) {
			return false;
		} else {
			return true;
		}
	}
	
	/*public*/ function select_db ($database) {
		$succeed = mysql_select_db ($database, $this->connection);
		if ($succeed == true) {
			return true;
		} else {
			trigger_error ('DEBUG: ' . $this->error ());
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('Couldn\'t open database'));
		}
	}

	/*public*/ function close () {
		mysql_close ($this->connection);
	}

	/*public*/ function query ( $sql,  $fatal = true) {
		if (! isset ($this->connection)) {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('No Database connection'));
		} else {
			$sql = $this->sql2mysql ($sql);
			$result = mysql_query ($sql, $this->connection);
			if ($result == false) {
				echo $sql;
				echo $this->error ();
				if ($fatal == true) {
					trigger_error ('DEBUG: ' . $sql . '  ' . $this->error ());
					trigger_error ('ERROR: ' . $this->i10nMan->translate ('Query not executed'));
				} else {
					trigger_error ('DEBUG: ' . $this->error ());					
					trigger_error ('WARNING: ' . $this->i10nMan->translate ('Query not executed'));
				}
				return false;
			} else {
				return $result;
			}
		}
	}

	/*public*/ function fetch_array ( $result) {
		return mysql_fetch_array ($result, MYSQL_ASSOC);
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
	
	/*public*/ function getType () {
		return 'MySQL';
	}
	
	/*private*/ function sql2mysql ( $sql) {
		if (ereg ('( serial )',$sql)) {
			$sql = ereg_replace ('( serial )', ' int AUTO_INCREMENT ', $sql);
		}
		return $sql;
	}
	
	/*private*/ function error () {
		return mysql_errno () . ': ' . mysql_error () . ' ';
	}
}
}
?>
