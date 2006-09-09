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
 * This is the viewPage class.
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
		if ($user) {
			if ($user->hasPermission ('read_admin')) {
				$pageManager = $this->_pluginAPI->getPageManager ();
				$page = $pageManager->newPage ();
				if ($pageID) {
					$page->initFromDatabaseID ($pageID);
				} else {
					$page->initFromGenericName ('Admin Home');
				}
				$sm = $this->_pluginAPI->getSmarty ();
				$sm->assign_by_ref ('MorgOS_CurrentAdminPage', $page);
				if ($page->getScript ()) {
					$sm->display ($page->getScript ());
				} elseif ($page->getLink ()) {
				} else {
					$sm->display ('admin/genericpage.tpl');
				}
			} else {
				return "ERROR_PLUGIN_NOT_PERMISSION";
			}
		} else {
			$sm = $this->_pluginAPI->getSmarty ();
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onLogin ($adminLogin, $adminPassword) {
		$userManager = $this->_pluginAPI->getUserManager ();
		$a = $userManager->login ($adminLogin, $adminPassword);
		if (isError ($a)) {
			return $a;
		} else {
			$this->_pluginAPI->doAction ('admin');
		}
	}
	
	function onLogout () {
		$userManager = $this->_pluginAPI->getUserManager ();
		$a = $userManager->logout ();
		if (isError ($a)) {
			return $a;
		} else {
			$this->_pluginAPI->doAction ('admin');
		}
	}
	
	function onViewPageManager ($pageID, $pageLang) {
		$sm = $this->_pluginAPI->getSmarty ();
		if ($this->_pluginAPI->userCanViewPage ()) {
			$pageManager = $this->_pluginAPI->getPageManager ();
			$page = $pageManager->newPage ();			
			$page->initFromGenericName ('Admin Pagemanager');
			
			$parentPage = $pageManager->newPage ();
			$parentPage->initFromDatabaseID ($pageID);
			$childPages = $parentPage->getAllChilds (); 
			$sm->assign ('MorgOS_PagesList', $childPages);
			$sm->assign_by_ref ('MorgOS_ParentPage', $parentPage);
			$sm->assign_by_ref ('MorgOS_CurrentAdminPage', $page);
			$sm->display ('admin/pagemanager.tpl'); 
		} else {
			$sm->display ('admin/login.tpl');
		}
	}
}
?>