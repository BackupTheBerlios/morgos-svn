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
/**
 * This is the admin plugin class.
 *
 * @since 0.2
 * @author Nathan Samson
*/
class adminCorePluginAdminPlugin extends plugin {
	
	function adminCorePluginAdminPlugin ($dir) {
		parent::plugin ($dir);
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		$am = &$this->_pluginAPI->getActionManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		
		$am->addAction (new action (
			'adminPluginManager', 'GET', array ($this, 'onViewPluginManager'), array (), array ()));
			
		$am->addAction (new action (
			'adminEnablePlugin', 'GET', array ($this, 'onEnablePlugin'), 
				array (new StringInput ('pluginID')), array ()));
			
		$am->addAction (new action (
			'adminInstallPlugin', 'GET', array ($this, 'onInstallPlugin'), 
				array (new StringInput ('pluginID')), array ()));
			
		$am->addAction (new action (
			'adminDisablePlugin', 'GET', array ($this, 'onDisablePlugin'), 
				array (new StringInput ('pluginID')), array ()));
			
		$am->addAction (new action (
			'adminUnInstallPlugin', 'GET', array ($this, 'onUnInstallPlugin'), 
				array (new StringInput ('pluginID')), array ()));
	}
	
	function onViewPluginManager () {
		$em = &$this->_pluginAPI->getEventManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$plugM = &$this->_pluginAPI->getPluginManager ();
		$dbM = &$this->_pluginAPI->getDBModule ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('MorgOS_Admin_PluginManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$em->triggerEvent ('viewAnyAdminPage', array (&$pageID));				
			
			$availablePlugins = array ();
			foreach ($plugM->getAllFoundPlugins () as $plugin) {
				if ($plugin->isCorePlugin () === false) {
					if ($plugin->isCompatible ()) {
						$cMessage = '';
					} else {
						if (! $plugin->isPHPCompatible ()) {
							$cMessage = 'Your version of PHP ('.PHP_VERSION.') is not compatible with this plugin.';
						} elseif (! $plugin->isMinVersionReached ()) {
							$cMessage = 'Your version of MorgOS is too old.';
						} elseif ($plugin->isMaxVersionExceeded ()) {
							$cMessage = 'Your version of MorgOS is too new.';
						}
					}
					$availablePlugins[] = array (
						'Name'=>$plugin->getName (), 'Version'=>$plugin->getVersion (), 'Enabled'=>$plugin->isLoaded (), 
						'EnableLink'=>'index.php?action=adminEnablePlugin&pluginID='.$plugin->getID (),
						'DisableLink'=>'index.php?action=adminDisablePlugin&pluginID='.$plugin->getID (),
						'Compatible'=>$plugin->isCompatible (), 'CompatibleMessage'=>$cMessage,
						'Installed'=>$plugin->isInstalled ($this->_pluginAPI), 
						'InstallLink'=>'index.php?action=adminInstallPlugin&pluginID='.$plugin->getID (),
						'UnInstallLink'=>'index.php?action=adminUnInstallPlugin&pluginID='.$plugin->getID ());
				}
			}
			$sm->assign ('MorgOS_AvailablePlugins', $availablePlugins);
			$sm->display ('admin/pluginmanager.tpl');
		} else {
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onEnablePlugin ($pluginID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$plugM = &$this->_pluginAPI->getPluginManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('MorgOS_Admin_PluginManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$plugin = $plugM->getPlugin ($pluginID);
			if (! $plugin->isInstalled ($this->_pluginAPI)) {
				$plugin->install ($this->_pluginAPI);
			}
			$cm = &$this->_pluginAPI->getConfigManager ();
			$a = new configItem ('/extplugs/'.$pluginID, BOOL);
			$a->setValue (true);
			$cm->addOption ($a);
			
			$this->_pluginAPI->writeConfigFile ($cm);
			$this->_pluginAPI->addRuntimeMessage ('Plugin is enabled', NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onInstallPlugin ($pluginID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$plugM = &$this->_pluginAPI->getPluginManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('MorgOS_Admin_PluginManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$plugin = $plugM->getPlugin ($pluginID);
			if (! $plugin->isInstalled ($this->_pluginAPI)) {
				$plugin->install ($this->_pluginAPI);
				$this->_pluginAPI->addRuntimeMessage ('Plugin is installed', NOTICE);
			} else {
				$this->_pluginAPI->addRuntimeMessage ('Plugin was already installed', WARNING);
			}
			
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onDisablePlugin ($pluginID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$plugM = &$this->_pluginAPI->getPluginManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('MorgOS_Admin_PluginManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$cm = &$this->_pluginAPI->getConfigManager ();
			$a = $cm->getItem ('/extplugs/'.$pluginID, BOOL);
			$cm->removeOption ($a);
			$this->_pluginAPI->writeConfigFile ($cm);
			$this->_pluginAPI->addRuntimeMessage ('Plugin is disabled', NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onUnInstallPlugin ($pluginID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$plugM = &$this->_pluginAPI->getPluginManager ();	
		
		$page = $pageM->newPage ();
		$page->initFromName ('MorgOS_Admin_PluginManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$plugin = $plugM->getPlugin ($pluginID);
			if ($plugin->isInstalled ($this->_pluginAPI)) {
				$plugin->Uninstall ($this->_pluginAPI);
				$this->_pluginAPI->addRuntimeMessage ('Plugin is uninstalled', NOTICE);
			} else {
				$this->_pluginAPI->addRuntimeMessage ('Plugin was not installed', WARNING);
			}
			
			$this->_pluginAPI->executePreviousAction ();
		}
	}
}
