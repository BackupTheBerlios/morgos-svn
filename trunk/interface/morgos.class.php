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
 * @since 0.3 File is completely reorganized
 * @author Nathan Samson
*/
define ('MORGOS_VERSION', '0.3.0');
define ('MORGOS_VIEWPAGE_PLUGINID', '{529e4a98-02a7-46bb-be2a-671a7dfc852f}');
define ('MORGOS_USER_PLUGINID', '{5df79e7c-2c14-4ad2-b13e-5c420d33182a}');
define ('MORGOS_ADMIN_PLUGINID', '{b8731582-9309-4629-a3d9-647f26a5a345}');

define ('MORGOS_DEFAULTSKIN_ID', '{33327ddc-9342-4f1a-9454-06e5a4adeef8}');
define ('MORGOS_DEFAULT_LANGUAGE', 'en_UK');

include_once ('interface/smarty/libs/Smarty.class.php');
include_once ('core/config.class.php');
include_once ('core/varia.functions.php');
include_once ('core/databasemanager.functions.php');
include_once ('core/sqlwrapper.class.php');
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
	 * The constructor.
	*/
	function ExtendedSmarty () {
		parent::Smarty ();
		$this->template_dir = array ();
	}	
	
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
		$this->shutdown;
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
		$this->error (SKINSC_NOT_WRITABLE);
	}
	
	/**
	 * Set the errors.
	 *
	 * @protected
	*/
	function setDefaultErrors () {
		$this->_i18nManager->addError ('EMPTY_INPUT', 
			$this->_i18nManager->translate ('Empty input, please give %1.'));
		$this->_i18nManager->addError ('INVALID_CHOICE', 
			$this->_i18nManager->translate ('This was an invalid choice.'));
		$this->_i18nManager->addError ('PASSWORDS_NOT_EQUAL', 
			$this->_i18nManager->translate ("Passwords didn't match."));
		$this->_i18nManager->addError ('SKINSC_NOT_WRITABLE', 
			$this->_i18nManager->translate ("skins_c/default is not writable by PHP. 
				Please make it writable and proceed."));
		$this->_i18nManager->addError ('DATABASE_NOT_INSTALLED', 
			$this->_i18nManager->translate ("skins_c/default is not writable by PHP. 
				Please make it writable and proceed."));
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
		$this->shutdown ();
	}
	
	/**
	 * Assign the errors to Smarty.
	 *
	 * @protected
	*/
	function assignErrors () {
		$allMessages = $this->_pluginAPI->getAllMessages ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->assign ('MorgOS_Errors', $allMessages[ERROR]);
		$sm->assign ('MorgOS_Warnings', $allMessages[WARNING]);
		$sm->assign ('MorgOS_Notices', $allMessages[NOTICE]);
	}
	
	/**
	 * Returns the name of the action to execute.
	 *
	 * @param $defaultAction (string)
	 * @return (string)
	*/
	function getActionToExecute ($defaultAction) {
		if (isset ($_GET['action'])) {
			$a = $_GET['action'];
		} elseif (isset ($_POST['action'])) {
			$a = $_POST['action'];
		} else {
			$a = $defaultAction;
		}
		return $a;
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
		} else {
			//$this->_smarty->assign ('MorgOS_PreviousLink', 'http://google.com');
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
		if (file_exists ('skins_c/default')) {
			if (is_dir ('skins_c/default')) {
				if (is_writable ('skins_c/default')) {
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
	 * Load the skin.
	 *
	 * @protected
	*/
	function loadSkin () {
		$this->_skinManager->findAllSkins ('skins/');
		//$this->_skinManager->loadSkin (MORGOS_DEFAULTSKIN_ID);
		$this->_skinManager->loadSkin ('{0abf1469-d312-40b9-ad3a-3cb28b4c204e}');
		//$this->_skinManager->loadSkin ('{c11681a8-5889-41cd-8fe1-d6fba2978804}');
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
	 * The database module
	 * @protected
	*/
	var $_dbModule;
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
		$this->_dbModule = databaseLoadModule ('MySQL');
		$e = $this->_dbModule->connect (
			$this->_configManager->getStringItem ('/databases/host'), 
			$this->_configManager->getStringItem ('/databases/user'), 
			$this->_configManager->getStringItem ('/databases/password'));
			
		if (isError ($e)) {
			$this->error ($e);
		}		
		$e = $this->_dbModule->selectDatabase (
			$this->_configManager->getStringItem ('/databases/database'));
		if (isError ($e)) {
			$this->error ($e);
		}	
		$this->_dbModule->setPrefix (
			$this->_configManager->getStringItem ('/databases/table_prefix'));	

		$this->_pageManager = new PageManager ($this->_dbModule);
		$this->_userManager = new UserManager ($this->_dbModule);
		$this->loadPluginAPI ();
		
		$this->_pluginManager->findAllPlugins ('interface/core-plugins');
		// hardcore loading of core plugins
		$a = $this->_pluginManager->setPluginToLoad (MORGOS_VIEWPAGE_PLUGINID);
		$a = $this->_pluginManager->setPluginToLoad (MORGOS_USER_PLUGINID);
		$a = $this->_pluginManager->setPluginToLoad (MORGOS_ADMIN_PLUGINID);
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
	 * Set the correct variables in the plugin API
	 *
	 * @protected
	*/
	function loadPluginAPI () {
		parent::loadPluginAPI ();
		$this->_pluginAPI->setDBModule ($this->_dbModule);
		$this->_pluginAPI->setPageManager ($this->_pageManager);
		$this->_pluginAPI->setUserManager ($this->_userManager);	
	}
	
	/**
	 * Shutdown the system.
	 * @public
	*/
	function shutdown () {
		$this->_dbModule->disconnect ();
		$this->_dbModule = null;
		$this->_pageManager = null;
		$this->_userManager = null;	
		parent::shutdown ();
	}
	
	/**
	 * Runs the system and show a page (or redirect to another page)
	 * @param $defaultAction (string)
	 * @public
	*/
	function run ($defaultAction) {
		$this->loadSkin ();
		
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
			$r = $this->_actionManager->executeAction ($a);
			if (isError ($r)) {
				$this->error ($r);
			}
		} else {
			return new Error ('USER_HASNOTPERMISSION_VIEWPAGE');
		}
	}
	
	/**
	 * Returns that morgos database is installed
	 * @public static
	 * @return (bool)
	*/	
	function isDatabaseInstalled () {
		$d = $this->_dbModule;
		return $d->tableExists ('groupPermissions') && $d->tableExists ('groups') && 
			$d->tableExists ('translatedGroups') && $d->tableExists ('groupUsers') && 
			$d->tableExists ('users') && 
			$d->tableExists ('pages') && $d->tableExists ('translatedPages');
	}
}

?>
