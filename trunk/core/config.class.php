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
/** \file config.class.php
 * Manager of the config loader/saver.
 *
 * @since 0.2
 * @author Nathan Samson
*/

define ('STRING', 'String');
define ('BOOL', 'Bool');
define ('NUMERIC', 'Numeric');
define ('REAL', 'Real');

/**
 * Checks the type of a variable.
 *
 * @param $value (mixed) the var to check
 * @param $type (const type) the type
 * @return (true | error)
*/
function checkType ($value, $type) {
	switch ($type) {
		case STRING   : $r = is_string   ($value); break;
		case BOOL     : $r = is_bool     ($value); break;
		case NUMERIC  : $r = is_int      ($value); break;
		case REAL     : $r = (is_real ($value) or is_int ($value)); break;
		default       : return "ERROR_TYPE_NOT_RECOGNIZED"; 
	}
	
	if ($r === true) {
		return true;
	} else {
		return "ERROR_TYPE_MISMATCH_VALUE $type $value";
	}
}

class configItem {
	/**
	 * The name of the item.
	 * @private
	*/
	var $name;
	/**
	 * The type of the item
	 * @privete
	*/
	var $type;
	/**
	 * The default value, if no value is given
	 * @private
	*/
	var $defaultValue;
	/**
	 * The first not default value.
	 * @private
	*/
	var $initialValue;
	/**
	 * The current value
	 * @private
	*/
	var $currentValue;
	
	/**
	 * Constructor.
	 * @param $name (string)
	 * @param $type (const type)
	*/
	function configItem ($name, $type) {
		$this->name = $name;
		$this->type = $type;
		$this->initialValue = null;
		$this->defaultValue = null;
	}
	
	/**
	 * Sets the default value.
	 *
	 * @param $defaultValue (mixed)
	 * @public
	*/
	function setDefaultValue ($defaultValue) {
		$r = checkType ($defaultValue, $this->getType ());
		if (! isError ($r)) {
			$this->defaultValue = $defaultValue;
		} else {
			return $r;
		}
	}
	
	/**
	 * Sets the new value.
	 *
	 * @param $newValue (mixed)
	 * @public
	*/
	function setValue ($newValue) {
		$r = checkType ($newValue, $this->getType ());
		if (! isError ($r)) {
			if ($this->getInitialValue () === null) {
				$this->initialValue = $newValue;
			}
			$this->currentValue = $newValue;
		} else {
			return $r;
		}
	}
	
	/**
	 * Returns the current value.
	 *
	 * @public
	 * @return (mixed)
	*/
	function getCurrentValue () {
		if ($this->currentValue !== null) {
			return $this->currentValue;
		} else {
			return $this->defaultValue;
		}
	}
	
	/**
	 * Returns the initial value.
	 *
	 * @public
	 * @return (mixed)
	*/
	function getInitialValue () {
		return $this->initialValue;
	}
	
	/**
	 * Returns the type of the item.
	 *
	 * @public
	 * @return (cont type) 
	*/
	function getType () {
		return $this->type;
	}

}

class configurator {

	function configurator () {
	}
	
	function loadConfigFile () {
	}
	
	function loadConfigArray () {
	}
	
	function getOption () {
	}

	function setOption () {
	}
	
	function addOption () {
	}
	
	function saveConfigFile () {
	}

}

?>