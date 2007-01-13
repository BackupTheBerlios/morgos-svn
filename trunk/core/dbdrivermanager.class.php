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
/** \file dbdrivermanager.class.php
 * File that take care of the database drivers
 *
 * @ingroup core database
 * @since 0.4
 * @author Nathan Samson
*/

/**
 * Manage database
 * @defgroup database Database
*/

// required for the used scandir
include_once ('core/compatible.functions.php');

global $_DBDriverList; // set global can be included in a function
$_DBDriverList = array ();

/**
 * The driver manager. All functions should be called statically
 *
 * @since 0.4
*/
class DatabaseDriverManager {
	
	/**
	 * Searches all db drivers in a directory.
	 *
	 * @since 0.4
	 * @param $dirName (string) the name of the dir.
	*/
	function findAllDriversInDirectory ($dirName) {
		global $_DBDriverList;
		if (is_dir ($dirName)) {
			foreach (scandir ($dirName) as $file) {
				$fullName = $dirName.'/'.$file;
				if (is_file ($fullName)) {
					include ($fullName);
				}
			}
		} else {
			return new Error ('PARAM_ISNOT_A_DIR', $dirName);
		}
	}
	
	/**
	 * Returns all installed drivers
	 *
	 * @since 0.4
	 * @return (string array)
	*/
	function getAllInstalledDrivers () {
		global $_DBDriverList;
		$drivers = array ();
		foreach ($_DBDriverList as $key => $driver) {
			if ($driver['canRun']) {
				$drivers[] = $key; 
			}
		}
		return $drivers;
	}
	
	/**
	 * Loads and returns a database driver.
	 *
	 * @since 0.4
	 * @return (DatabaseDriver)
	 * @param $driverName (string) the name of the driver
	*/
	function loadDriver ($driverName) {
		global $_DBDriverList;
		if (DatabaseDriverManager::canDriverRun ($driverName)) {
			return new $_DBDriverList[$driverName]['className']();
		} else {
			return new Error ('INVALID_DATABASEDRIVER', $driverName);
		}
	}
	
	/**
	 * Returns if the driver can run correctly
	 *
	 * @since 0.4
	 * @return bool
	 * @param $driverName (string)
	*/
	function canDriverRun ($driverName) {
		global $_DBDriverList;
		if (DatabaseDriverManager::isDriverInstalled ($driverName)) {
			return $_DBDriverList[$driverName]['canRun'];
		} else {
			return false;
		}
	}
	
	/**
	 * Returns if the driver is found
	 *
	 * @since 0.4
	 * @return bool
	 * @param $driverName (string)
	*/
	function isDriverInstalled ($driverName) {
		global $_DBDriverList;
		return array_key_exists ($driverName, $_DBDriverList);
	}
	
	/**
	 * Adds the driver with name $driverName to the driver list.
	 *
	 * @since 0.4
	 * @param $driverName (string)
	 * @param $driverClass (string) the name of the class of the driver
	 * @param $canRun (bool)
	*/
	function addDriver ($driverName, $driverClass, $canRun) {
		global $_DBDriverList;
		if (! DatabaseDriverManager::isDriverInstalled ($driverName)) {
			if (class_exists ($driverClass)) {
				$_DBDriverList[$driverName] = array (
					'className' => $driverClass,
					'canRun'    => $canRun );
			} else {
				return new Error ('DBDRIVERCLASS_NOTFOUND', 
					$driverName, $driverClass);
			}
		} else {
			return new Error ('DBDRIVER_ALREADY_ADDED', $driverName);
		}
	} 
}

/**
 * Base class for all database drivers.
 *
 * @ingroup core database
 * @since 0.2
 * @since 0.4 Renamed from databaseActions to DatabaseDriver
 * @author Nathan Samson
*/
class DatabaseDriver {

	/**
	 * The prefix for databasetables
	 * @protected
	*/
	var $_prefix;
	/**
	 * The type of the database. Should be uniqe for each database. 
	 *  Examples: MySQL, MySQLi, PgSQL, ...
	 * @protected
	*/
	var $_type;
	
	/**
	 * Set the database tablenames prefix.
	 *
	 * @param $prefix (string)
	 * @public
	*/
	function setPrefix ($prefix) {
		$this->_prefix = $prefix;
	}

	/**
	 * Returns the prefix of the tablenames
	 *
	 * @public
	 * @return (string)
	*/
	function getPrefix () {return $this->_prefix;}
	
	/**
	 * Sets the type of the database
	 *
	 * @param $type (string)
	 * @protected
	*/
	function setType ($type) {
		$this->_type = $type;
	}
	
	/**
	 * Returns the databasetype
	 *
	 * @return (string)
	 * @public
	*/
	function getType () {return $this->_type;}
}

?>