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
 * This is the admin class.
 *
 * @since 0.2
 * @author Nathan Samson
*/
class adminCorePlugin extends InstallablePlugin {
	var $_pluginAdmin;
	
	function adminCorePlugin ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Admin core plugin';
		$this->_ID = MORGOS_ADMIN_PLUGINID;
		$this->_minMorgOSVersion = MORGOS_VERSION;
		$this->_maxMorgOSVersion = MORGOS_VERSION;
		$this->_version = MORGOS_VERSION;
		include_once ($this->_loadedDir.'/adminpluginplugin.class.php');
		$this->_pluginAdmin = new adminCorePluginAdminPlugin ($this->_loadedDir);
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);				
		$am = &$this->_pluginAPI->getActionManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		$am->addAction (
			new action ('admin', 'GET',  array ($this, 'onViewAdmin'), 
				array (), 
				array (new IDInput ('pageID'), new LocaleInput ('pageLang'))));
		$am->addAction (
			new action ('adminHome', 'GET',  array ($this, 'onAdminHome'), 
				array (), array (), 'MorgOS_Admin_Home'));
		$am->addAction (
			new action ('adminLogin', 'POST',  array ($this, 'onLogin'), 
			array (new StringInput ('adminLogin'), new StringInput ('adminPassword')),
			array ()));
		$am->addAction (
			new action ('adminLogout', 'GET',  array ($this, 'onLogout'), array (), 
			array ()));
			
		$am->addAction (
			new action ('adminChangeSiteSettings', 'POST',  
				array ($this, 'onChangeSiteSettings'), 
				array (new StringInput ('siteTitle'), new BoolInput ('enableUsers')), 
				array ()));
				
		$am->addAction (
			new action ('adminInstallLanguage', 'POST',  
				array ($this, 'onInstallLanguage'), 
				array (new StringInput ('languageName')), 
				array (), 'MorgOS_Admin_Home', false));
		
		$am->addAction (
			new action ('adminDeleteLanguage', 'GET',  
				array ($this, 'onDeleteLanguage'), 
				array (new StringInput ('languageName')), 
				array (), 'MorgOS_Admin_Home', false));
			
		$em->addEvent (new event ('viewAnyAdminPage', array ('pageID')));
		$em->subscribeToEvent ('viewAnyAdminPage', 
			new callback ('setAdminVars', array ($this, 'setAdminVars'), 
			array ('pageID')));
		$em->subscribeToEvent ('viewPage',
			new callback ('setAdminBox', array ($this, 'setAdminBox'),
			array ()));
		$this->_pluginAdmin->load ($pluginAPI);
	}
	
	function onViewAdmin ($pageID, $pageLang) {
		//$a = $this->_pluginAPI->getEventManager ()->triggerEvent ('viewPage');
		/*foreach ($a as $r) {
			if ($r == false or isError ($r)) {
				return;
			}
		}*/
		
		$userManager = &$this->_pluginAPI->getUserManager ();
		$user = $userManager->getCurrentUser ();
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$configManager = &$this->_pluginAPI->getConfigManager ();
		
		if ($pageLang === null) {
			$pageLang = $configManager->getStringItem ('/user/contentLang');
		}		
		
		$page = $pageManager->newPage ();
		$am = &$this->_pluginAPI->getActionManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		if ($pageID) {
			$page->initFromDatabaseID ($pageID);
		} else {
			$page->initFromName ('MorgOS_Admin_Home');
			$pageID = $page->getID ();
		}
		$sm = &$this->_pluginAPI->getSmarty ();
		$em = &$this->_pluginAPI->getEventManager ();
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {
			$em->triggerEvent ('viewAnyAdminPage', array (&$pageID, $pageLang));
			
			if ($page->getAction ()) {
				$am->executeAction ($page->getAction (), array (), false);
			} else {
				$sm->display ('admin/genericpage.tpl');
			}
		} else {
			$this->_pluginAPI->addRuntimeMessage (
				$t->translate ('Login as a valid admin user to view this page.'), 
				NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onAdminHome () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$config = &$this->_pluginAPI->getConfigManager ();
		$sm->assign ('MorgOS_AdminHome_EnableUsers', 
			$config->getBoolItem ('/site/enableUsers'));
		$sm->appendTo ('MorgOS_AdminPage_Content', $sm->fetch ('admin/home.tpl'));
		$sm->display ('admin/genericpage.tpl');
	}
	
	function onChangeSiteSettings ($siteTitle, $enableUsers) {
		$config = &$this->_pluginAPI->getConfigManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		$a = $config->setItemValue ('/site/title', STRING, $siteTitle);
		$config->setItemValue ('/site/enableUsers', BOOL, $enableUsers);
		$this->_pluginAPI->writeConfigFile ($config);
		$this->_pluginAPI->addMessage (
			$t->translate ('Configuration is saved.'), NOTICE);
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onLogin ($adminLogin, $adminPassword) {
		$userManager = &$this->_pluginAPI->getUserManager ();
		$a = $userManager->login ($adminLogin, $adminPassword);
		$t = &$this->_pluginAPI->getI18NManager ();
		if (isError ($a)) {
			if ($a->is ('USERMANAGER_LOGIN_FAILED_INCORRECT_INPUT')) {
				$sm = &$this->_pluginAPI->getSmarty ();
				$this->_pluginAPI->addRuntimeMessage (
					$t->translate ('Given a wrong password/username.'), ERROR);
				$sm->display ('admin/login.tpl');
			} else {
				return $a;
			}
		} else {
			$this->_pluginAPI->addMessage ($t->translate ('You are now logged in.'), 
				NOTICE);
			$this->_pluginAPI->doAction ('admin');
		}
	}
	
	function onLogout () {
		$userManager = &$this->_pluginAPI->getUserManager ();
		$a = $userManager->logout ();
		$t = &$this->_pluginAPI->getI18NManager ();
		if (isError ($a)) {
			return $a;
		} else {
			$this->_pluginAPI->addMessage ($t->translate ('You are logged out.'), 
				NOTICE);
			$this->_pluginAPI->doAction ('admin');
		}
	}
	
	function setAdminVars ($pageID) {
		$sm = &$this->_pluginAPI->getSmarty ();	
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$config = &$this->_pluginAPI->getConfigManager ();
		
		$pageLang = $config->getStringItem ('/user/contentLang');
		
		$rootPage = $pageManager->newPage ();
		$rootPage->initFromName ('admin');
		$adminNav = $this->_pluginAPI->menuToArray ($pageManager->getMenu ($rootPage));
		
		$sm->assign ('MorgOS_Admin_RootMenu', $adminNav);
		$sm->assign ('MorgOS_AdminTitle', 'Admin panel');
		
		$page = $pageManager->newPage ();
		$page->initFromDatabaseID ($pageID);
		$tpage = $page->getTranslation ($pageLang);
		if (isError ($tpage)) {
			//debug_print_backtrace ();
			die ('Translation doesnt exists'.$pageID);
		}		
		$sm->assign ('MorgOS_AdminPage_Title', $tpage->getTitle ());
		$sm->assign ('MorgOS_AdminPage_Content', $tpage->getContent ());
		$sm->assign ('MorgOS_SiteTitle', 
			$config->getStringItem ('/site/title'));
		$sm->assign ('MorgOS_AvailableContentLanguages', $this->_pluginAPI->getInstalledContentLanguages ());
		$sm->assign_by_ref ('MorgOS_CurrentAdminPage', $tpagearray);
		return true;
	}
	
	function onInstallLanguage ($language) {
		$configM = &$this->_pluginAPI->getConfigManager ();
		$t = $this->_pluginAPI->getI18NManager ();
		$cItem = new ConfigItem ('/languages/'.$language, STRING);
		$cItem->setValue ($language);
		$ret = $configM->addOption ($cItem);
		if (isError ($ret)) {
			if ($ret->is ('CONFIGURATOR_OPTION_EXISTS')) {
				$this->_pluginAPI->addMessage (
					$t->translate ('This language already exists'), ERROR);
				$this->_pluginAPI->executePreviousAction ();
			} else {
				return $ret;
			}
		} else {
			$this->_pluginAPI->writeConfigFile ($configM);
			$this->_pluginAPI->addMessage (
				$t->translate ('New language added'), NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onDeleteLanguage ($language) {
		$configM = &$this->_pluginAPI->getConfigManager ();
		$t = $this->_pluginAPI->getI18NManager ();
		$cItem = $configM->getItem ('/languages/'.$language, STRING);
		$ret = $configM->removeOption ($cItem);
		if (isError ($ret)) {
			if ($ret->is ('CONFIGURATOR_ITEM_DOESNT_EXISTS')) {
				$this->_pluginAPI->addMessage (
					$t->translate ("This language doesn't exists"), ERROR);
				$this->_pluginAPI->executePreviousAction ();
			} else {
				return $ret;
			}
		} else {
			$this->_pluginAPI->writeConfigFile ($configM);
			$this->_pluginAPI->addMessage (
				$t->translate ('Language removed'), NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function setAdminBox () {
		$userM = &$this->_pluginAPI->getUserManager ();
		$user = $userM->getCurrentUser ();
		if ($user == null) {
			// this is obiously not an admin
			return true;
		}
		if ($user->isInGroup ('administrator')) {
			$sm = &$this->_pluginAPI->getSmarty ();
			$sm->appendTo ('MorgOS_Sidebar_Content', 
				$sm->fetch ('user/adminbox.tpl'));
		}
		return true;
	}
	
	function install (&$pluginAPI, &$dbModule, $siteDefaultLanguage) {
		$pageM = new pageManager ($dbModule);
		$t = &$pluginAPI->getI18NManager();
		$admin = $pageM->getAdminPage ();
		$ahome = $pageM->newPage ();
		$ahome->initFromArray (array (
				'name'=>'MorgOS_Admin_Home', 
				'parent_page_id'=>$admin->getID (),
				'action'=>'adminHome',
				'place_in_menu'=>MORGOS_MENU_FIRST));
		$pageM->addPageToDatabase ($ahome);
		$tAHome = $pageM->newTranslatedPage ();	
		$tAHome->initFromArray (array (
				'language_code'=>$siteDefaultLanguage, 
				'translated_title'=>$t->translate ('Admin'), 
				'translated_content'=>
					$t->translate ('This is the admin.'
						.' Here you can configure the site, add/remove and edit' 
						.' pages, or ban users.')));
		$ahome->addTranslation ($tAHome);
		
		$adminSaveConfig = $pageM->newPage ();
		$adminSaveConfig->initFromArray (array (
				'name'=>'MorgOS_Admin_SaveConfig', 
				'parent_page_id'=>$admin->getID (),
				'place_in_menu'=>MORGOS_MENU_INVISIBLE));
		$pageM->addPageToDatabase ($adminSaveConfig);	
		
		$tASaveConfig = $pageM->newTranslatedPage ();
		$tASaveConfig->initFromArray (array ('language_code'=>$siteDefaultLanguage, 
				'translated_title'=>$t->translate ('Save config'), 
				'translated_content'=>$t->translate ('')));
		$adminSaveConfig->addTranslation ($tASaveConfig);
		$this->_pluginAdmin->install ($pluginAPI, $dbModule, $siteDefaultLanguage);
		
		$adminLogout = $pageM->newPage ();
		$adminLogout->initFromArray (array (
				'name'=>'MorgOS_Admin_Logout', 
				'parent_page_id'=>$admin->getID (), 
				'action'=>'adminLogout',
				'place_in_menu'=>MORGOS_MENU_LAST));
		$pageM->addPageToDatabase ($adminLogout);	
		
		$tALogout = $pageM->newTranslatedPage ();
		$tALogout->initFromArray (array ('language_code'=>$siteDefaultLanguage, 
				'translated_title'=>$t->translate ('Logout'), 
				'translated_content'=>$t->translate ('Logout')));
		$adminLogout->addTranslation ($tALogout);
	}
	
	function isInstalled (&$pluginAPI) {return true;}
	
	function isCorePlugin () {return true;}
}
?>
