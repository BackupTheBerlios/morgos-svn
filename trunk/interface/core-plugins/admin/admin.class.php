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
class adminCorePlugin extends Plugin {
	var $_pluginAdmin;
	
	function adminCorePlugin ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Admin core plugin';
		$this->_ID = MORGOS_ADMIN_PLUGINID;
		$this->_minMorgOSVersion = MORGOS_VERSION;
		$this->_maxMorgOSVersion = MORGOS_VERSION;
		$this->_version = MORGOS_VERSION;
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		include_once ($this->_loadedDir.'/adminpluginplugin.class.php');
		$this->_pluginAdmin = new adminCorePluginAdminPlugin ($this->_loadedDir);
		$this->_pluginAdmin->load ($pluginAPI);
		$am = &$this->_pluginAPI->getActionManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		$am->addAction (
			new action ('admin', 'GET',  array (&$this, 'onViewAdmin'), 
				array (), array (new IDInput ('pageID'), new LocaleInput ('pageLang'))));
		$am->addAction (
			new action ('adminLogin', 'POST',  array (&$this, 'onLogin'), 
			array (new StringInput ('adminLogin'), new StringInput ('adminPassword')), array ()));
		$am->addAction (
			new action ('adminLogout', 'GET',  array (&$this, 'onLogout'), array (), array ()));
			
		$em->addEvent (new event ('viewAnyAdminPage', array ('pageID')));
		$em->subscribeToEvent ('viewAnyAdminPage', 
			new callback ('setAdminVars', array (&$this, 'setAdminVars'), array ('pageID')));
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
		
		if ($pageLang === null) {
			$pageLang = $this->_pluginAPI->getUserSetting ('pageLang');
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
			$this->_pluginAPI->addRuntimeMessage ($t->translate ('Login as a valid admin user to view this page.'), NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onLogin ($adminLogin, $adminPassword) {
		$userManager = &$this->_pluginAPI->getUserManager ();
		$a = $userManager->login ($adminLogin, $adminPassword);
		$t = &$this->_pluginAPI->getI18NManager ();
		if (isError ($a)) {
			if ($a->is ('USERMANAGER_LOGIN_FAILED_INCORRECT_INPUT')) {
				$sm = &$this->_pluginAPI->getSmarty ();
				$this->_pluginAPI->addRuntimeMessage ($t->translate ('Given a wrong password/username.'), ERROR);
				$sm->display ('admin/login.tpl');
			} else {
				return $a;
			}
		} else {
			$this->_pluginAPI->addMessage ($t->translate ('You are now logged in.'), NOTICE);
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
			$this->_pluginAPI->addMessage ($t->translate ('You are logged out.'), NOTICE);
			$this->_pluginAPI->doAction ('admin');
		}
	}
	
	function setAdminVars ($pageID) {
		$sm = &$this->_pluginAPI->getSmarty ();	
		$pageManager = &$this->_pluginAPI->getPageManager ();
		
		$pageLang = $this->_pluginAPI->getUserSetting ('pageLang');	
		
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
		$sm->assign_by_ref ('MorgOS_CurrentAdminPage', $tpagearray);
		return true;
	}
	
	function isCorePlugin () {return true;}
}
?>
