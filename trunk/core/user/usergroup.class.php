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
/** \file usergroup.class.php
 * File that take care of one group
 *
 * @since 0.2
 * @author Nathan Samson
*/

class group extends databaseObject {
	
	function group ($db, $allExtraOptions, &$creator) {
		parent::databaseObject ($db, $allExtraOptions, array ('genericName', 'genericDescription'), 'groups', 'groupID', &$creator);
	}
	
	function initFromDatabaseGenericName ($genericName) {
		$sql = "SELECT * FROM {$this->db->getPrefix ()}groups WHERE genericName='$genericName'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q)) {
				$row = $this->db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->ID = $row['groupID'];
			} else {
				return "ERROR_GROUP_GENERICNAME_DONT_EXISTS $genericName";
			}
		} else {
			return $q;
		}
	}
	
	/*Public functions*/
	
	function hasPermission ($permissionName) {
	}
	
	function addUserToGroup ($user) {
		if (! $this->isUserInGroup ($user->getID ())) {
			$sql = "INSERT INTO {$this->db->getPrefix()}group_users (groupID, userID) VALUES ('{$group->getID ()}', '{$user->getID ()}')";
			$q = $this->db->query ($q);
			if (isError ($q)) {
				return $q;
			}
		} else {
			return "ERROR_GROUP_USER_ALREADY_IN_GROUP";
		}
	}
	
	function getAllUsers () {
		$allIDS = $this->getAllUsersID ();
		$allUsers = array ();
		foreach ($allIDS as $ID) {
			$newUser = $this->getParent->newUser ();
			$newUser->initFromDatabaseID ($ID);
			$allUsers[] = $newUser;
		}
		return $allUsers;
	}
	
	function getAllUsersID () {
		$sql = "SELECT userID FROM {$this->db->getPrefix ()}group_users WHERE groupID='{$this->getID ()}'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$allUsers = array ();
			while ($row = $this->db->fetchArray ($q)) {
				$allUsers[] = $row['userID'];
			}
			return $allUsers;
		} else {
			return $q;
		}
	}
	
	function isUserInGroup ($user) {
		$allIDS = $this->getAllUsersID ();
		if (! isError ($allIDS)) {
			foreach ($allIDS as $ID) {
				if ($user->getID () == $ID) {
					return true;
				}
			}
			return false;
		} else {
			return $allIDS;
		}
	}
	
	function getGenericName () {
		return $this->getOption ('genericName');
	}
	
	function getGenericDescription () {
		return $this->getOption ('genericDescription');
	}	
	
	function getAllOptionsForTranslatedGroup () {
		return array ();
	}
	
	function addOptionForTranslatedGroup () {
	}
	
	function removeOptionForTranslatedGroup () {
	}
	
	function getAllTranslatedGroups () {
		$allTranslatedGroupsIDS = $this->getAllTranslatedGroupsID ();
		if (! isError ($allTranslatedGroupsIDS)) {
			$allGroups = array ();
			foreach ($allTranslatedGroupsIDS as $translatedGroupID) {
				$groups = $this->newTranslatedGroup ();
				$groups->initFromDatabaseID ($translatedGroupID);
				$allGroups[] = $groups;
			}
			return $allGroups;
		} else {
			return $allTranslatedGroupsIDS;
		}
	}
	
	function getAllTranslatedGroupsID () {
		$sql = "SELECT translatedGroupID FROM {$this->db->getPrefix ()}translatedgroups";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$allGroups = array ();
			while ($row = $this->db->fetchArray ()) {
				$allGroups[] = $row['translatedGroupID'];
			}
			return $allGroups;
		} else {
			return $q;
		}
	}
	
	function newTranslatedGroup () {
		return new translatedGroup ($this->db, $this->getAllOptions, $this);
	}
	
	function addTranslatedGroupToDatabase ($translatedGroup) {
		return $translatedGroup->addToDatabase ();
	}
	
	function removeTranslatedGroupFromDatabase ($translatedGroup) {
		return $translatedGroup->removeFromDatabase ();
	}

}