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
/** \file morgos.class.php
 * File that take care of morgos
 *
 * @since 0.2
 * @author Nathan Samson
*/
define ('MORGOS_VERSION', '0.2.0');
define ('MORGOS_VIEWPAGE_PLUGINID', '{529e4a98-02a7-46bb-be2a-671a7dfc852f}');

include_once ('interface/smarty/libs/Smarty.class.php');
include_once ('core/config.class.php');
include_once ('core/varia.functions.php');
include_once ('core/databasemanager.functions.php');
include_once ('core/user/usermanager.class.php');
include_once ('core/page/pagemanager.class.php');
include_once ('interface/actionmanager.class.php');
include_once ('interface/pluginmanager.class.php');
include_once ('interface/eventmanager.class.php');
include_once ('interface/pluginapi.class.php');

class morgos {
	/**
	 * The smarty system.
	 * @private 
	*/
	var $_smarty;
	/**
	 * The plugin manager
	 * @private
	*/
	var $_pluginManager;
	/**
	 * The config manager
	 * @private
	*/
	var $_configManager;
	/**
	 * The action manager
	 * @private
	*/
	var $_actionManager;
	/**
	 * The plugin API
	 * @private
	*/
	var $_pluginAPI;
	/**
	 * The database module
	 * @private
	*/
	var $_dbModule;
	/**
	 * The page manager
	 * @private
	*/
	var $_pageManager;
	/**
	 * The user manager
	 * @private
	*/
	var $_userManager;
	

	/**
	 * Constructor.
	*/
	function morgos () {
		$this->init ();
	}	

	/**
	 * Init the system (if possible otherwise try a lowInit).
	 * @public
	*/
	function init () {
		if ($this->isInstalled ()) {
			$this->_configManager = new configurator ();
			$this->_configManager->loadConfigFile ('config.php');
			$this->_dbModule = databaseLoadModule ('MySQL');
			$this->_userManager = new userManager ($this->_dbModule);
			$this->_pageManager = new pageManager ($this->_dbModule);
			$this->_actionManager = new actionManager ();
			$this->_smarty = new Smarty ();
			//$this->_smarty->debugging = true;
			$this->_pluginAPI = new pluginAPI ();
			$this->_pluginAPI->setUserManager ($this->_userManager);
			$this->_pluginAPI->setDBModule ($this->_dbModule);
			$this->_pluginAPI->setConfigManager ($this->_configManager);
			$this->_pluginAPI->setActionManager ($this->_actionManager);
			$this->_pluginAPI->setPageManager ($this->_pageManager);
			$this->_pluginAPI->setSmarty ($this->_smarty);
			$this->_pluginManager = new pluginManager ($this->_pluginAPI);
			$this->_pluginAPI->setPluginManager ($this->_pluginManager);
						
			// Hardcoded for the moment
			$this->_smarty->template_dir = 'skins/default/';
			$this->_smarty->compile_dir = 'skins_c/default/';
			$this->_smarty->cache_dir = 'cache/default/';
			$this->_smarty->config_dir = 'configs/';
			
			$a = $this->_dbModule->connect ($this->_configManager->getStringItem ('/databases/host'), 
								  $this->_configManager->getStringItem ('/databases/user'), 
								  $this->_configManager->getStringItem ('/databases/password'));
			if (isError ($a)) {
				var_dump ($a);
			}						
			$this->_dbModule->selectDatabase ($this->_configManager->getStringItem ('/databases/database'));
			$this->_dbModule->setPrefix ($this->_configManager->getStringItem ('/databases/table_prefix'));
			
			$this->_pluginManager->findAllPlugins ('interface/core-plugins');
			
			// load for the moment only the viewpage plugin;
			$a = $this->_pluginManager->setPluginToLoad (MORGOS_VIEWPAGE_PLUGINID);
			if (isError ($a)) {
				var_dump ($a);
			}
			$a = $this->_pluginManager->loadPlugins ();
			if (isError ($a)) {
				var_dump ($a);
			}
			$this->_smarty->assign ('SkinPath', 'skins/default');
		} else {
			$this->lowInit ();
			$this->error ("ERROR_MORGOS_NOT_INSTALLED", true);
		}
	}
	
	/**
	 * An intialization function that does't read config files and doesn't connect with database.
	*/
	function lowInit () {
		$this->_actionManager = new actionManager ();
		
		$this->_smarty = new Smarty ();
		$this->_smarty->template_dir = 'skins/default/';
		$this->_smarty->compile_dir = 'skins_c/default/';
		$this->_smarty->cache_dir = 'cache/default/';
		$this->_smarty->config_dir = 'configs/';
		
		$this->_pluginAPI = new pluginAPI ();
		$this->_pluginManager = new pluginManager ($this->_pluginAPI);
	}
	
	/**
	 * Shutdown the system.
	 * @public
	*/
	function shutdown () {
		//$this->_pluginAPI->shutdown ();
		$this->_smarty = null;
		$this->_pluginManager = null;
		$this->_configManager = null;
		$this->_actionManager = null;
		$this->_pluginAPI = null;
		$this->_dbModule->disconnect ();
		$this->_dbModule = null;
		$this->_pageManager = null;
	}
	
	/**
	 * Reinits the sytem
	 * @public
	*/
	function reinit () {
		$this->shutdown ();
		$this->init ();
	}
	
	/**
	 * Executes the command in $_GET
	 *
	 * @public
	*/
	function execute () {
		$this->_actionManager->execute ();
	}
	
	/**
	 * Shows an error
	 *
	 * @param $error (error) the error string
	 * @param $isFatal (bool) if true the execution ends
	 * @public	 
	*/
	function error ($error, $isFatal) {
		if ($isFatal) {
			$this->_smarty->assign ('MorgOS_PreviousLink', 'http://google.be');
			$this->_smarty->assign ('MorgOS_Error', $error);
			$this->_smarty->display ('error.tpl');
			$this->shutdown ();
			exit ();
		} else {
			$this->_smarty->assign ('MorgOS_RuntimeErrors', $error);
		}
	}	
	
	/**
	 * Runs the system and show a page (or redirect to another page)
	 * @public
	*/
	function run () {
		if (isset ($_GET['action'])) {
			$r = $this->_actionManager->executeAction ($_GET['action']);
		} else {
			$r = $this->_actionManager->executeAction ('viewPage');
		}
		if (isError ($r)) {
			die ('Unexpected error. ' . $r);
		}
	}
	
	/**
	 * Returns that morgos is installed
	 * @public
	 * @return (bool)
	*/
	function isInstalled () {
		return file_exists ('config.php');
	}
}


?>