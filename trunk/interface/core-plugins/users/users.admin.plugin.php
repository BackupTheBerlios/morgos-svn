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
class adminCoreUserAdminPlugin extends plugin {
	
	function adminCoreUserAdminPlugin ($dir) {
		parent::plugin ($dir);
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		$am = &$this->_pluginAPI->getActionManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		
		$am->addAction (new action (
			'adminUserManager', 'GET', array ($this, 'onViewUserManager'), array (), array ()));
		$am->addAction (new action (
			'adminMakeUserAdmin', 'POST', array ($this, 'onMakeUserAdmin'), array (new IDInput ('userID')), array ()));
		$am->addAction (new action (
			'adminMakeUserNormal', 'POST', array ($this, 'onMakeUserNormal'), array (new IDInput ('userID')), array ()));
			
	}
	
	function onViewUserManager () {
		$em = &$this->_pluginAPI->getEventManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$plugM = &$this->_pluginAPI->getPluginManager ();
		$dbM = &$this->_pluginAPI->getDBModule ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('MorgOS_Admin_UserManager');
		$pageID = $page->getID ();

		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$em->triggerEvent ('viewAnyAdminPage', array (&$pageID));				
			
			$sm->assign ('MorgOS_Current_Admins', $this->getCurrrentAdmins ());
			$sm->assign ('MorgOS_All_Users', $this->getAllNormalUsers ());
				
			$sm->display ('admin/usermanager.tpl');
		} else {
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onMakeUserAdmin ($userID) {
		$em = &$this->_pluginAPI->getEventManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$userM = &$this->_pluginAPI->getUserManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('MorgOS_Admin_UserManager');
		$pageID = $page->getID ();

		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$user = $userM->newUser ();
			$user->initFromDatabaseID ($userID);
			$adminGroup = $userM->newGroup ();
			$adminGroup->initFromDatabaseGenericName ('administrator');
			$user->addToGroup ($adminGroup);
			
			$this->_pluginAPI->addMessage ('User is new administrator', NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		} else {
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onMakeUserNormal ($userID)  {
		$em = &$this->_pluginAPI->getEventManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$userM = &$this->_pluginAPI->getUserManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('MorgOS_Admin_UserManager');
		$pageID = $page->getID ();

		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$user = $userM->newUser ();
			$user->initFromDatabaseID ($userID);
			$adminGroup = $userM->newGroup ();
			$adminGroup->initFromDatabaseGenericName ('administrator');
			$user->removeFromGroup ($adminGroup);
			
			$this->_pluginAPI->addMessage ('User is again a normal user', NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		} else {
			$sm->display ('admin/login.tpl');
		}
	}
	
	function getCurrrentAdmins () {
		$userM = &$this->_pluginAPI->getUserManager ();
		$db = &$this->_pluginAPI->getDBModule ();
		$tPrefix = &$db->getPrefix ();
		$adminGroup = $userM->newGroup ();
		$adminGroup->initFromDatabaseGenericName ('administrator');
		$adminID = $adminGroup->getID ();
		$sql = "SELECT userID FROM {$tPrefix}group_users WHERE groupID=$adminID";
		$q = $db->query ($sql);
		$admins = array ();
		while ($row = $db->fetchArray ($q)) {
			$admin = $userM->newUser ();
			$admin->initFromDatabaseID ($row['userID']);
			$adminArray = array ('Login'=>$admin->getLogin (), 'ID'=>$admin->getID ());
			//$permissions = array ('UserManager'=>'Y', 'PluginManager'=>'N');
			//$adminArray['Permissions'] = $permissions;
			$admins[] = $adminArray;
		}
		return $admins;
	}
	
	function getAllNormalUsers () {
		$userM = &$this->_pluginAPI->getUserManager ();
		$db = &$this->_pluginAPI->getDBModule ();
		$tPrefix = &$db->getPrefix ();
		$adminGroup = $userM->newGroup ();
		$adminGroup->initFromDatabaseGenericName ('administrator');
		$adminID = $adminGroup->getID ();
		$allUsers = $userM->getAllUsers ();
		$users = array ();
		foreach ($allUsers as $user) {
			if (! $user->hasPermission ('edit_admin', false)) {
				$users[] = array ('Login'=>$user->getLogin (), 'ID'=>$user->getID ());
			}
		}
		return $users;
	}
}
