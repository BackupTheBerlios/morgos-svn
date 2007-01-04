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
class adminCorePluginAdminPlugin extends InstallablePlugin {
	
	function adminCorePluginAdminPlugin ($dir) {
		parent::plugin ($dir);
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		$am = &$this->_pluginAPI->getActionManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		
		$am->addAction (new action (
			'adminPluginManager', 'GET', array ($this, 'onViewPluginManager'), array (), array (), 'MorgOS_Admin_PluginManager'));
			
		$am->addAction (new action (
			'adminEnablePlugin', 'GET', array ($this, 'onEnablePlugin'), 
				array (new StringInput ('pluginID')), array (), 'MorgOS_Admin_PluginManager', false));
			
		$am->addAction (new action (
			'adminInstallPlugin', 'GET', array ($this, 'onInstallPlugin'), 
				array (new StringInput ('pluginID')), array (), 'MorgOS_Admin_PluginManager', false));
			
		$am->addAction (new action (
			'adminDisablePlugin', 'GET', array ($this, 'onDisablePlugin'), 
				array (new StringInput ('pluginID')), array (), 'MorgOS_Admin_PluginManager', false));
			
		$am->addAction (new action (
			'adminUnInstallPlugin', 'GET', array ($this, 'onUnInstallPlugin'), 
				array (new StringInput ('pluginID')), array (), 'MorgOS_Admin_PluginManager', false));
		$a = $em->subscribeToEvent ('viewAnyAdminPage', 
			new callback ('setPluginError', array ($this, 'setPluginErrors'), 
			array ('pageID')));
	}
	
	function onViewPluginManager () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$plugM = &$this->_pluginAPI->getPluginManager ();
		$t = &$this->_pluginAPI->getI18NManager ();		
			
		$availablePlugins = array ();
		$enabledPlugins = array ();
		$disabledPlugins = array ();
		foreach ($plugM->getAllFoundPlugins () as $plugin) {
			if ($plugin->isCorePlugin () === false) {
				$c = false;
				if (! $plugin->isPHPCompatible ()) {
					$cMessage = $t->translate ('Your version of PHP (%1) is not compatible with this plugin.', array (PHP_VERSION));
				} elseif (! $plugin->isMinVersionReached ()) {
					$cMessage = $t->translate ('Your version of MorgOS is too old.');
				} elseif ($plugin->isMaxVersionExceeded ()) {
					$cMessage = $t->translate ('Your version of MorgOS is too new.');
				} else {
					$c = true;
					$cMessage = '';
				}
				$pInfo = array (
					'Name'=>$plugin->getName (), 'Version'=>$plugin->getVersion (), 'Enabled'=>$plugin->isLoaded (), 
					'EnableLink'=>'index.php?action=adminEnablePlugin&pluginID='.$plugin->getID (),
					'DisableLink'=>'index.php?action=adminDisablePlugin&pluginID='.$plugin->getID (),
					'Compatible'=>$c, 'CompatibleMessage'=>$cMessage,
					'Installable'=> is_a ($plugin, 'InstallablePlugin'),
					'Installed'=>$plugin->isInstalled ($this->_pluginAPI), 
					'InstallLink'=>'index.php?action=adminInstallPlugin&pluginID='.$plugin->getID (),
					'UnInstallLink'=>'index.php?action=adminUnInstallPlugin&pluginID='.$plugin->getID ());	
				
				$availablePlugins[] = $pInfo; 
				
				if ($plugin->isLoaded ()) {
					$enabledPlugins[] = $pInfo;
				} else {
					$disabledPlugins[] = $pInfo;
				}
			}
		}
		$sm->assign ('MorgOS_AvailablePlugins', $availablePlugins);
		$sm->assign ('MorgOS_EnabledPlugins', $enabledPlugins);
		$sm->assign ('MorgOS_DisabledPlugins', $disabledPlugins);
		$sm->appendTo ('MorgOS_AdminPage_Content', 
			$sm->fetch ('admin/plugin/pluginmanager.tpl'));
		$sm->display ('admin/genericpage.tpl');
	}
	
	function onEnablePlugin ($pluginID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$plugM = &$this->_pluginAPI->getPluginManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		$plugin = $plugM->getPlugin ($pluginID);
		if (! $plugin->isInstalled ($this->_pluginAPI)) {
			$plugin->install ($this->_pluginAPI);
		}
		$cm = &$this->_pluginAPI->getConfigManager ();
		$a = new configItem ('/extplugs/'.$pluginID, BOOL);
		$a->setValue (true);
		$cm->addOption ($a);
		
		$this->_pluginAPI->addMessage ($t->translate ('Plugin is enabled'), NOTICE);
		$this->_pluginAPI->writeConfigFile ($cm);			
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onInstallPlugin ($pluginID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$plugM = &$this->_pluginAPI->getPluginManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		$plugin = $plugM->getPlugin ($pluginID);
		if (! $plugin->isInstalled ($this->_pluginAPI)) {
			$plugin->install ($this->_pluginAPI);
			$this->_pluginAPI->addMessage ($t->translate ('Plugin is installed'), NOTICE);
		} else {
			$this->_pluginAPI->addMessage ($t->translate ('Plugin was already installed'), WARNING);
		}
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onDisablePlugin ($pluginID) {
		$plugM = &$this->_pluginAPI->getPluginManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		$cm = &$this->_pluginAPI->getConfigManager ();
		$a = $cm->getItem ('/extplugs/'.$pluginID, BOOL);
		$cm->removeOption ($a);
		$this->_pluginAPI->addMessage ($t->translate ('Plugin is disabled'), NOTICE);
		$this->_pluginAPI->writeConfigFile ($cm);
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onUnInstallPlugin ($pluginID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$plugM = &$this->_pluginAPI->getPluginManager ();	
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$plugin = $plugM->getPlugin ($pluginID);
		if ($plugin->isInstalled ($this->_pluginAPI)) {
			$plugin->Uninstall ($this->_pluginAPI);
			$this->_pluginAPI->addMessage ($t->translate ('Plugin is uninstalled'), NOTICE);
		} else {
			$this->_pluginAPI->addMessage ($t->translate ('Plugin was not installed'), WARNING);
		}
		
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function setPluginErrors ($pageID) {
		$pluginM = &$this->_pluginAPI->getPluginManager ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$pluginMPage = $pageM->newPage ();
		$pluginMPage->initFromName ('MorgOS_Admin_PluginManager');
		$errors = $pluginM->getAllLoadErrors ();
		if (count ($errors) > 0 and $pluginMPage->getID () == $pageID) {
			foreach ($errors as $error) {
				$this->_pluginAPI->addRuntimeMessage ("Failed loading plugin: ".$error->getParam (2), ERROR);
			}
		}
	}
	
	function install (&$pluginAPI, &$dbModule, $siteDefaultLanguage) {
		$pageM = new pageManager ($dbModule);
		$t = &$pluginAPI->getI18NManager();
		$pluman = $pageM->newPage ();
		$admin = $pageM->getAdminPage ();
		
		$pluman->initFromArray (array (
				'name'=>'MorgOS_Admin_PluginManager', 
				'parent_page_id'=>$admin->getID (), 
				'action'=>'adminPluginManager'));
		$pageM->addPageToDatabase ($pluman);
		
		$tPlugMan = $pageM->newTranslatedPage ();
		
		$tPlugMan->initFromArray (array (
				'language_code'=>$siteDefaultLanguage, 
				'translated_title'=>$t->translate ('Plugin Manager'), 
				'translated_Content'=>$t->translate ('Enable/disable plugins.')));
				
		$pluman->addTranslation ($tPlugMan);
	}
}
