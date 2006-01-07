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
/** \file database.class.php
 * File that take care of the database SubSystem
 *
 * \author Nathan Samson
*/
function checkDatabase ($database, $tables) {
	// TODO
	return true;
}
include_once ('core/compatible.php');
/** \interface iDatabase
 * interface that take care off the generic database subsystem. All Database-Modules must follow this API
 *
 * \version 0.1svn
 * \author Nathan Samson
*/
/*interface iDatabase {
	/* why does this API not follow the normal coding standards?
	 * The API is mostly copied from the normal MySQL API
	 * Do we want to change that?
	 * Maybe
	
	public function __construct ();
	public function connect (string $host, string $user, string $password);
	public function select_db (string $database);
	public function close ();
	public function query (string $sql, bool $fatal = false);
	public function fetch_array (resource $result);
	public function num_rows (resource $result);
	public function get_all_tables (string $DBName);
}*/

/** \class genericDatabase
 * Class that take care off the database SubSystem
 *
 * \version 0.1svn
 * \author Nathan Samson
*/
class genericDatabase {
	/*private $loadedDatabase = NULL;
	private $supported;*/

	function genericDatabase (&$i10nMan) {
		$this->__construct (&$i10nMan);
	}

	function __construct (&$i10nMan) {
		$this->i10nMan = &$i10nMan;
		$this->getAllSupportedDatabases ();
	}
	/** \fn getAllSupportedDatabases ()
	 * It returns an array with as key the type of the database. The value of the array is the name of the class
	 * where the implementation lives, this shouldn't be used public
	 * \return array (string)
	*/
	/*public*/ function getAllSupportedDatabases () {
		if (! isset ($this->supported)) {
			$supported = array ();
			$handler = opendir ('core/databases/');
			$files = scandir ('core/databases/');
			foreach ($files as $file) {
				// starts with a letter, then you have whatever you want and it ends with '.database.php'
				if ((preg_match ('/^\w.*\.database\.php$/i', $file) == 1) and (is_file ('core/databases/' . $file))) {
					include ('core/databases/' . $file);
				}
			}
			$this->supported = $supported;
		}
		return $this->supported;
	}

	/** \fn &load ($type)
	 * Loads a database implementation
	 *
	 * \param $type (string) the database type you wish to load, make sure it exists otherwise we will
	 * throw an error.
	 * \return class
	*/
	/*public*/ function &load ($type) {
		if (array_key_exists ($type, $this->supported)) {
			$className = $this->supported[$type];
			$this->loadedDatabase = new $className ($this->i10nMan);
			return $this->loadedDatabase;
		} else {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('Database type not supported.'));
		}
	}
}
?>
