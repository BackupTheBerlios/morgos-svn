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

include_once ('core/compatible.functions.php');
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
		return new Error ('DATABASEMANAGER_MODULE_DOES_NOT_EXITS', $module);
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

/**
 * Base class for all databasemodules.
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
	 * Preventing a string from SQL injection
	*/		
	function escapeString ($value) {
		if (get_magic_quotes_gpc ()) {
			$value = stripslashes ($value);
		}
		return addslashes ($value);
	}
	
	/**
	 * Add a new field to the database
	 *
	 * @param $newField (object dbField)
	 * @param $dbName (string)
	*/
	function addNewField ($newField, $dbName) {
		$sql = "ALTER TABLE $dbName ADD {$newField->name} {$newField->type}";
		if (! $newField->canBeNull) {
			$sql .= " NOT NULL";
		}
		return $this->query ($sql);
	}
	
	/**
	 * Remove a field from a table
	 *
	 * @param $fieldName (string)
	 * @param $tableName (string)
	 * @public
	*/
	function removeField ($fieldName, $tableName) {
		$sql = "ALTER TABLE ".$tableName." DROP $fieldName";
		return $this->query ($sql);
		
	}
	
	function queryFile ($fileName) {
		$sql = file_get_contents ($fileName);
		$sql = str_replace ('{prefix}', $this->getPrefix (), $sql);
		$this->query ($sql);
	}
	
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

class dbField {
	var $name;
	var $type;
	var $canBeNull;
	var $value;
	
	function dbField ($name = null, $type = null) {
		$this->canBeNull = false;
		$this->type = $type;
		$this->name = $name;
		$this->value = null;
	}	
	
	function setValue ($newValue) {
		if ($this->getNonDBType () == 'string') {
			$this->value = strval ($newValue);
		} elseif ($this->getNonDBType () == 'int') {
			$this->value = (int) ($newValue);
		} else {
			$this->value = $newValue;
		}
	}
	
	function getValue () {
		return $this->value;
	}
	
	function getNonDBType () {
		if (substr ($this->type, 0, 3) == 'int') {
			return 'int';
		} else {
			return 'string';
		}
	}
	
	function getName () {return $this->name;}
}

/**
 * A databaseobject. All objects that are stored in the database must be derived from this one.
*/
class databaseObject {
	/**
	 * A mixed array of all basic options with their values
	 * @private 
	*/
	var $basicOptions;
	/**
	 * A mixed array of all extra options with their values
	 * @private
	*/
	var $extraOptions;
	/**
	 * The databasemodule object
	 * @protected
	*/
	var $db;
	/**
	 * The value of the ID. negative if not yet stored
	 * @private
	*/
	var $ID;
	/**
	 * The name of the database table for the ID
	 * @private
	*/
	var $IDName;
	/**
	 * The creator of the object
	 * @private
	*/
	var $creator;
	
	/**
	 * The constructor
	 *
	 * @param $db (databaseModule)
	 * @param $extraOptions (object option array)
	 * @param $basicOptions (object option array)
	 * @param $tableName (string)
	 * @param $IDName (int)
	 * @param $creator (object)
	*/
	function databaseObject (&$db, $extraOptions, $basicOptions, $tableName, $IDName, &$creator) {
		$this->db = &$db;
		$this->setExtraOptions ($extraOptions);
		$this->setBasicOptions ($basicOptions);
		$this->setTableName ($tableName);
		$this->setIDName ($IDName);
		$this->setCreator ($creator);
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
		if (! is_numeric ($ID)) {
			return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__,__LINE__);
		}
		$prefix = $this->db->getPrefix ();
		$tableName = $this->getTableName ();
		$IDName = $this->getIDName ();
		$sql = "SELECT * FROM $prefix$tableName WHERE $IDName='$ID'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			$this->initFromArray ($row);
			$this->setOption ('ID', $row[$this->getIDName ()]);
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
		foreach ($allOptions as $dbField) {
			$name = $dbField->name;
			if (array_key_exists ($name, $array)) {
				$this->setOption ($name, $array[$name]);
			} else {
				if (! $dbField->canBeNull) {
					$this->initEmpty ();
					return new Error ('DATABASEOBJECT_KEY_NOT_EXISTS', $name);
				}
			}
		}
	}
	
	/*Public functions*/
	
	/**
	 * Gets the current value of an option.
	 *
	 * @param $name (string) the name of the option
	 * @public
	 * @return (string) the value of the option
	*/
	function getOption ($name) {
		$allOptions = $this->getAllOptions ();
		if (array_key_exists ($name, $allOptions)) {
			return $allOptions[$name]->getValue ();
		} elseif ($name == 'ID') {
			return $this->ID;
		} else {
			return new Error ('DATABASEOBJECT_OPTION_DOES_NOT_EXISTS', $name);
		}
	}
	
	/**
	 * Sets the value of an option
	 * @warning all values are converted to a string.
	 *
	 * @param $name (string) the name of the option
	 * @param $value (mixed) the new value
	 * @public
	*/
	function setOption ($name, $value) {
		$allExtraOptions = $this->getExtraOptions ();
		$allBasicOptions = $this->getBasicOptions ();
		if (array_key_exists ($name, $allExtraOptions)) {
			$this->extraOptions[$name]->setValue ($value);
		} elseif (array_key_exists ($name, $allBasicOptions)) {
			$this->basicOptions[$name]->setValue ($value);
		} elseif ($name == 'ID') {
			$this->ID = (int) $value;
		} else {
			return new Error ('DATABASEOBJECT_OPTION_DOES_NOT_EXISTS', $name);
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
			$prefix = $this->db->getPrefix ();
			$tableName = $this->getTableName ();
			//$IDName = $this->getIDName ();
			$sql = "INSERT into $prefix$tableName (";
			$allOptions = $this->getAllOptions ();
			foreach ($allOptions as $dbField) {
				$name = $dbField->name;
				$sql.= "$name,";
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )			
			$sql .= ' VALUES (';
			foreach ($allOptions as $dbField) {
				$value = $this->db->escapeString ($dbField->getValue ());
				$sql.= "'$value',";
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )	
			$q = $this->db->query ($sql);			
			if (! isError ($q)) {
				$ID = $this->db->latestInsertID ($q);
				$this->setOption ('ID', $ID);
			} else {
				return $q;
			}
		} else {
			return new Error ('DATABASEOBJECT_ALREADY_IN_DATABASE');
		}
	}

	/**
	 * Removes the object form the database.
	 *
	 * @public
	*/	
	function removeFromDatabase () {
		if ($this->ID >= 0) {
			$prefix = $this->db->getPrefix ();
			$tableName = $this->getTableName ();
			$IDName = $this->getIDName ();
			$ID = $this->getID ();
			if (! is_numeric ($ID)) {
				return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__,__LINE__);
			}
			$sql = "DELETE FROM $prefix$tableName WHERE $IDName='$ID'";
			$q = $this->db->query ($sql);
			if (! isError ($q)) {
				$this->ID = -1;
			} else {
				return $q;
			}
		} else {
			return new Error ('DATABASEOBJECT_NOT_IN_DATABASE');
		}
	}
	
	function updateToDatabase () {
		$updates = '';
		foreach ($this->getAllOptions () as $opt) {
		 	if (! empty ($updates)) {
		 		$updates .= ', ';
		 	}
			$updates .= $opt->getName ();
			$updates .= '=';
			$updates .= '\''.$opt->getValue ().'\' '; 
		}
		$fullTableName = $this->getFullTableName (); 
		$IDName = $this->getIDName ();
		$ID = $this->getID ();
		$sql = "UPDATE $fullTableName SET $updates WHERE $IDName='$ID'";
		$a = $this->db->query ($sql);
		return $a;
	}
	
	/**
	 * Gets the ID of the object.
	 *
	 * @return (int)
	 * @public
	*/	
	function getID () {return $this->getOption ('ID');}
	
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
	 * Returns the full tablename where the object is stored (with prefix)
	 *
	 * @protected
	 * @return (string)
	*/
	function getFullTableName () {return $this->db->getPrefix () . $this->getTableName ();}
	
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
	 * @param $array (object dbField array)
	 * @protected
	*/
	function setExtraOptions ($array) {
		$this->extraOptions = $array;
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
	 * @param $array (object dbField)
	 * @protected
	*/
	function setBasicOptions ($array) {
		$this->basicOptions = $array;
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
			$this->setOption ($name, null);
		}
	}

}

?>
