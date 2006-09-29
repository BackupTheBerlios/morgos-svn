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
class adminCorePlugin extends plugin {
	
	function adminCorePlugin ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Admin core plugin';
		$this->_ID = MORGOS_ADMIN_PLUGINID;
		$this->_minMorgOSVersion = '0.2';
		$this->_maxMorgOSVersion = '0.2';
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		$am = &$this->_pluginAPI->getActionManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		$am->addAction (
			new action ('admin', 'GET',  array (&$this, 'onViewAdmin'), 
				array (), array ('pageID', 'pageLang')));
		$am->addAction (
			new action ('adminLogin', 'POST',  array (&$this, 'onLogin'), 
			array ('adminLogin', 'adminPassword'), array ()));
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
		$page = $pageManager->newPage ();
		$am = &$this->_pluginAPI->getActionManager ();
		if ($pageID) {
			$page->initFromDatabaseID ($pageID);
		} else {
			$page->initFromName ('Admin Home');
			$pageID = $page->getID ();
		}
		$sm = &$this->_pluginAPI->getSmarty ();
		$em = &$this->_pluginAPI->getEventManager ();
		if ($this->canUserViewAdminPage ($page->getID ())) {
			$em->triggerEvent ('viewAnyAdminPage', array (&$pageID));
			if ($pageLang == null) {
				$pageLang = 'en_UK';
			}
			$tpage = $page->getTranslation ($pageLang);
			$tpagearray = array ('Title'=>$tpage->getTitle (), 'Content'=>$tpage->getContent ());
			$sm->assign_by_ref ('MorgOS_CurrentAdminPage', $tpagearray);
			if ($page->getAction ()) {
				$am->executeAction ($page->getAction ());
			} else {
				$sm->display ('admin/genericpage.tpl');
			}
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onLogin ($adminLogin, $adminPassword) {
		$userManager = &$this->_pluginAPI->getUserManager ();
		$a = $userManager->login ($adminLogin, $adminPassword);
		if (isError ($a)) {
			if ($a->is ('USERMANAGER_LOGIN_FAILED_INCORRECT_INPUT')) {
				$sm = &$this->_pluginAPI->getSmarty ();
				$this->_pluginAPI->addRuntimeMessage ('Given a wrong password/username.', ERROR);
				$sm->display ('admin/login.tpl');
			} else {
				return $a;
			}
		} else {
			$this->_pluginAPI->addMessage ('You are now logged in.', NOTICE);
			$this->_pluginAPI->doAction ('admin');
		}
	}
	
	function onLogout () {
		$userManager = &$this->_pluginAPI->getUserManager ();
		$a = $userManager->logout ();
		if (isError ($a)) {
			return $a;
		} else {
			$this->_pluginAPI->addMessage ('You are logged out.', NOTICE);
			$this->_pluginAPI->doAction ('admin');
		}
	}
	
	function setAdminVars ($pageID) {
		$sm = &$this->_pluginAPI->getSmarty ();	
	
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$rootPage = $pageManager->newPage ();
		$rootPage->initFromName ('admin');
		$adminNav = $this->_pluginAPI->menuToArray ($pageManager->getMenu ($rootPage));
		
		$sm->assign_by_ref ('MorgOS_AdminNav', $adminNav);
	}
	
	function canUserViewAdminPage ($pageID) {
		$userM = &$this->_pluginAPI->getUserManager ();
		$user = &$userM->getCurrentUser ();
		if ($user) {
			if ($user->hasPermission ('edit_admin', false)) {
				return true;
			} else {
				return $user->hasPermission ('edit_admin_'.$pageID, true);
			}
		} else {
			return false;
		}
	}
}
?>
