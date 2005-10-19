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
	public function addConfigItem (string $configName, mixed $value, int $type, string $password) {
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
	public function addConfigItemFromArray (array $array, string $arrayKey, string $configName,int $type, string $password = NULL) {
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
	public function getConfigItem (string $configName, int $type = TYPE_FLEXIBLE, string $password = NULL) {
	}
}
?>