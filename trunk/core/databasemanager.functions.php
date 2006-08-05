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
/** \file databasemanager.functions.php
 * File that take care of the database modules
 *
 * @since 0.2
 * @author Nathan Samson
 * @author Sam Heijens
*/

/**
 * Loads a database module. Returns the newly created class.
 *
 * @return module (class) the newly created class.
*/
function databaseLoadModule ($module) {
	if (databaseModuleExists ($module)) {
		$allModules = databaseGetAllModules ();
		$dbClass = new $allModules[$module] ();
		return $dbClass;
	} else {
		return "ERROR_DATABASEMANAGER_MODULE_DOES_NOT_EXITS $module";
	}
}

/**
 * Returns all available modules. If $checkReqs is true it checks of that all
 * requirements (PHP extensions) are installed.
 *
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

function databaseInstallModule () {
}

function databaseUnInstallModule () {
}

class databaseActions {

	var $prefix;
	var $type;
	
	function setPrefix ($prefix) {
		$this->prefix = $prefix;
	}

	function getPrefix () {return $this->prefix;}
	
	function setType ($type) {
		$this->type = $type;
	}
	
	function getType () {return $this->type;}
}

?>