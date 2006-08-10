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

class databaseObject {
	var $basicOptions;
	var $extraOptions;
	var $db;
	var $ID;
	var $IDName;
	var $creator;
	
	/**
	 * The constructor
	 *
	 * @param $db (databaseModule)
	 * @param $extraOptions (empty array)
	 * @param $basicOptions (empty array)
	 * @param $tableName (string)
	 * @param $IDName (int)
	 * @param $creator (object)
	*/
	function databaseObject ($db, $extraOptions, $basicOptions, $tableName, $IDName, &$creator) {
		$this->db = $db;
		$this->setExtraOptions ($extraOptions);
		$this->setBasicOptions ($basicOptions);
		$this->setTableName ($tableName);
		$this->setIDName ($IDName);
		$this->setCreator (&$creator);
		$this->initEmpty ();
	}	
	
	/*Public initters*/	
	
	/**
	 * Initializes the object with an ID
	 *
	 * @param $ID (int)
	 * @public
	*/
	function initFromDatabaseID ($ID) {
		$sql = "SELECT * FROM {$this->db->getPrefix ()}{$this->getTableName ()} WHERE {$this->getIDName ()}='$ID'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			$this->initFromArray ($row);
			$this->ID = $row[$this->getIDName ()];
		} else {
			return $q;
		}
	}
	
	/**
	 * Initializes the object from an array
	 * @warning If a key isn't an option, an error will be generated and the object
	 * 		will be inited empty again.
	 *
	 * @param $array (mixed array) The keys are the options.
	 * @public
	*/
	function initFromArray ($array) {
		$this->initEmpty ();
		$allOptions = $this->getAllOptions ();
		foreach ($allOptions as $name => $value) {
			if (array_key_exists ($name, $array)) {
				$this->setOption ($name, $array[$name]);
			} else {
				$this->initEmpty ();
				return "ERROR_DATABASEOBJECT_KEY_NOT_EXISTS $name";
			}
		}
	}
	
	/*Public functions*/
	
	/**
	 * Gets the current value of an option.
	 *
	 * @param $name (string) the name of the option
	 * @public
	*/
	function getOption ($name) {
		$allOptions = $this->getAllOptions ();
		if (array_key_exists ($name, $allOptions)) {
			return $allOptions[$name];
		} else {
			return "ERROR_DATABASEOBJECT_OPTION_DOES_NOT_EXISTS $name";
		}
	}
	
	/**
	 * Sets the value of an option
	 *
	 * @param $name (string) the name of the option
	 * @param $value (mixed) the new value
	 * @public
	*/
	function setOption ($name, $value) {
		$allExtraOptions = $this->getExtraOptions ();
		$allBasicOptions = $this->getBasicOptions ();
		if (array_key_exists ($name, $allExtraOptions)) {
			$this->extraOptions[$name] = $value;
		} elseif (array_key_exists ($name, $allBasicOptions)) {
			$this->basicOptions[$name] = $value;
		} else {
			return "ERROR_DATABASEOBJECT_OPTION_DOES_NOT_EXISTS $name";
		}
	}	
	
	/**
	 * Updates all values from an array.
	 *
	 * @param $array (mixed array) The keys are the option names, values are the new values.
	 * @public
	*/
	function updateFromArray ($array) {
		$allOptions = $this->getAllOptions ();
		foreach ($allOptions as $option => $value) {
			if (array_key_exists ($option, $array)) {
				$this->setOption ($option, $array[$option]);
			}
		}
	}
	
	/**
	 * Checks that the current object is stored into the database.
	 *
	 * @public
	 * @return (bool)
	*/
	function isInDatabase () {
		if ($this->ID < 0) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Insert the object into the database.
	 *	 
	 * @public
	*/
	function addToDatabase () {
		if (! $this->isInDatabase ()) {
			$sql = "INSERT into {$this->db->getPrefix()}{$this->getTableName ()} (";
			$allOptions = $this->getAllOptions ();
			foreach ($allOptions as $name => $value) {
				$sql.= "$name,";
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )			
			$sql .= ' VALUES(';
			foreach ($allOptions as $name => $value) {
				$sql.= "'$value',";
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )	
			$q = $this->db->query ($sql);			
			if (! isError ($q)) {
				$this->ID = $this->db->latestInsertID ($q);
			} else {
				return $q;
			}
		} else {
			return "ERROR_DATABASEOBJECT_ALREADY_IN_DATABASE";
		}
	}
	
	function removeFromDatabase () {
		if ($this->ID >= 0) {
			$sql = "DELETE FROM {$this->db->getPrefix ()}{$this->getTableName ()} WHERE {$this->getIDName ()}='{$this->getID ()}'";
			$q = $this->db->query ($sql);
			if (! isError ($q)) {
				$this->ID = -1;
			} else {
				return $q;
			}
		} else {
			return "ERROR_DATABASEOBJECT_NOT_IN_DATABASE";
		}
	}
	
	function updateToDatabase () {
	}
	
	/**
	 * Gets the ID of the object.
	 *
	 * @retun (int)
	 * @public
	*/	
	function getID () {return $this->ID;}
	
	/*Protected functions*/
	
	/**
	 * Sets the fieldname of the ID
	 *
	 * @param $name (string) fieldname
	 * @protected
	*/
	function setIDName ($name) {
		$this->IDName = $name;
	}
	/**
	 * Returns the fieldname of the ID value
	 *
	 * @protected
	 * @return (string)
	*/
	function getIDName () {return $this->IDName;}
	
	/**
	 * Sets the tablename where the data should be stored (without dbprefix)
	 *
	 * @param $name (string) tablename (without prefix)
	 * @protected
	*/
	function setTableName ($name)	 {
		$this->tableName = $name;
	}
	
	/**
	 * Returns the tablename where the object is stored (without prefix)
	 *
	 * @protected
	 * @return (string)
	*/
	function getTableName () {return $this->tableName;}
	
	/**
	 * Set the creator of the object.
	 *
	 * @param $creator (object)
	 * @protected
	*/
	function setCreator (&$creator) {
		$this->creator = &$creator;
	}
	
	/**
	 * Returns the creator of the object
	 *
	 * @protected
	 * @return (object)
	*/
	function getCreator () {return $this->creator; }
	
	/**
	 * Set all extra options that should be stored in the db.
	 *
	 * @param $array (string array) the values are the options/field names
	 * @protected
	*/
	function setExtraOptions ($array) {
		$this->extraOptions = array ();
		foreach ($array as $option) {
			$this->extraOptions[$option] = null;
		}
	}
	
	/**
	 * Returns all extra options
	 *
	 * @protected
	 * @return (mixed array) the keys are the fieldname. Values are the current values.
	*/
	function getExtraOptions () {return $this->extraOptions;}
	
	/**
	 * Set all basic options (without ID) that should be stored in the DB.
	 *
	 * @param $array (string array) the values are the options/field names
	 * @protected
	*/
	function setBasicOptions ($array) {
		$this->basicOptions = array ();
		foreach ($array as $option) {
			$this->basicOptions[$option] = null;
		}
	}
	
	/**
	 * Returns all basic options
	 *
	 * @protected
	 * @return (mixed array) the keys are the fieldnames. Values are the current values.
	*/
	function getBasicOptions () {return $this->basicOptions;}
	
	/**
	 * Returns an array of all options. (without ID)
	 *
	 * @protected
	 * @return (mixed array) the keys are the fieldnames. Values are the current values.
	*/
	function getAllOptions () {return array_merge ($this->extraOptions, $this->basicOptions); }
	
	/**
	 * Empty initializer.
	 *	 
	 * @protected
	*/	
	function initEmpty () {
		$this->ID = -1;
		$allOptions = $this->getAllOptions ();
		foreach ($allOptions as $name => $value) {
			$this->setOption ($name, 'NOT SET');
		}
	}

}

?>