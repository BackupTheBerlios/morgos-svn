<?php
/* XMLSQL is a SQL library written in PHP. All data is stored in XML files.
 * The goal is to create a database solution for web developers who have a host
 * that is so stupid to not give a normal database system.
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
/** \file xmlsql.database.php
 * File that defines the sql commands.
 *
 * \author Nathan Samson
*/
class XMLSQL {
	private $connection; // an array where info is stored about the connection
	
	public function __construct () {
	}
	
	/** \fn openConnection (string $directory, string $user, string $password)
	 * Opens a connection to a database.
	 *
	 * \param $directory (string) the directory where the database is stored
	 * \param $user (string) the username of the owner of the database
	 * \param $password (string) the password for that user
	*/
	public function openConnection (string $directory, string $user, string $password) {
		if (is_dir ($directory)) {
			foreach (scandir ($directory) as $files) {
				// the user file are random chars + username + random chars;
				if (strpos ($file, $user) !== false) {
					i
				}
			}
			$this->connection = array ('DIRECTORY' => $directory, 'USER' => $user, 'PASSWORD' => $password);
		} else {
			trigger_error ('ERROR: Host not found.');
		}
	}

	/** \fn closeConnection ()
	 * Closes the connection
	*/	
	public function closeConnection () {
		$this->connection = NULL;
	}
	
	/** \fn selectDB (string $DB)
	 * Connects to a specified database system
	*/
	public function selectDB (string $DB) {
		
	}
	
	/** \fn query (string $SQL)
	 * Queries a $SQL command.
	 *
	 * \return (array) on success, (bool) false on error
	*/
	public function query (string $SQL) {
		//$firstWord = 
	}
	
	/** \fn fetchArray (array $result)
	 * Fetch a result row as an associative array
	*/
	public function fetchArray (array $result) {
		
	}
	
	/** \fn numRows (array $result)
	 * Get number of rows in result.
	*/
	public function numRows (array $result) {
	}
	
	public function getAllTables () {
	}
}