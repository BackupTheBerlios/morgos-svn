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
		default       : return new Error ('TYPE_NOT_RECOGNIZED'); 
	}
	
	if ($r === true) {
		return true;
	} else {
		return new Error ('TYPE_MISMATCH_VALUE', $type, $value);
	}
}

/**
 * Guess the type of a variable.
 *
 * @param $value (mixed) the var
 * @return (const type)
*/
function guessType ($value) {
	if (is_bool ($value)) {
		return BOOL;
	} elseif (is_real ($value)) {
		return REAL;
	} elseif (is_int ($value)) {
		return NUMERIC;
	} else {
		return STRING;
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
	 * @private
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

	/**
	 * Returns the name of the item.
	 *
	 * @public
	 * @return (string)
	*/
	function getName () {
		return $this->name;
	}
}

class configurator {
	/**
	 * All config items that this manager stores.
	 * @private
	*/
	var $allConfigItems;

	function configurator () {
		$this->allConfigItems = array ();
	}
	
	/**
	 * Loads all items from a config file.
	 *
	 * @param $fileName (string) The filename.
	 * @public
	*/
	function loadConfigFile ($fileName) {
		if (file_exists ($fileName)) {
			if (is_readable ($fileName)) {
				$configItems = array ();
				include ($fileName);
				$this->loadConfigArray ($configItems);
			} else {
				return new Error ('CONFIGURATOR_CANT_READ_FILE', $fileName);
			}
		} else {
			return new Error ('CONFIGURATOR_CANT_READ_FILE', $fileName);
		}
	}
	
	/**
	 * Loads all items from an array
	 *
	 * @param $array (mixed array)
	 * @public
	*/
	function loadConfigArray ($array) {
		foreach ($array as $name => $value) {
			$type = guessType ($value);
			if (! isError ($type)) {
				$item = new configItem ($name, $type);
				$item->setValue ($value);
				$this->addOption ($item);
			} else {
				return $type;
			}
		}
	}
	
	/**
	 * Returns an item with type string.
	 *
	 * @param $name (string)
	 * @public
	 * @return (string)
	*/
	function getStringItem ($name) {
		return $this->getItemValue ($name, STRING);
	}
	
	/**
	 * Returns an item with type bool.
	 *
	 * @param $name (string)
	 * @public
	 * @return (bool)
	*/
	function getBoolItem ($name) {
		return $this->getItemValue ($name, BOOL);
	}	
	
	/**
	 * Returns an item with type numeric.
	 *
	 * @param $name (string)
	 * @public
	 * @return (integer)
	*/
	function getNumericItem ($name) {
		return $this->getItemValue ($name, NUMERIC);
	}
	
	/**
	 * Returns an item with type real.
	 *
	 * @param $name (string)
	 * @public
	 * @return (real)
	*/
	function getRealItem ($name) {
		return $this->getItemValue ($name, REAL);
	}
	
	/**
	 * Adds an option.
	 *
	 * @param $option (object configItem)
	 * @public
	*/
	function addOption ($option) {
		if (! $this->existsItem ($option->getName ())) {
			$fullName = '/'.$option->getType () . $option->getName ();
			$this->allConfigItems[$fullName] = $option;
		} else {
			return new Error ('CONFIGURATOR_OPTION_EXISTS', $option->getName ());
		}
	}

	/**
	 * Returns if an item exists
	 *
	 * @param $name (string) the name of the option
	*/
	function existsItem ($name) {
		if ($this->existsItemStrict ($name, STRING)) {
			return true;
		} elseif ($this->existsItemStrict ($name, STRING)) {
			return true;
		} elseif ($this->existsItemStrict ($name, STRING)) {
			return true;
		} elseif ($this->existsItemStrict ($name, STRING)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns an item with name and type.
	 *
	 * @param $name (string)
	 * @param $type (const type)
	 * @public
	 * @return (mixed)
	*/
	function getItem ($name, $type) {
		if ($this->existsItemStrict ($name, $type)) {
			$fullName = $fullName = '/'.$type.$name;
			return $this->allConfigItems[$fullName];
		} else {
			return new Error ('CONFIGURATOR_ITEM_DOESNT_EXISTS', $name);
		}
	}
	
	/**
	 * Returns an item value with name and type.
	 *
	 * @param $name (string)
	 * @param $type (const type)
	 * @private
	 * @return (mixed)
	*/
	function getItemValue ($name, $type) {
		$item = $this->getItem ($name, $type);
		if (! isError ($item)) {
			return $item->getCurrentValue ();
		} else {
			return $item;
		}
	}
	
	function existsItemStrict ($name, $type) {
		if (array_key_exists ('/'.$type.$name, $this->allConfigItems)) {
			return true;
		} else {
			return false;
		}
	}
	
}

?>