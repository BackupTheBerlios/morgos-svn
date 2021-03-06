<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2007 MorgOS
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
 * @since 0.3 File is completely reorganized
 * @author Nathan Samson
ja
*/
define ('MORGOS_VERSION', '0.4.0');
define ('MORGOS_VIEWPAGE_PLUGINID', '{529e4a98-02a7-46bb-be2a-671a7dfc852f}');
define ('MORGOS_USER_PLUGINID', '{5df79e7c-2c14-4ad2-b13e-5c420d33182a}');
define ('MORGOS_ADMIN_PLUGINID', '{b8731582-9309-4629-a3d9-647f26a5a345}');

define ('MORGOS_DEFAULTSKIN_ID', '{33327ddc-9342-4f1a-9454-06e5a4adeef8}');
define ('MORGOS_DEFAULT_LANGUAGE', 'en_UK');

include_once ('core/config.class.php');
include_once ('core/varia.functions.php');
include_once ('core/dbdrivermanager.class.php');
include_once ('core/sqlwrapper.class.php');
include_once ('core/i18n.class.php');
include_once ('core/user/usermanager.class.php');
include_once ('core/page/pagemanager.class.php');
include_once ('interface/actionmanager.class.php');
include_once ('interface/pluginmanager.class.php');
include_once ('interface/eventmanager.class.php');
include_once ('interface/pluginapi.class.php');
include_once ('interface/skinmanager.class.php');
include_once ('interface/extendedsmarty.class.php');

/**
 * This is the front-end for MorgOS.
 * @defgroup interface Interface
*/

/**
 * Loads the correct MorgOS class and returns it.
 *
 * @since 0.3
 * @return (object *MorgOS) returns a MorgOS object.
*/
function loadMorgos () {
	if (BaseMorgOS::checkSmartyDirs ()) {
		if (ConfigMorgOS::isConfigured ()) {
			$m = new MorgOS ();
			if ($m->isDatabaseInstalled ()) {
				return $m;
			} else {
				return new ConfigMorgOS ();
			}
		} else {
			return new BaseMorgOS ();
		}
	} else {
		return new NoGUIMorgOS ();
	}
}

/**
 * A stripped down version of the MorgOS class. It doesn't have a GUI (Smarty).
*/
class NoGUIMorgOS {
	/**
	 * The translation manager
	 * @protected
	*/
	var $_i18nManager;
	
	/**
	 * The constructor.
	*/
	function NoGUIMorgOS () {
		$this->init ();
	}	
	
	/**
	 * Init the system.
	*/
	function init () {
		ob_start ();
		$this->_i18nManager = new localizer ();
		$this->_i18nManager->loadLanguage (MORGOS_DEFAULT_LANGUAGE, 'i18n');
		$this->setDefaultErrors ();
	}
	
	/** 
	 * Shows an error on the screen. Execution stops here. 
	 *
	 * @protected
	 * @param $error (object Error)
	*/
	function error ($error) {
		echo "<html>";
		echo "<head><title>";
			echo $this->_i18nManager->translate ('Fatal Error');
		echo "</title></head>";
		echo "<body>";
			echo "<div>";
				echo "<h2>".$this->_i18nManager->translate ('Fatal Error')."</h2>";
				echo "<p>".$this->_i18nManager->translateError ($error)."</p>";
			echo "</div>";
		echo "</body>";
		echo "</html>";
		$this->shutdown ();
		exit;
	}
	
	/**
	 * Shutdown
	 *
	 * @protected
	*/
	function shutdown () {
		$this->_i18nManager = null;
	}
	
	/**
	 * Run the system.
	 *
	 * @protected
	*/
	function run () {
		$this->error (new Error ('SKINSC_NOT_WRITABLE'));
	}
	
	/**
	 * Set the errors.
	 *
	 * @protected
	*/
	function setDefaultErrors () {
		$this->_i18nManager->addError ('EMPTY_INPUT', 'Empty input, please give %1.');
		$this->_i18nManager->addError ('INVALID_CHOICE','This was an invalid choice.');
		$this->_i18nManager->addError ('PASSWORDS_NOT_EQUAL', 
			'Passwords didn\'t match.');
		$this->_i18nManager->addError ('SKINSC_NOT_WRITABLE', 
			'skins_c/ is not writable by PHP. 
			 Please make it writable and proceed.');
		$this->_i18nManager->addError ('DATABASE_NOT_INSTALLED', 
			'It seems that the installation is not complete. Reinstall the database.');
		$this->_i18nManager->addError ('DBDRIVER_CANT_CONNECT', 
			"I can't connect with the database. Please try again later, or warn the system administrator. ");
	}
}


/**
 * A base Morgos class, it doesn't read config files, and doesn't connect with DB.
 *
 * @ingroup interface
 * @since 0.3
 * @author Nathan Samson
*/
class BaseMorgos extends NoGUIMorgOS {
	/**
	 * The smarty system.
	 * @protected 
	*/
	var $_smarty;
	/**
	 * The plugin manager
	 * @protected
	*/
	var $_pluginManager;
	/**
	 * The action manager
	 * @protected
	*/
	var $_actionManager;
	/**
	 * The event manager
	 * @protected
	*/
	var $_eventManager;
	/**
	 * The plugin API
	 * @protected
	*/
	var $_pluginAPI;
	
	/**
	 * Init the system.
	 *
	 * @protected
	*/
	function init () {
		parent::init ();
		
		//$this->_pluginAPI = new BasePluginAPI ();
		$this->_pluginAPI = new BasePluginAPI ($this);
		$this->_smarty = new ExtendedSmarty ();
		$this->_pluginManager = new PluginManager ($this->_pluginAPI);
		$this->_actionManager = new ActionManager ();
		$this->_eventManager = new EventManager ();
		$this->_skinManager = new skinManager ($this->_pluginAPI);
		
		$this->_smarty->plugins_dir[] = 'interface/smarty-plugins/';
		$this->_smarty->assign_by_ref ('t', $this->_i18nManager);
	}
	
	/**
	 * Run the system
	 *
	 * @protected
	*/
	function run () {
		$this->loadPluginAPI ();
		$this->loadSkin ();
		$this->_pluginManager->findAllPlugins ('interface/installer');
		$allInstallerPlugins = $this->_pluginManager->getAllFoundPlugins ();
		foreach ($allInstallerPlugins as $plug) {
			$this->_pluginManager->setPluginToLoad ($plug->getID ());
		}
		$this->_pluginManager->loadPlugins ();
		
		$this->executeAction ('installerShowLicense');
	}
	
	/**
	 * Assign the errors to Smarty.
	 *
	 * @protected
	*/
	function assignErrors () {
		//$allMessages = $this->_pluginAPI->getAllSystemMessages ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->assign_by_ref ('MorgOS_Errors', &$this->_smarty->_systemMessages[ERROR]);
		$sm->assign_by_ref ('MorgOS_Warnings', &$this->_smarty->_systemMessages[WARNING]);
		$sm->assign_by_ref ('MorgOS_Notices', &$this->_smarty->_systemMessages[NOTICE]);
	}
	
	/**
	 * Returns the name of the action to execute.
	 *
	 * @param $defaultAction (string)
	 * @return (string)
	*/
	function getActionToExecute ($defaultAction) {
		switch ($_SERVER['REQUEST_METHOD']) {
			case 'POST':
				$from = $_POST;
				break;
			case 'GET':
				$from = $_GET;
				break;
		}
		if ($from) {
			foreach ($from as $key=>$value) {
				if (strpos ($key, 'override_action_') !== false) {
					return substr ($key, strlen ('override_action_'));
				}
			}
			if (array_key_exists ('action', $from)) {
				return $from['action'];
			}
		}
		return $defaultAction;	
	}
	
	/**
	 * Returns an object action.
	 *
	 * @protected
	 * @param $aname (string)
	 * @return (object Action)
	*/
	function getActionFromName ($aname) {
		$actionArray =  $this->_actionManager->getAction ($aname);
		if (! isError ($actionArray)) {
			return $actionArray['action'];
		} else {
			return $actionArray;
		}
	}
	
	/**
	 * Checks that the user can view a page (that is associated with the given action)
	 *
	 * @param $action (object Action)
	 * @protected
	 * @return (bool)
	*/
	function canUserViewPage ($action) {
		if ($action->getPageName ()) {
			$pageM = $this->_pageManager;
			$page = $pageM->newPage ();
			$page->initFromName ($action->getPageName ());
			if (! $this->_pluginAPI->canUserViewPage ($page->getID ())) {
				return false;
			} else {
				return true;
			}	
		} else {
			return true;
		}
	}
	
	/**
	 * Execute the wanted action
	 *
	 * @param $defaultAction (string) The name of the action if the user didn't select 
	 *  something.
	*/
	function executeAction ($defaultAction) {
		$this->assignErrors ();
		$a = $this->getActionToExecute ($defaultAction);
		$action = $this->getActionFromName ($a);

		if ($this->canUserViewPage ($action)) {
			$r = $this->_actionManager->executeAction ($a);
			if (isError ($r)) {
				$this->error ($r);
			}
		} else {
			return new Error ('USER_HASNOTPERMISSION_VIEWPAGE');
		}
	}
	
	/**
	 * Shows an error page on the screen. Execution stops here.
	 *
	 * @public
	*/
	function error ($error) {
		$this->loadPluginAPI ();
		$this->loadSkin ();
		if (array_key_exists ('HTTP_REFERER', $_SERVER)) {
			$this->_smarty->assign ('MorgOS_PreviousLink', $_SERVER['HTTP_REFERER']);
		}
		$this->_smarty->assign ('MorgOS_Error', 
			$this->_i18nManager->translateError ($error));
		$this->_smarty->display ('error.tpl');
		$this->shutdown ();
		exit;
	}
	
	/**
	 * Loads the pluginAPI
	 *
	 * @protected
	*/
	function loadPluginAPI () {
		$this->_pluginAPI->setI18NManager ($this->_i18nManager);
		$this->_pluginAPI->setSmarty ($this->_smarty);
		$this->_pluginAPI->setPluginManager ($this->_pluginManager);
		$this->_pluginAPI->setActionManager ($this->_actionManager);
		$this->_pluginAPI->setEventManager ($this->_eventManager);
		$this->_pluginAPI->setSkinManager ($this->_skinManager);
	}
	
	/**
	 * Shutdown the system.
	 *
	 * @public
	*/
	function shutdown () {
		$this->_pluginManager->shutdown ();
		$this->_pluginManager = null;
		$this->_actionManager->shutdown ();
		$this->_actionManager = null;
		$this->_eventManager = null;
		$this->_pluginAPI->shutdown ();
		$this->_pluginAPI = null;
		$this->_smarty = null;
		parent::shutdown ();
	}
	
	/**
	 * Load the skin.
	 *
	 * @protected
	*/
	function loadSkin () {
		$this->_skinManager->findAllSkins ('skins/');
		$this->_skinManager->loadSkin (MORGOS_DEFAULTSKIN_ID);
		//$this->_skinManager->loadSkin ('{0abf1469-d312-40b9-ad3a-3cb28b4c204e}');
		//$this->_skinManager->loadSkin ('{c11681a8-5889-41cd-8fe1-d6fba2978804}');
	}
	
	/**
	 * Checks that smarty dirs are writable.
	 *
	 * @public static
	 * @return (bool)
	*/
	function checkSmartyDirs () {
		if (file_exists ('skins_c/')) {
			if (is_dir ('skins_c/')) {
				if (is_writable ('skins_c/')) {
					return true;
				}
			}
		}
		return false;
	}
}

/**
 * A somewhat extended MorgOS class.
*/
class ConfigMorgos extends BaseMorgos {
	/**
	 * The config manager
	 * @protected
	*/
	var $_configManager;
	
	/**
	 * Init the system.
	 *
	 * @public
	*/
	function init () {
		parent::init ();
		$this->_pluginAPI = new ConfigPluginAPI ($this);
		$this->_configManager = new configurator ();
		$e = $this->_configManager->loadConfigFile ('config.php');		
		if (isError ($e)) {
			$this->error ($e);
		}
	}
	
	/**
	 * Run the system
	 *
	 * @public
	*/
	function run () {
		$this->loadPluginAPI ();
		$this->loadSkin ();
		$this->error (new Error ('DATABASE_NOT_INSTALLED'));
	}
	
	/**
	 * Shutdown the system.
	 *
	 * @protected
	*/
	function shutdown () {
		$this->_configManager = null;
		parent::shutdown ();
	}
	
	/**
	 * Returns that morgos configuration is installed
	 * @public
	 * @return (bool)
	*/
	function isConfigured () {
		return file_exists ('config.php');
	}
	
	/**
	 * Set the correct variables in the plugin API
	 *
	 * @protected
	*/
	function loadPluginAPI () {
		parent::loadPluginAPI ();
		$this->_pluginAPI->setConfigManager ($this->_configManager);
	}
	
	/**
	 * Load the skin.
	 *
	 * @protected
	*/
	function loadSkin () {
		$this->_skinManager->findAllSkins ('skins/');
		$this->_skinManager->loadSkin (MORGOS_DEFAULTSKIN_ID);
	}
}

/**
 * A main class that uses all others to show a page.
 *
 * @ingroup interface
 * @since 0.3
 * @author Nathan Samson
*/
class Morgos extends ConfigMorgos {	
	/**
	 * The database driver
	 * @protected
	*/
	var $_dbDriver;
	/**
	 * The page manager
	 * @protected
	*/
	var $_pageManager;
	/**
	 * The user manager
	 * @protected
	*/
	var $_userManager;

	/**
	 * Init the system.
	 * @public
	*/
	function init () {
		parent::init ();
		$this->_pluginAPI = new PluginAPI ($this);
		DatabaseDriverManager::findAllDriversInDirectory ('core/dbdrivers');
		$this->_dbDriver = DatabaseDriverManager::loadDriver (
			$this->_configManager->getStringItem ('/databases/driver'));
		$e = $this->_dbDriver->connect (
			$this->_configManager->getStringItem ('/databases/host'), 
			$this->_configManager->getStringItem ('/databases/user'), 
			$this->_configManager->getStringItem ('/databases/password'),
			$this->_configManager->getStringItem ('/databases/database'));
			
		if (isError ($e)) {
			$this->_pageManager = new PageManager ($this->_dbDriver);
			$this->_userManager = new UserManager ($this->_dbDriver);
			$this->loadPluginAPI ();
			$this->loadUserSettings ();
			$this->error ($e);
		}		

		$this->_dbDriver->setPrefix (
			$this->_configManager->getStringItem ('/databases/table_prefix'));	

		$this->_pageManager = new PageManager ($this->_dbDriver);
		$this->_userManager = new UserManager ($this->_dbDriver);
		$this->_pluginManager->findAllPlugins ('interface/core-plugins');
		$this->loadPluginAPI ();
		$this->loadUserSettings ();
	}
	
	/**
	 * Set the correct variables in the plugin API
	 *
	 * @protected
	*/
	function loadPluginAPI () {
		parent::loadPluginAPI ();
		$this->_pluginAPI->setDBDriver ($this->_dbDriver);
		$this->_pluginAPI->setPageManager ($this->_pageManager);
		$this->_pluginAPI->setUserManager ($this->_userManager);	
	}
	
	/**
	 * Shutdown the system.
	 * @public
	*/
	function shutdown () {
		$this->_dbDriver->disconnect ();
		$this->_dbDriver = null;
		$this->_pageManager = null;
		$this->_userManager = null;
		parent::shutdown ();
	}
	
	/**
	 * Loads the plugins
	 *
	 * @protected
	*/
	function loadPlugins () {
		foreach ($this->getAllEnabledCorePlugins () as $corePluginID) {
			$this->_pluginManager->setPluginToLoad ($corePluginID);
		}
		$a = $this->_pluginManager->loadPlugins ();

		$this->_pluginManager->findAllPlugins ('plugins');
		$allExternalPlugins = $this->_configManager->getArrayItem ('/extplugs');
		foreach ($allExternalPlugins as $pID => $item) {
			if ($item->getCurrentValue () == true) {
				$this->_pluginManager->setPluginToLoad ($pID);
			}
		}
		$this->_pluginManager->loadPlugins ();
	}
	
	/**
	 * Runs the system and show a page (or redirect to another page)
	 * @param $defaultAction (string)
	 * @public
	*/
	function run ($defaultAction) {
		$this->loadSkin ();
		$this->loadPlugins ();
		
		$this->assignErrors ();
		$a = $this->getActionToExecute ($defaultAction);
		$action = $this->getActionFromName ($a);
		if (isError ($action)) {
			$this->error ($action);
		}
		
		$user = $this->_userManager->getCurrentUser ();
		$perms = $this->_actionManager->getActionRequiredPermissions ($a);
		foreach ($perms as $perm) {
			if (! $user->hasPermission ($perm)) {
				$this->error ('USER_HASNOTPERMISSION_VIEWPAGE');
			}
		}
		if ($this->canUserViewPage ($action)) {
			if ($action->getPageName ()) {
				$page = $this->_pageManager->newPage ();
				$page->initFromName ($action->getPageName ());
				if ($page->isAdminPage ()) {
					$this->_eventManager->triggerEvent ('viewAnyAdminPage', 
						array ($page->getID ()));
				} else {
					$this->_eventManager->triggerEvent ('viewPage', 
						array ($page->getID ()));
				}
			}
			$r = $this->_actionManager->executeAction ($a);
			if (isError ($r)) {
				if ($r->is ('ACTIONMANAGER_INVALID_INPUT')) {
					$this->_pluginAPI->addMessage ('Invalid input', ERROR);
					$this->_pluginAPI->executePreviousAction ();
				} else {
					$this->error ($r);
				}
			}
		} else {
			return new Error ('USER_HASNOTPERMISSION_VIEWPAGE');
		}
	}
	
	/**
	 * Returns that morgos database is installed
	 * @public
	 * @return (bool)
	*/	
	function isDatabaseInstalled () {
		foreach ($this->getAllEnabledCorePlugins () as $corePluginID) {
			$plug = $this->_pluginManager->getPlugin ($corePluginID);
			if (! $plug->isInstalled ($this->_pluginAPI)) {
				return false;
			} 
		}
		return true;
	}
	
	/**
	 * Returns an array of all enabled core pluginsID's
	 *
	 * @protected
	 * @return (string array)
	*/
	function getAllEnabledCorePlugins () {
		$corePlugs = array ( MORGOS_VIEWPAGE_PLUGINID, MORGOS_ADMIN_PLUGINID);
		if ($this->_configManager->getBoolItem ('/site/enableUsers')) {
			$corePlugs[] = MORGOS_USER_PLUGINID;
		}
		return $corePlugs;
	}
	
	/**
	 * Loads the (basic)user settings
	 *
	 * @protected
	 * @since 0.3
	*/
	function loadUserSettings () {
		$r = $this->_pluginAPI->addUserSetting ('skin', MORGOS_DEFAULTSKIN_ID, 'skin');
		$this->_pluginAPI->addUserSetting ('UILang', 
			$this->_pluginAPI->getDefaultLanguage ());
		$this->_pluginAPI->addUserSetting ('contentLang', 
			$this->_pluginAPI->getDefaultLanguage (), 'contentLanguage');
	}
	
	function loadSkin () {
		$this->_skinManager->findAllSkins ('skins/');
		$this->_skinManager->loadSkin (
			$this->_configManager->getStringItem ('/user/skin'));
	}
}

?>
