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
/** \file uimanager.class.php
 * File that take care of the main UI layer, extensionhandling and HTML output
 *
 * \namespace ui
 * \author Nathan Samson
*/
include_once ('core/database.class.php');
include_once ('core/user.class.php');
include_once ('core/config.class.php');
/** \class UIManager
 * class that take care of the main UI layer, extensionhandling and HTML output.
 *
 * \author Nathan Samson
 * \version 0.1svn
*/
class UIManager {
	/*private $DBManager;
	private $genDB;
	private $config
	private $user*/

	function UIManager () {
		$this->__construct ();
	}

	function __construct () {
		$this->config = new config ();
		$this->config->addConfigItem ('/database/type','MySQL 4.x',TYPE_STRING);
		$this->DBManager = new genericDatabase ();
		$this->genDB = $this->DBManager->load ($this->config->getConfigItem ('/database/type',TYPE_STRING));
		$this->user = new user ($this->genDB);
	}
	
	/** \fn getGenericDB ()
	 * returns the generic DB class
	 *
	 * @return class
	*/
	/*public*/ function &getGenericDB () {
		return $this->genDB;
	}
	
	/** \fn getConfigClass ()
	 * returns the configclass
	 *
	 * @return class
	*/
	/*public*/ function &getConfigClass () {
		return $this->config;
	}
}

?>
