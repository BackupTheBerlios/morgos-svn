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
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
define ('MORGOS_VERSION', '0.2.0');
define ('MORGOS_VIEWPAGE_PLUGINID', '{529e4a98-02a7-46bb-be2a-671a7dfc852f}');
define ('MORGOS_USER_PLUGINID', '{5df79e7c-2c14-4ad2-b13e-5c420d33182a}');
define ('MORGOS_ADMIN_PLUGINID', '{b8731582-9309-4629-a3d9-647f26a5a345}');

define ('MORGOS_DEFAULTSKIN_ID', '{33327ddc-9342-4f1a-9454-06e5a4adeef8}');

include_once ('interface/smarty/libs/Smarty.class.php');
include_once ('core/config.class.php');
include_once ('core/varia.functions.php');
include_once ('core/databasemanager.functions.php');
include_once ('core/i18n.class.php');
include_once ('core/user/usermanager.class.php');
include_once ('core/page/pagemanager.class.php');
include_once ('interface/actionmanager.class.php');
include_once ('interface/pluginmanager.class.php');
include_once ('interface/eventmanager.class.php');
include_once ('interface/pluginapi.class.php');
include_once ('interface/skinmanager.class.php');

/**
 * This is the front-end for MorgOS.
 * @defgroup interface Interface
*/

/**
 * A class that extends the functionality for smarty.
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
class ExtendedSmarty extends Smarty {
	
	/**
	 * Adds something to a value, put it after current value.
	 *
	 * @param $varName (string)
	 * @param $extraValue (mixed)
	 * @public
	*/
	function appendTo ($varName, $extraValue) {
		$this->assign ($varName, $this->get_template_vars ($varName).$extraValue);
	}
	
	/**
	 * Prepends something to a value, put it before current value.
	 *
	 * @param $varName (string)
	 * @param $extraValue (mixed)
	 * @public
	*/
	function prependTo ($varName, $extraValue) {
		$this->assign ($varName, $extraValue.$this->get_template_vars ($varName));
	}

}

/**
 * A main class that uses all others to show a page.
 *
 * @ingroup interface
 * @since 0.2
 * @author Nathan Samson
*/
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
	 * The event manager
	 * @private
	*/
	var $_eventManager;
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
	 * The translation manager
	 * @private
	*/
	var $_i18nManager;
	/**
	 * The skin manager
	 * @private
	*/
	

	/**
	 * Constructor.
	*/
	function morgos () {
		$this->init ();
	}	

	/**
	 * Init the system (if possible otherwise try a tinyInit).
	 * @public
	*/
	function init () {
		$this->checkSmartyDirs ();
		if ($this->isInstalled ()) {
			ob_start ();
			$this->_eventManager = new eventManager ();
			$this->_configManager = new configurator ();
			$this->_configManager->loadConfigFile ('config.php');
			$this->_i18nManager = new localizer ();
			$this->_i18nManager->loadErrorStrings ();
			$this->_dbModule = databaseLoadModule ('MySQL');
			$a = $this->_dbModule->connect ($this->_configManager->getStringItem ('/databases/host'), 
								  $this->_configManager->getStringItem ('/databases/user'), 
								  $this->_configManager->getStringItem ('/databases/password'));
						
			$this->_dbModule->selectDatabase ($this->_configManager->getStringItem ('/databases/database'));
			$this->_dbModule->setPrefix ($this->_configManager->getStringItem ('/databases/table_prefix'));
			$this->_userManager = new userManager ($this->_dbModule);
			$this->_pageManager = new pageManager ($this->_dbModule);
			$this->_actionManager = new actionManager ();
			$this->_smarty = new ExtendedSmarty ();
			//$this->_smarty->debugging = true;
			$this->_pluginAPI = new pluginAPI ($this);
			$this->_pluginAPI->setEventManager ($this->_eventManager);
			$this->_pluginAPI->setUserManager ($this->_userManager);
			$this->_pluginAPI->setDBModule ($this->_dbModule);
			$this->_pluginAPI->setConfigManager ($this->_configManager);
			$this->_pluginAPI->setActionManager ($this->_actionManager);
			$this->_pluginAPI->setPageManager ($this->_pageManager);
			$this->_pluginAPI->setSmarty ($this->_smarty);
			$this->_pluginAPI->setI18NManager ($this->_i18nManager);
			$this->_pluginManager = new pluginManager ($this->_pluginAPI);
			$this->_pluginAPI->setPluginManager ($this->_pluginManager);
						
			// Hardcoded for the moment

			$this->_skinManager = new skinManager ($this->_pluginAPI);
			$this->_skinManager->findAllSkins ('skins/');
			$this->_skinManager->loadSkin (MORGOS_DEFAULTSKIN_ID);
			//$this->_skinManager->loadSkin ('{0abf1469-d312-40b9-ad3a-3cb28b4c204e}');
			$this->_smarty->plugins_dir[] = 'interface/smarty-plugins/';
			$this->_smarty->assign_by_ref ('t', $this->_i18nManager);
			
			$this->_pluginManager->findAllPlugins ('interface/core-plugins');
			
			// hardcore loading of core plugins
			$a = $this->_pluginManager->setPluginToLoad (MORGOS_VIEWPAGE_PLUGINID);
			$a = $this->_pluginManager->setPluginToLoad (MORGOS_USER_PLUGINID);
			$a = $this->_pluginManager->setPluginToLoad (MORGOS_ADMIN_PLUGINID);
			if (isError ($a)) {
				var_dump ($a);
			}
			$a = $this->_pluginManager->loadPlugins ();
			if (isError ($a)) {
				var_dump ($a);
			}
			
			$this->_pluginManager->findAllPlugins ('plugins');
			$allExternalPlugins = $this->_configManager->getArrayItem ('/extplugs');
			foreach ($allExternalPlugins as $pID => $item) {
				if ($item->getCurrentValue () == true) {
					$this->_pluginManager->setPluginToLoad ($pID);
				}
			}
			$this->_pluginManager->loadPlugins ();
		} else {
			$this->tinyInit ();
			$this->loadInstaller ();
			//$this->error (new Error ('MORGOS_NOT_INSTALLED'), true);
		}
	}
	
	/**
	 * An intialization function that does't read config files and doesn't connect with database.
	*/
	function tinyInit () {
		ob_start ();
		$this->_actionManager = new actionManager ();
		$this->_i18nManager = new localizer ();	
		$this->_i18nManager->loadErrorStrings ();	
		
		$this->_smarty = new ExtendedSmarty ();
		$this->_smarty->template_dir = array ('skins/default/');
		$this->_smarty->compile_dir = 'skins_c/default/';
		$this->_smarty->cache_dir = 'cache/default/';
		$this->_smarty->plugins_dir[] = 'interface/smarty-plugins/';
		$this->_smarty->config_dir = 'configs/';
		$this->_smarty->assign ('SkinPath', 'skins/default');
		$this->_smarty->assign_by_ref ('t', $this->_i18nManager);		
		
		$this->_pluginAPI = new pluginAPI ($this);
		$this->_pluginAPI->setActionManager ($this->_actionManager);
		$this->_pluginAPI->setSmarty ($this->_smarty);
		$this->_pluginAPI->setI18NManager ($this->_i18nManager);
		
		$this->_pluginManager = new pluginManager ($this->_pluginAPI);
	}
	
	/**
	 * Shutdown the system.
	 * @public
	*/
	function shutdown () {
		if ($this->_pluginManager) {
			$this->_pluginManager->shutdown ();
		}
		$this->_pluginAPI = null;
		$this->_smarty = null;
		$this->_pluginManager = null;
		$this->_configManager = null;
		if ($this->_actionManager) {
			$this->_actionManager->shutdown ();
		}
		$this->_actionManager = null;
		if ($this->_dbModule != null) {
			$this->_dbModule->disconnect ();
			$this->_dbModule = null;
		}
		$this->_pageManager = null;
	}
	
	/**
	 * Shows a fatal error and stop running
	 *
	 * @param $error (error) the error string
	 * @public	 
	*/
	function error ($error) {
		if (array_key_exists ('HTTP_REFERER', $_SERVER)) {
			$this->_smarty->assign ('MorgOS_PreviousLink', $_SERVER['HTTP_REFERER']);
		} else {
			$this->_smarty->assign ('MorgOS_PreviousLink', 'http://google.com');
		}
		$this->_smarty->assign ('MorgOS_Error', $error);
		$this->_smarty->display ('error.tpl');
		$this->shutdown ();
	}	
	
	/**
	 * Runs the system and show a page (or redirect to another page)
	 * @param $defaultAction (string)
	 * @public
	*/
	function run ($defaultAction = 'viewPage') {
		//var_dump ($this->_pluginAPI);
		$allMessages = $this->_pluginAPI->getAllMessages ();
		$sm = $this->_pluginAPI->getSmarty ();
		$sm->assign_by_ref ('MorgOS_Errors', $allMessages[ERROR]);
		$sm->assign_by_ref ('MorgOS_Warnings', $allMessages[WARNING]);
		$sm->assign_by_ref ('MorgOS_Notices', $allMessages[NOTICE]);
	
		if (isset ($_GET['action'])) {
			$r = $this->_actionManager->executeAction ($_GET['action']);
		} elseif (isset ($_POST['action'])) {
			$r = $this->_actionManager->executeAction ($_POST['action']);
		} else {
			$r = $this->_actionManager->executeAction ($defaultAction);
		}

		if (isError ($r)) {
			if ($r->is ('ACTIONMANAGER_ACTION_NOT_FOUND')) {
				$this->error ($this->_i18nManager->translate ('You can\'t do this.'), true);
			} elseif ($r->is ('ACTIONMANAGER_INVALID_INPUT')) {
				$errors = $r->getParam (1);
				foreach ($errors as $error) {
					$this->_pluginAPI->addMessage ($this->_i18nManager->translateError ($error), ERROR);
				}
				$this->_pluginAPI->executePreviousAction ();
			} else {
				var_dump ($r);
				$this->error ($this->_i18nManager->translate ('Unexpected error.'), true);
			}
		}
	}
	
	/**
	 * Loads and runs the installer
	 * @public
	*/
	function loadInstaller () {
		$this->_pluginManager->findAllPlugins ('interface/installer');
		$allInstallerPlugins = $this->_pluginManager->getAllFoundPlugins ();
		foreach ($allInstallerPlugins as $plug) {
			$this->_pluginManager->setPluginToLoad ($plug->getID ());
		}
		$this->_pluginManager->loadPlugins ();
		
		$this->run ('installerShowLicense');
		$this->shutdown ();
		exit ();
	}
	
	/**
	 * Returns that morgos is installed
	 * @public
	 * @return (bool)
	*/
	function isInstalled () {
		return file_exists ('config.php');
	}
	
	/**
	 * Checks that smarty dirs are writable. If not show an error
	 *
	 * @private
	*/
	function checkSmartyDirs () {
		if (file_exists ('skins_c/default')) {
			if (is_dir ('skins_c/default')) {
				if (is_writable ('skins_c/default')) {
					return;
				}
			}
		}
		echo 'ERROR: A required dir is not found or writable!! fix it (skins_c/default)';
	}
}


?>
