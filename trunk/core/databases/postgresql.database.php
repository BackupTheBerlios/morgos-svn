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
/** \file postgresql.database.php
 * File that take care of the database (PostgreSQL) SubSystem
 *
 * \author Nathan Samson
*/

if (function_exists ('pg_connect')) {
	$supported['PostgreSQL 6.5'] = 'postgreSQLDatabase';
	$supported['PostgreSQL 7.x'] = 'postgreSQLDatabase';
	$supported['PostgreSQL 8.x'] = 'postgreSQLDatabase';
}

if (array_search ('postgreSQLDatabase', $supported, true)) {
gettype ($supported); // this is here only to trick Doxygen
/** \class postgreSQLDatabase
 * class that take care of the database (PostgreSQL) SubSystem
 *
 * \author Nathan Samson
 * \todo if no database connection exists at query-time, try to reconnect and try to redo the query
*/
class postgreSQLDatabase /*implements iDatabase*/ {
	function __construct () {
	}

	/*public*/ function connect ( $host,  $user,  $password,  $database) {
		$link  =  'user=' .$user;
		$link .= ' password=' .$password;
		$link .= ' dbname=' . $database;
		$link .= ' host=' . $host;
		$this->connection = pg_connect ($link);
		if ($this->connection == false) {
			trigger_error ('Couldn\'t connect with database', E_USER_ERROR);
			trigger_error ($this->error, E_USER_NOTICE);
		} else {
			return true;
		}
	}

	/*public*/ function close () {
		pg_close ($this->connection);
	}

	/*public*/ function query ( $sql,  $fatal = false) {
		if (! isset ($this->connection)) {
			trigger_error ('No Database connection', E_USER_WARNING);
		} else {
			$result =  pg_query ($this->connection, $sql);
			if ($result == false) {
				if ($fatal == true) {
					trigger_error ('Query not executed', E_USER_ERROR);
					trigger_error ($this->error, E_USER_NOTICE);
				} else {
					trigger_error ('Query not executed', E_USER_WARNING);
					trigger_error ($this->error, E_USER_NOTICE);					
				}
			} else {
				return $result;
			}
		}
	}

	/*public*/ function get_all_tables ( $DBName) {
		$tables = array ();
		while ($row = $this->fetch_array ($this->list_tables ())) {
			$tables[] = $row['relname'];
		}
		return $tables;
	}

	/*public*/ function fetch_array ( $result) {
		return pg_fetch_array ($result);
	}

	/*public*/ function num_rows ( $result) {
		return pg_num_rows ($result);
	}
	
	/*private*/ function error () {
		return pg_last_error ();
	}
	
	/*private*/ function list_tables () {
		$sql = 'SELECT relname FROM pg_stat_user_tables ORDER by relname';
		$result = $this->query ($sql);
		return $result;
	}
}
}
?>
