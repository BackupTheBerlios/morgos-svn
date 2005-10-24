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
 * \todo implement isType
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
	 * \return bool
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
					trigger_error ('Problem with config path',E_USER_ERROR);
				}
			} else {
				$this->configTree[$curPath] = 'PATH';
			}
		}
		if ($this->isType ($value) == $type) {
			$this->configTree[$configName] = array ('value' => $value,'type' => $type,'password' => $password);
		} else {
			trigger_error ('Type is not correct', E_USER_ERROR);
			trigger_error ('Type is: ' . $this->typeToString ($this->isType ($value)). ', needs to be: ' . $this->typeToString ($type),E_USER_NOTICE);
		}
		return true;
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
	 * \return bool
	*/
	/*public*/ function addConfigItemFromArray ( $array,  $arrayKey,  $configName, $type,  $password = NULL) {
		return $this->addConfigItem ($configName,$array[$arrayKey],$type,$password);
	}
	
	/** \fn getConfigItem ($configName,$type = TYPE_FLEXIBLE,$password = NULL)
	 * gets the value from the configtree.
	 *
	 * \param $configName (string) the complete name of the config where in must be put	
	 *	in the configTree, you can create paths divided with '/'
	 * \param $type (int) the type of the value (TYPE_NUMERIC, TYPE_STRING, TYPE_BOOL, TYPE_FLEXIBLE)
	 * \param $password (string) if you wants to protect the value of this configItem you 
	 	can give your password here, the standard is NULL (not protected)
	 * \return mixed
	*/
	/*public*/ function getConfigItem ( $configName,  $type = TYPE_FLEXIBLE,  $password = NULL) {
		if ($this->exists ($configName)) {
			if (($this->configTree[$configName]['type'] == $type) or ($type = TYPE_FLEXIBLE)) {
				return $this->configTree[$configName]['value'];
			} else {
				echo $this->configTree[$configName];
				trigger_error ('Type is: ' . $this->typeToString ($this->configTree[$configName]['type']). ', needs to be: ' . $this->typeToString ($type),E_USER_NOTICE);
				trigger_error ('Type is not correct', E_USER_ERROR);
			}
		} else {
			trigger_error ('Config doesn\' exists',E_USER_ERROR);
		}
	}
	
	/** \fn exists ($configName)
	 * Checks if a config item exists.
	 *
	 * \param configName (string) the name of the config item
	 * \return bool
	*/
	/*private | public*/ function exists ($configName) {
		if ($this->configTree[$configName] != NULL) {
			return true;
		} else {
			return false;
		}
	}
	
	/** \fn isDir ($configName)
	 * Checks if a config item is a dir. If it doesn't exists it returns false;
	 *
	 * \param configName (string) the name of the config item
	 * \return bool
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
				trigger_error ('Type is not recognized',E_USER_NOTICE);
				return false;
			}
		}
		return true;
	}
	
	/** \fn typeToString ($type)
	 * converts a type integer into a string.
	 *
	 * \param $type (mixed) the value
	 * \private
	 * \return string
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
	 * \return int the type of the value
	*/
	/*private*/ function isType ($value) {
		return TYPE_STRING;
	}
}
?>
