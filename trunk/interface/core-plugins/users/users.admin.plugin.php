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
			'adminUserManager', 'GET', array ($this, 'onViewUserManager'), array (), array (), 'MorgOS_Admin_UserManager'));
		$am->addAction (new action (
			'adminMakeUserAdmin', 'POST', array ($this, 'onMakeUserAdmin'), array (new IDInput ('userID')), array (), 'MorgOS_Admin_UserManager'));
		$am->addAction (new action (
			'adminMakeUserNormal', 'POST', array ($this, 'onMakeUserNormal'), array (new IDInput ('userID')), array (), 'MorgOS_Admin_UserManager'));
		$am->addAction (new action (
			'adminUserDelete', 'GET', array ($this, 'onDeleteUser'), array (new IDInput ('userID')), array (), 'MorgOS_Admin_UserManager'));
			
	}
	
	function onViewUserManager () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$eventM = &$this->_pluginAPI->getEventManager ();				
		$sm->assign ('MorgOS_Current_Admins', $this->getCurrrentAdmins ());
		$sm->assign ('MorgOS_All_Users', $this->getAllNormalUsers ());
		$sm->appendTo ('MorgOS_AdminPage_Content', 
			$sm->fetch ('admin/user/usermanager.tpl'));
		$sm->display ('admin/genericpage.tpl');
		
	}
	
	function onMakeUserAdmin ($userID) {
		$userM = &$this->_pluginAPI->getUserManager ();
		$t = &$this->_pluginAPI->getI18NManager ();		
		
		$user = $userM->newUser ();
		$user->initFromDatabaseID ($userID);
		$adminGroup = $userM->newGroup ();
		$adminGroup->initFromDatabaseGenericName ('administrator');
		$user->addToGroup ($adminGroup);
			
		$this->_pluginAPI->addMessage ($t->translate ('User is new administrator'), NOTICE);
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onMakeUserNormal ($userID)  {
		$sm = &$this->_pluginAPI->getSmarty ();
		$userM = &$this->_pluginAPI->getUserManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$user = $userM->newUser ();
		$user->initFromDatabaseID ($userID);
		$adminGroup = $userM->newGroup ();
		$adminGroup->initFromDatabaseGenericName ('administrator');
		$user->removeFromGroup ($adminGroup);
		
		$this->_pluginAPI->addMessage ($t->translate ('User is again a normal user'), NOTICE);
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onDeleteUser ($userID) {
		$userM = &$this->_pluginAPI->getUserManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$user = $userM->newUser ();
		$user->initFromDatabaseID ($userID);
		$userM->removeUserFromDatabase ($user);
			
		$this->_pluginAPI->addMessage ('User is deleted', NOTICE);
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function getCurrrentAdmins () {
		$userM = &$this->_pluginAPI->getUserManager ();
		$db = &$this->_pluginAPI->getDBModule ();
		$tPrefix = &$db->getPrefix ();
		$adminGroup = $userM->newGroup ();
		$adminGroup->initFromDatabaseGenericName ('administrator');
		$adminID = $adminGroup->getID ();
		$sql = "SELECT user_id FROM {$tPrefix}groupUsers WHERE group_id=$adminID";
		$q = $db->query ($sql);
		$admins = array ();
		$currentUser = $userM->getCurrentUser ();
		while ($row = $db->fetchArray ($q)) {
			$admin = $userM->newUser ();
			$a = $admin->initFromDatabaseID ($row['user_id']);
			if (isError ($a)) {
				continue;
			}
			if ($admin->getID () == $currentUser->getID ())  {
				$isCurrent = true;
			} else {
				$isCurrent = false;
			}
			$adminArray = array ('Login'=>$admin->getLogin (), 'ID'=>$admin->getID (), 'IsCurrent'=>$isCurrent);
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
