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
 * The base layer for the creation of SQL for the sqlwrapper
 *
 * @ingroup database core sqlwrapperng
 * @since 0.4
 * @author Nathan Samson
*/

class DataSQLCreator {
	
	/**
	 * Returns the SQL for a table creation
	 *
	 * @public
	 * @return (string)
	*/
	function createTableSQL ($table) {
		$sql =  "CREATE TABLE {$table->getTableName ()} (";
		$fieldSQL = array ();
		foreach ($table->getFields () as $field) {
			$fieldSQL[] = $this->createFieldSQL ($field);
		}
		$sql .= implode (',', $fieldSQL);
		$sql .= ' )';
		return $sql;
	}
	
	/**
	 * Returns the SQL for a field definition for ex. (NAME type(length) NOT NULL)
	 *
	 * @protected
	 * @return (string)
	*/
	function createFieldSQL ($field) {
		switch ($field->getDataType ()) {
			case DATATYPE_STRING:
				$fieldType = 'varchar('.$field->getMaxLength ().')';
				break;
			case DATATYPE_INT:
				$fieldType = 
					'INT('.$field->getMaxBytes ().')';
				if (! $field->isSigned ()) {
					$fieldType .= ' UNSIGNED';
				}
				break;
			case DATATYPE_ENUM:
				$options = $field->getOptions ();
				foreach ($options as $key=>$option) {
					$options[$key] = '\''.addslashes ($option).'\'';
				}
				$fieldType = 'ENUM('.implode (',', $options).')';
		}
		return "{$field->getName ()} $fieldType NOT NULL";
	}
	
}