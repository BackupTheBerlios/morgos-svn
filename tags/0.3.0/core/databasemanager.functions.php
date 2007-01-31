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
/** \file databasemanager.functions.php
 * File that take care of the database modules
 *
 * @ingroup core database
 * @since 0.2
 * @author Nathan Samson
 * @author Sam Heijens
*/

/**
 * Manage database
 * @defgroup database Database
*/

include_once ('core/compatible.functions.php');
/**
 * Loads a database module. Returns the newly created class.
 *
 * @ingroup database
 * @since 0.2
 * @return module (class) the newly created class.
*/
function databaseLoadModule ($module) {
	if (databaseModuleExists ($module, true)) {
		$allModules = databaseGetAllModules ();
		$dbClass = new $allModules[$module] ();
		return $dbClass;
	} else {
		return new Error ('DATABASEMANAGER_MODULE_DOES_NOT_EXITS', $module);
	}
}

/**
 * Returns all available modules. If $checkReqs is true it checks of that all
 * requirements (PHP extensions) are installed.
 *
 * @ingroup database
 * @since 0.2
 * @param $checkReqs (bool) Check that all PHP extensions are installed.
 * @return array (string) all modules.
*/
function databaseGetAllModules ($checkReqs = false) {
	global $allModules, $availableModules;
	$allModules = array ();
	$availableModules = array ();
	foreach (scandir ('core/databases') as $file) {
		if (is_file ('core/databases/'.$file)) {
			include ('core/databases/'.$file);
		}
	}
	
	if ($checkReqs) {
		return $availableModules;
	} else {
		return $allModules;
	}
}

/**
 * Checks if a database module exists.
 *
 * @ingroup database
 * @since 0.2
 * @param $module (string) the module name to check
 * @param $checkReqs (bool) Check that the all extensions for that module exists.
 * @return exists (bool)
*/
function databaseModuleExists ($module, $checkReqs = false) {
	if (array_key_exists ($module, databaseGetAllModules ($checkReqs))) {
		return true;
	} else {
		return false;
	}
}

/**
 * Base class for all databasemodules.
 *
 * @ingroup core database
 * @since 0.2
 * @author Nathan Samson
*/
class databaseActions {

	/**
	 * The prefix for databasetables
	 * @protected
	*/
	var $prefix;
	/**
	 * The type of the database. Should be uniqe for each database. Examples: MySQL, MySQLi, PgSQL, ...
	 * @protected
	*/
	var $type;
	
	/**
	 * Set the database tablenames prefix.
	 *
	 * @param $prefix (string)
	 * @public
	*/
	function setPrefix ($prefix) {
		$this->prefix = $prefix;
	}

	/**
	 * Returns the prefix of the tablenames
	 *
	 * @public
	 * @return (string)
	*/
	function getPrefix () {return $this->prefix;}
	
	/**
	 * Sets the type of the database
	 *
	 * @param $type (string)
	 * @protected
	*/
	function setType ($type) {
		$this->type = $type;
	}
	
	/**
	 * Returns the databasetype
	 *
	 * @return (string)
	 * @public
	*/
	function getType () {return $this->type;}
}
?>