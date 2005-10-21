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
/**
 * File that take care of the config subsystem
 *
 * @package config
 * @author Nathan Samson
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

class config {
	/**
	 * the configtree is an array, each path or configItem has a place in the array
	 * the content of an item has different items
	 * 1) the value
	 * 2) the type
	 * 3) maybe a password
	*/
	private $configTree;

	function config () {
		$this->__construct ();
	}

	function __construct () {
		$this->configTree = array ();
	}
	
	/**
	 * adds an item in the config tree
	 *
	 * @param string $configName the complete name of the config where in must be put	
	 *	in the configTree, you can create paths divided with '/'
	 * @param mixed $value the value of the configitem
	 * @param int $type the type of the value (TYPE_NUMERIC, TYPE_STRING, TYPE_BOOL, TYPE_FLEXIBLE)
	 * @param string $password if you wants to protect the value of this configItem you 
	 	can give your password here, the standard is NULL (not protected)
	 * @return bool
	*/
	/*public*/ function addConfigItem ( $configName,  $value,  $type,  $password) {
		$dirs = explode ('/',$configName);
		$curPath = NULL;
		foreach ($dirs as $dir) {
			$curPath .= '/' . $dir;
			if ($this->exists ($curPath)) {
				if (! $this->isDir ($curPath)) {
					trigger_error ('Problem with config path',E_USER_ERROR);
				}
			} else {
				$this->configTree[$curPath] = 'PATH';
			}
		}
		if ($this->convertType ($value, $type)) {
			$this->configtree[$curPath] = array ('value' => $value,'type' => $type,'password' => $password);
		} else {
			trigger_error ('Type is not correct', E_USER_ERROR);
			trigger_error ('Type is: ' . $this->typeToString ($this->isType ($value)). ', needs to be: ' . $this->typeToString ($type),E_USER_NOTICE);
		}
		return true;
	}
	
	/**
	 * adds an item in the config tree, from an array
	 *
	 * @param array $array the array where the current value lives (for ex. $_COOKIE)
	 * @param string $arrayKey the key in the array where the current value lives
	 * @param string $configName the complete name of the config where in must be put	
	 *	in the configTree, you can create paths divided with '/'
	 * @param int $type the type of the value (TYPE_NUMERIC, TYPE_STRING, TYPE_BOOL, TYPE_FLEXIBLE)
	 * @param string $password if you wants to protect the value of this configItem you can give your password here, the standard is NULL (not protected)
	 * @return bool
	*/
	/*public*/ function addConfigItemFromArray ( $array,  $arrayKey,  $configName, $type,  $password = NULL) {
		return $this->addConfigItem ($configName,$array[$arrayKey],$type,$password);
	}
	
	/**
	 * gets the value from the configtree
	 
	 * @param string $configName the complete name of the config where in must be put	
	 *	in the configTree, you can create paths divided with '/'
	 * @param int $type the type of the value (TYPE_NUMERIC, TYPE_STRING, TYPE_BOOL, TYPE_FLEXIBLE)
	 * @param string $password if you wants to protect the value of this configItem you 
	 	can give your password here, the standard is NULL (not protected)
	 * @return mixed
	*/
	/*public*/ function getConfigItem ( $configName,  $type = TYPE_FLEXIBLE,  $password = NULL) {
	}
	
	/*private | public*/ function exists ($configName) {
		if ($this->configTree[$configName] != NULL) {
			return true;
		} else {
			return false;
		}
	}
	
	/*private | public*/ function isDir ($configName) {
		if ($this->configTree[$configName] == 'PATH') {
			return true;
		} else {
			return false;
		}
	}
}
?>
