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
/**
 * File that take care of the main UI layer, extensionhandling and HTML output
 *
 * @package ui
 * @author Nathan Samson
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
include_once ('database.class.php');
/**
 * class that take care of the main UI layer, extensionhandling and HTML output
 *
 * @author Nathan Samson
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class UIManager {
	/*private $DBManager;
	private $genDB;*/

	function UIManager () {
		$this->__construct ();
	}

	function __construct () {
		$this->DBManager = new genericDatabase ();
		$this->genDB = $this->DBManager->load ('MySQL 4.x');
	}
	
	/**
	 * returns the generic DB class
	 *
	 * @return class
	*/
	/*public*/ function getGenericDB () {
		return $this->genDB;
	}
}

?>
