<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005 MorgOS
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
define ('TYPE_STRING',1);
define ('TYPE_NUMERIC',2);
define ('TYPE_BOOL',3);
define ('TYPE_FLEXIBLE',0);
define ('TYPE_GUESS',-1); // use this only for addConfigItemsFromFile ()
/** \file config.class.php
 * File that take care of the config subsystem
 *
 * \author Nathan Samson
*/

/** \class config
 * class that take care of the config implementation.
 *
 * \version 0.1svn
 * \author Nathan Samson
 * \todo test this class, especially isType ()
*/
class config {
	/** \var $configTree
	 * the configtree is an array, each path or configItem has a place in the array
	 * the content of an item has different items
	 *  \li the value
	 *  \li the type
	 *  \li maybe a password
	 * \private
	*/
	var $configTree;

	function config () {
		$this->__construct ();
	}

	function __construct () {
		$this->configTree = array ();
	}
	
	/** \fn addConfigItem ($configName,$value,$type,$password = NULL)
	 * adds an item in the config tree
	 *
	 * \param $configName (string) the complete name of the config where in must be put	
	 *	in the configTree, you can create paths divided with '/'
	 * \param $value (mixed) the value of the configitem
	 * \param $type (int) the type of the value (TYPE_NUMERIC, TYPE_STRING, TYPE_BOOL, TYPE_FLEXIBLE)
	 * \param $password (string) if you wants to protect the value of this configItem you 
	 	can give your password here, the standard is NULL (not protected)
	*/
	/*public*/ function addConfigItem ( $configName,  $value,  $type,  $password = NULL) {
		$dirs = explode ('/',$configName);
		$curPath = NULL;
		foreach ($dirs as $dir) {
			if ($dir == NULL) {
				// normally this is only when you have double '/' or at the beginning of the configName
				continue;
			}
			$curPath .= '/' . $dir;
			if ($this->exists ($curPath)) {
				if (! $this->isDir ($curPath)) {
					trigger_error ('INTERNAL_ERROR: Problem with config path');
				}
				break;
			} else {
				$this->configTree[$curPath] = 'PATH';
			}
		}
		if ($this->isType ($value) == $type) {
			$this->configTree[$configName] = array ('value' => $value,'type' => $type,'password' => $password);
		} else {
			trigger_error ('DEBUG: type is: ' . $this->valueToString ($value) . ' needs to be ' . $this->valueToString ($value));
			trigger_error ('INTERNAL_ERROR: type is not correct');
		}
	}
	
	/** \fn addConfigItemFromArray ($array,$arrayKey,$configName,$type,  $password = NULL)
	 * adds an item in the config tree, from an array.
	 *
	 * \param $array (mixed array) the array where the current value lives (for ex. $_COOKIE)
	 * \param $arrayKey (string) the key in the array where the current value lives
	 * \param $configName (string) the complete name of the config where in must be put	
	 *	in the configTree, you can create paths divided with '/'
	 * \param $type (int) the type of the value (TYPE_NUMERIC, TYPE_STRING, TYPE_BOOL, TYPE_FLEXIBLE)
	 * \param $password (string) if you wants to protect the value of this configItem you can give your password here, the standard is NULL (not protected)
	*/
	/*public*/ function addConfigItemFromArray ( $array,  $arrayKey,  $configName, $type,  $password = NULL) {
		$this->addConfigItem ($configName,$array[$arrayKey],$type,$password);
	}
	
	/** \fn addConfigItemsFromFile ($file,$globalType = -1,$globalPassword = NULL)
	 * adds all items in from a configfile. The configfile is based on an array with the name config.
	 * the keys of that arrays are the pathnames of that item, the value is the value of the item. The type is
	 * guessed (never TYPE_FLEXIBLE), or if you have set the $type parameter it is always that type (can be TYPE_FLEXIBLE)
	 *
	 * \todo implments a method to have a different type for each var, without guessing it
	 * \param $file (string) the filename
	 * \param $type (integer) which type all vars needs to be, if not set it is guessed for each type
	 * \param $password (string) protect all items with this password, standard is not protected
	*/
	/*public*/ function addConfigItemsFromFile ($file,$globalType = -1,$globalPassword = NULL) {
		include ($file);
		foreach ($config as $path => $item) {
			if ($globalType = TYPE_GUESS) {
				$type = $this->isType ($item);
			} else {
				$type = $globalType;
			}
			$this->addConfigItem ($path, $item, $type, $globalPassword);
		}
	}
	
	/** \fn getConfigItem ($configName,$type = TYPE_FLEXIBLE,$password = NULL)
	 * gets the value from the configtree.
	 *
	 * \param $configName (string) the complete name of the config where in must be put	
	 *	in the configTree, you can create paths divided with '/'
	 * \param $type (int) the type of the value (TYPE_NUMERIC, TYPE_STRING, TYPE_BOOL, TYPE_FLEXIBLE)
	 * \param $password (string) if you wants to protect the value of this configItem you 
	 	can give your password here, the standard is NULL (not protected)
	 * \return (mixed)
	*/
	/*public*/ function getConfigItem ( $configName,  $type = TYPE_FLEXIBLE,  $password = NULL) {
		if ($this->exists ($configName)) {
			if (($this->configTree[$configName]['type'] == $type) or ($type = TYPE_FLEXIBLE)) {
				return $this->configTree[$configName]['value'];
			} else {
				$value = $this->configTree[$configName]['value'];
			 	trigger_error ('DEBUG: type is: ' . $this->valueToString ($value) . ' needs to be ' . $this->valueToString ($value));
				trigger_error ('INTERNAL_ERROR: type is not correct');
			}
		} else {
			trigger_error ('DEBUG: configname is: ' . $configName);
			trigger_error ('INTERNAL_ERROR: configname does not exists');
		}
	}
	
	/** \fn changeValueConfigItem ($configName, $newValue, $password = NULL)
	 * changes the value into a new value
	 * \warning This function doesn't check on types
	 *
	 * \param $configName (string) the config item you want to change
	 * \param $newValue (mixed) the new value of the configitem
	 * \param $passWord (string) the password of the configitem, fill not in if the configitem is not protected
	 * \bug If the type of $newValue is not the same as the original the configTree uses the old one, 
	 *  we assume that you don't want to change the type of the value
	*/
	function changeValueConfigItem ($configName, $newValue, $password = NULL) {
		if ($this->exists ($configName)) {
			if (! $this->isDir ($configName)) {
				if ($this->configTree[$configName]['password'] == $password) {
					$this->configTree[$configName]['value'] = $newValue;
			 	} else {
			 		trigger_error ('DEBUG: configname is: ' . $configName);
					trigger_error ('INTERNAL_ERROR: configname does not exists');
				}
			} else {
				trigger_error ('DEBUG: configname is : ' . $configName);
				trigger_error ('INTERNAL_ERROR: configname yet exists');
			}
		}
	}
	
	/** \fn exists ($configName)
	 * Checks if a config item exists.
	 *
	 * \param configName (string) the name of the config item
	 * \return (bool)
	*/
	/*private | public*/ function exists ($configName) {
		if (array_key_exists ($configName, $this->configTree)) {
			return true;
		} else {
			return false;
		}
	}
	
	/** \fn isDir ($configName)
	 * Checks if a config item is a dir. If it doesn't exists it returns false;
	 *
	 * \param configName (string) the name of the config item
	 * \return (bool)
	*/
	/*private | public*/ function isDir ($configName) {
		if ($this->configTree[$configName] == 'PATH') {
			return true;
		} else {
			return false;
		}
	}
	
		
	/** \fn convertType (&$value,$type)
	 * converts the value into type $type. If $value could not be converted it returns false
	 * \param $value (mixed) the value to convert
	 * \param $type (int) the type to convert to
	 * \return bool false if convertion is not possible
	 * \private
	*/
	/*private*/ function convertType ( &$value,  $type) {
		if ($type != TYPE_FLEXIBLE) {
			if ($type == TYPE_STRING) {
				$value = (string) $value;
			} elseif ($type == TYPE_BOOL) {
				$value = (boolean) $value;
			} elseif ($type == TYPE_NUMERIC) {
				$value = (integer) $vaue;
			} else {
				trigger_error ('DEBUG: Type is: ' . $type);
				trigger_error ('INTERNAL_ERROR: Type is not correct');
			}
		}
	}
	
	/** \fn typeToString ($type)
	 * converts a type integer into a string.
	 *
	 * \param $type (mixed) the value
	 * \private
	 * \return (string)
	*/
	/*private*/ function typeToString ($type) {
		if ($type == TYPE_STRING) {
			return 'string';
		} elseif ($type == TYPE_BOOL) {
			return 'bool';
		} elseif ($type == TYPE_NUMERIC) {
			return 'numeric';
		} else {
			return 'flexible';
		}
	}
	
	/** \fn isType ($value)
	 * see for a value what the type is.
	 *
	 * \param $value (mixed)
	 * \private
	 * \return (int) the type of the value
	*/
	/*private*/ function isType ($value) {
		if (is_bool ($value)) {
			return TYPE_BOOL;
		} elseif (is_numeric ($value)) {
			return TYPE_NUMERIC;
		} elseif (is_string ($value)) {
			return TYPE_STRING;
		} else {
			return TYPE_FLEXIBLE;
		}
	}
}
?>
