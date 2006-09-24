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
	
	function load ($pluginAPI) {
		parent::load ($pluginAPI);
		$this->_pluginAPI->getActionManager ()->addAction (new action ('admin', 'GET',  array (&$this, 'onViewAdmin'), array (), array ('pageID', 'pageLang')));
		$this->_pluginAPI->getActionManager ()->addAction (new action ('adminLogin', 'POST',  array (&$this, 'onLogin'), array ('adminLogin', 'adminPassword'), array ()));
		$this->_pluginAPI->getActionManager ()->addAction (new action ('adminLogout', 'GET',  array (&$this, 'onLogout'), array (), array ()));
		$this->_pluginAPI->getActionManager ()->addAction (new action ('adminPageManager', 'GET',  array (&$this, 'onViewPageManager'), array (), array ('pageID', 'pageLang')));
		
		$this->_pluginAPI->getEventManager ()->addEvent (new event ('viewAnyAdminPage', array ('pageID')));
		$this->_pluginAPI->getEventManager ()->subscribeToEvent ('viewAnyAdminPage', new callback ('setAdminVars', array (&$this, 'setAdminVars'), array ('pageID')));
		
		// page edit action
		$this->_pluginAPI->getActionManager ()->addAction (
			new action ('adminMovePageDown', 'GET',  
				array (&$this, 'onMovePageDown'), array ('pageID'), array ()));
				
		$this->_pluginAPI->getActionManager ()->addAction (
			new action ('adminMovePageUp', 'GET',  
				array (&$this, 'onMovePageUp'), array ('pageID'), array ()));
				
		$this->_pluginAPI->getActionManager ()->addAction (
			new action ('adminSavePage', 'POST',  
				array (&$this, 'onSavePage'), array ('pageID', 'pageTitle', 'pageContent'), array ()));
				
		$this->_pluginAPI->getActionManager ()->addAction (
			new action ('adminNewPage', 'GET',  
				array (&$this, 'onNewPage'), array ('parentPageID', 'pageTitle'), array ()));
	}
	
	function onViewAdmin ($pageID, $pageLang) {
		//$a = $this->_pluginAPI->getEventManager ()->triggerEvent ('viewPage');
		/*foreach ($a as $r) {
			if ($r == false or isError ($r)) {
				return;
			}
		}*/
		
		$userManager = $this->_pluginAPI->getUserManager ();
		$user = $userManager->getCurrentUser ();
		$pageManager = $this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();
		if ($pageID) {
			$page->initFromDatabaseID ($pageID);
		} else {
			$page->initFromGenericName ('Admin Home');
			$pageID = $page->getID ();
		}
		$sm = $this->_pluginAPI->getSmarty ();
		if ($this->canUserViewAdminPage ($page->getID ())) {
			$this->_pluginAPI->getEventManager ()->triggerEvent ('viewAnyAdminPage', array (&$pageID));
			$sm->assign_by_ref ('MorgOS_CurrentAdminPage', $page);
			if ($page->getAction ()) {
				$this->_pluginAPI->getActionManager ()->executeAction ($page->getAction ());
			} else {
				$sm->display ('admin/genericpage.tpl');
			}
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onLogin ($adminLogin, $adminPassword) {
		$userManager = $this->_pluginAPI->getUserManager ();
		$a = $userManager->login ($adminLogin, $adminPassword);
		if (isError ($a)) {
			return $a;
		} else {
			$this->_pluginAPI->addMessage ('You are now logged in.', NOTICE);
			$this->_pluginAPI->doAction ('admin');
		}
	}
	
	function onLogout () {
		$userManager = $this->_pluginAPI->getUserManager ();
		$a = $userManager->logout ();
		if (isError ($a)) {
			return $a;
		} else {
			$this->_pluginAPI->addMessage ('You are logged out.', NOTICE);
			$this->_pluginAPI->doAction ('admin');
		}
	}
	
	function onViewPageManager ($pageID, $pageLang = 'en') {
		$this->_pluginAPI->getEventManager ()->triggerEvent ('viewAnyAdminPage', array (&$pageID));
		$sm = $this->_pluginAPI->getSmarty ();
		$pageManager = $this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();			
		$page->initFromGenericName ('Admin Pagemanager');
		if ($this->canUserViewAdminPage ($page->getID ())) {				
			if ($pageID === NULL) {
				$pageID = 1; /*The ID of site */
			}	
			$parentPage = $pageManager->newPage ();
			$parentPage->initFromDatabaseID ($pageID);
			$childPages = $pageManager->getMenu ($parentPage);
			$sm->assign ('MorgOS_PagesList', $childPages);
			$sm->assign_by_ref ('MorgOS_ParentPage', $parentPage);
			$sm->assign_by_ref ('MorgOS_CurrentAdminPage', $page);
			$sm->display ('admin/pagemanager.tpl'); 
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onMovePageDown ($pageID) {
		$pageManager = $this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();			
		$page->initFromGenericName ('Admin Pagemanager');
		$sm = $this->_pluginAPI->getSmarty ();
		if ($this->canUserViewAdminPage ($page->getID ())) {	
			$r = $pageManager->movePageDown ($pageID);
			if (! isError ($r)) {
				$this->_pluginAPI->executePreviousAction ();
			} elseif ($r->is ("PAGEMANAGER_PAGE_DOESNT_EXISTS")) {
				$this->_pluginAPI->error ($this->_pluginAPI->getLocalizator ()->translate ('Page doesn\'t exists'), true);
			} else {
				$this->_pluginAPI->error ('Onverwachte fout');
			}
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onMovePageUp ($pageID) {
		$pageManager = $this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();			
		$page->initFromGenericName ('Admin Pagemanager');
		$sm = $this->_pluginAPI->getSmarty ();
		if ($this->canUserViewAdminPage ($page->getID ())) {	
			$r = $pageManager->movePageUp ($pageID);
			if (! isError ($r)) {
				$this->_pluginAPI->executePreviousAction ();
			} elseif ($r->is ("PAGEMANAGER_PAGE_DOESNT_EXISTS")) {
				$this->_pluginAPI->error ($this->_pluginAPI->getI18NManager ()->translate ('Page doesn\'t exists'), true);
			} else {
				$this->_pluginAPI->error ('Onverwachte fout', true);
			}
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onSavePage ($pageID, $pageTitle, $pageContent) {
		$pageManager = $this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();			
		$page->initFromGenericName ('Admin Pagemanager');
		$sm = $this->_pluginAPI->getSmarty ();
		if ($this->canUserViewAdminPage ($page->getID ())) {	
			$editedPage = $pageManager->newPage ();
			$editedPage->initFromDatabaseID ($pageID);
			$editedPage->updateFromArray (array ('genericContent'=>$pageContent, 'genericName'=>$pageTitle));
			$editedPage->updateToDatabase ();
			$a = $this->_pluginAPI->executePreviousAction ();
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onNewPage ($parentPageID, $title) {
		$pageManager = $this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();			
		$page->initFromGenericName ('Admin Pagemanager');
		$sm = $this->_pluginAPI->getSmarty ();
		if ($this->canUserViewAdminPage ($page->getID ())) {	
			$pageManager = $this->_pluginAPI->getPageManager ();
			$newPage = $pageManager->newPage ();
			$ap = array ('genericName'=>$title, 'parentPageID'=>$parentPageID, 'genericContent'=>$this->_pluginAPI->getI18NManager ()->translate ('A newly created page.'));
			$newPage->initFromArray ($ap);
			$pageManager->addPageToDatabase ($newPage);
			$a = $this->_pluginAPI->executePreviousAction ();
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function setAdminVars ($pageID) {
		$sm = $this->_pluginAPI->getSmarty ();	
	
		$pageManager = $this->_pluginAPI->getPageManager ();
		$rootPage = $pageManager->newPage ();
		$rootPage->initFromGenericName ('admin');
		$adminNav = $pageManager->getMenu ($rootPage);
		
		$sm->assign_by_ref ('MorgOS_AdminNav', $adminNav);
	}
	
	function canUserViewAdminPage ($pageID) {
		$userM = $this->_pluginAPI->getUserManager ();
		$user = $userM->getCurrentUser ();
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
