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
 * A compatability layer for the SQL creation of the SQLWrapper
 *
 * @ingroup database core sqlwrapperng
 * @since 0.4
 * @author Nathan Samson
*/

if (class_exists ('DataMySQLCompatLayer')) {
	return; // for one reason include_once doesn't work
} else {
class DataMySQLCompatLayer extends DataSQLCreator {

	function createFieldSQL ($field) {
		switch ($field->getDataType ()) {
			case DATATYPE_STRING:
				$fieldType = 'varchar('.$field->getMaxLength ().')';
				break;
			case DATATYPE_INT:
				switch ($field->getMaxBytes ()) {
					case 1:
						$fieldType = 'TINYINT';
						break;
					case 2:
						$fieldType = 'SMALLINT';
						break;
					case 3:
						$fieldType = 'MEDIUMINT';
						break;
					case 4:
						$fieldType = 'INT';
						break;
					case 8:
						$fieldType = 'BIGINT';
						break;
					default:
						$fieldType = 
							'INT('.$field->getMaxBytes ().')';
						break;
				}
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

}