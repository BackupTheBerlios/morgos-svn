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
/** 
 * File that defines the sqlwrapper
 *
 * @ingroup core database sqlwrapper
 * @since 0.4
 * @author Nathan Samson
*/

/**
 * The SqlWrapper makes that developers shouldn't write any sql.
 *
 * @defgroup sqlwrapper SQLWrapper
*/

include_once ('core/sqlwrapperng/datarow.class.php');
include_once ('core/sqlwrapperng/datafield.class.php');

/**
 * A data table defines a table in a database. You can request a datatable for rows,
 * adding fields to the table and so on.
 * It is only used as a base type for the derived DataTables (users, newsitems, ...)
 * 
 * @since 0.4
*/
class DataTable {
	var $_dbDriver;
	var $_tableName;
	var $_baseFields;
	var $_optionalFields;
	var $_sqlCreator;
	
	/**
	 * The constructor of the DataTable.
	 *
	 * @param $tableName (string)
	 * @param $fields (DataField array) the basic fields fot the table
	 * @param &$dbDriver (DBDriver) the dbdriver which connects to the database
	*/
	function DataTable ($tableName, $fields, &$dbDriver) {
		$this->_dbDriver = &$dbDriver;
		$this->_tableName = $tableName;		
		$this->_baseFields = array ();
		$this->_optionalFields = array ();
		foreach ($fields as $field) {
			$this->_baseFields[$field->getName ()] = $field;
		}
		$this->_sqlCreator = $this->_dbDriver->getSQLCreator ();
	}
	
	/**
	 * This creates the database
	 *
	 * @public
	*/
	function createTable () {
		$this->_dbDriver->query ($this->_sqlCreator->CreateTableSQL ($this));
	}
	
	/**
	 * Return all fields for this table
	 *
	 * @public
	 * @return (DataField array)
	*/
	function getFields () {
		return array_merge ($this->_baseFields, $this->_optionalFields);
	}
	
	/**
	 * Returns the name of the table
	 *
	 * @public
	 * @return (string)
	*/
	function getTableName () {
		return $this->_tableName;
	}
}

?>