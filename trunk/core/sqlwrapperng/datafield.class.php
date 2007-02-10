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
 * File that defines a datafield
 *
 * @ingroup core database sqlwrapper
 * @since 0.4
 * @author Nathan Samson
*/

define ('DATATYPE_STRING', 1);
define ('DATATYPE_TEXT', 2);
define ('DATATYPE_INT', 3);
define ('DATATYPE_REAL', 4);
define ('DATATYPE_ENUM', 5);

/**
 * The data field class. It defines a field with its name and type.
 * It is only used as a base class for the real types (String, text, int, ...)
 * @since 0.4 
*/
class DataField {
	var $_dataType;
	var $_fieldName;
	var $_table;
	
	/**
	 * The constructor
	 *
	 * @param $name (string) the field name
	 * @param $table (DataTable) the DataTable
	 * @param $type (DATATYPE_ENUM) the type
	*/
	function DataField ($name, &$table, $type) {
		$this->_dataType = $type;
		$this->_table = &$table;
		$this->_fieldName = $name;
	}	
	
	/**
	 * Returns if the value is valid for this field.
	 *
	 * @param $value (string) The value to check
	 * @return bool
	 * @public
	*/
	function isValidValue ($value) {
		return true;
	}
	
	/**
	 * Returns the type
	 *
	 * @return (DATATYPE_ENUL)
	 * @public
	*/
	function getDataType () {
		return $this->_dataType;
	}
	
	/**
	 * Returns the name of the field
	 *
	 * @return (string)
	 * @public
	*/
	function getName () {
		return $this->_fieldName;
	}
}

class DataFieldString extends DataField {
	var $_maxLength;

	/**
	 * The constructor
	 *
	 * @param $name (string) the field name
	 * @param $table (DataTable) the DataTable
	 * @param $maxLength (int) the maximal length of the string. 
	*/
	function DataFieldString ($name, &$table, $maxLength = 255) {
		parent::DataField ($name, $table, DATATYPE_STRING);
		if ($maxLength < 0) {
			$maxLength = 255;
		}
		$this->_maxLength = $maxLength;
	}
	
	function isValidValue ($value) {
		return (strlen ($value) <= $this->_maxLength);
	}
	
	/**
	 * Returns the maxlength of the string
	 *	 
	 * @public
	 * @return (string)
	*/
	function getMaxLength () {
		return $this->_maxLength;
	}
}

class DataFieldInt extends DataField {
	var $_maxBytes;
	var $_signed;
	var $_maxValue;
	var $_minValue;

	/**
	 * The constructor
	 *
	 * @param $name (string) the field name
	 * @param $table (DataTable) the DataTable
	 * @param $maxBytes (int) the maximal number of bits. 
	 *	Default , will reach from -2147483648 to  2147483647 (signed)
	 * @param $signed (bool) if the value is signed (default true)
	 * @warning bits higher than 4 are converted to floats (on some systems). 
	 * This will result in inpredictable results (of the isValidValue func)
	*/
	function DataFieldInt ($name, &$table, $maxBytes = 4, $signed = true) {
		parent::DataField ($name, $table, DATATYPE_INT);
		$this->_maxBytes = $maxBytes;
		$this->_signed = $signed;
		if ($this->_signed) {
			$this->_maxValue = (pow (2, 8*$this->_maxBytes)/2)-1;
			$this->_minValue = -$this->_maxValue-1;
		} else {
			$this->_maxValue = pow (2, 8*$this->_maxBytes)-1;
			$this->_minValue = 0;
		}
	}
	
	function isValidValue ($value) {
		if (is_int ($value) || is_float ($value)) {
			if ($value <= $this->_maxValue && $value >= $this->_minValue) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the maxbytes for the int
	 *
	 * @public
	 * @return (int)
	*/
	function getMaxBytes () {
		return $this->_maxBytes;
	}
	
	/**
	 * Returns if the int is signed
	 *
	 * @public
	 * @return (bool)
	*/
	function isSigned () {
		return $this->_signed;
	}
}

class DataFieldEnum extends DataField {
	var $_optionArrays;

	/**
	 * The constructor
	 *
	 * @param $name (string) the field name
	 * @param $table (DataTable) the DataTable
	 * @param $optionArrays (string array) all options
	*/
	function DataFieldEnum ($name, &$table, $optionArrays) {
		parent::DataField ($name, $table, DATATYPE_ENUM);
		$this->_optionArrays = $optionArrays;
	}
	
	function isValidValue ($value) {
		return (in_array ($value, $this->_optionArrays));
	}
	
	/**
	 * Returns the options for the enum
	 *
	 * @public
	 * @return (string array)
	*/
	function getOptions () {
		return $this->_optionArrays;
	}
}