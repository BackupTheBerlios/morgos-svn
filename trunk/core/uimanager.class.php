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
 * \author Nathan Samson
*/
include_once ('core/database.class.php');
include_once ('core/user.class.php');
include_once ('core/config.class.php');
define ('MORGOS_VERSION','0.1');
/** \class UIManager
 * class that take care of the main UI layer, extensionhandling and HTML output.
 *
 * \author Nathan Samson
 * \version 0.1svn
 * \bug not compatible with PHP lower than 4.1  (use of version_compare)
 * \bug not compatible with PHP 4.0.4 and lower (use of array_search)
 * \bug not compatible with PHP 4.0.0 and lower (use of trigger_error)
 * \todo change the dir in __construct to install in place of DOT install
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
		if (! is_dir ('.install/')) {
			$this->config = new config ();
			$this->config->addConfigItem ('/database/type','MySQL 4.x', TYPE_STRING);
			$this->DBManager = new genericDatabase ();
			$this->genDB = $this->DBManager->load ($this->config->getConfigItem ('/database/type', TYPE_STRING));
			$this->user = new user ($this->genDB);	

			$this->loadSkin ('MorgOS Default');
			$this->loadPage ('index.html');
		} else {
			trigger_error ('Remove the dir install before you continue', E_USER_ERROR);
		}
	}
	
	/** \fn getGenericDB ()
	 * returns the generic DB class
	 *
	 * \return class
	*/
	/*public*/ function &getGenericDB () {
		return $this->genDB;
	}
	
	/** \fn getConfigClass ()
	 * returns the configclass
	 *
	 * \return class
	*/
	/*public*/ function &getConfigClass () {
		return $this->config;
	}
	
	/** \fn loadPage ($pageName,$authorized = false,$authorizedAsAdmin = false) 
	 * Echo a page with name $pageName. If the user needs to be logged in to view this page (and he isn't) 
	 * an error is triggered, if he needs to be admin and he isn't als an error is triggered
	 *
	 * \todo trigger errors if user is not logged in, but userclass needs to be written first
	*/ 
	/*public*/ function loadPage ($pageName,$authorized = false,$authorizedAsAdmin = false) {
		// User code needs to be implemented first
		readfile ($this->skinPath . '/' . $pageName);
	}
	 
	/** \fn loadSkin ($skinName)
	 * Loads all skin options
	 *
	 * \param $skinName (string) the name of the skin to load
	*/
	/*private*/ function loadSkin ($skinName) {
		$supported = array ();
		$handler = opendir ('skins');
		// $files = scandir ('skins'); PHP5 only :( 
		// foreach ($files as $file) PHP5 only :(
		while (false !== ($file = readdir ($handler))) {
			$supSkins = array ();
			$skinPaths = array ();
			// do not start with a point (.)
			if ((preg_match ('/^\w.*/i', $file) == 1) and (is_dir ('skins/' . $file)) and (is_file ('skins/' . $file . '/skin.ini'))) {
				unset ($skin);
				$skin = parse_ini_file ('skins/' . $file . '/skin.ini',true);
				if (version_compare ($skin['general']['minversion'],MORGOS_VERSION,'<=') and (version_compare ($skin['general']['maxversion'],MORGOS_VERSION,'>='))) {
					$supSkins[] = $skin['general']['name'];
					$skinPaths[] = 'skins/' . $file . '/';
				}
			}
		}
		$key = array_search ($skinName,$supSkins);
		// needs to be 3 = (===) because the key can be 0 and than it is the same as false
		// to be compatible with PHP <= 4.2 we need to have === NULL
		if (($key === false) or ($key === NULL)) {
			trigger_error ('Couldn\'t load skin, unsupported skin',E_USER_ERROR);
		} else {
			$this->skinPath = $skinPaths[$key];
		}
	}
}
?>
