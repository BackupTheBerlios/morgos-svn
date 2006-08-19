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
		parent::databaseObject ($db, $allExtraOptions, array ('genericName', 'genericDescription'), 'groups', 'groupID', $creator);
	}
	
	function initFromDatabaseGenericName ($genericName) {
		$fullTableName = $this->getFullTableName ();
		$sql = "SELECT * FROM $fullTableName WHERE genericName='$genericName'";
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
		$isInGroup = $this->isUserInGroup ($user); 
		if (! isError ($isInGroup)) {
			if ($isInGroup == false) {
				$prefix = $this->db->getPrefix();
				$groupID = $this->getID ();
				$userID = $user->getID ();
				$sql = "INSERT INTO ".$prefix."group_users (groupID, userID) VALUES ('.$groupID', '$userID')";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
			} else {
				return "ERROR_GROUP_USER_ALREADY_IN_GROUP";
			}
		} else {
			return $isInGroup;			
		}
	}
	
	function removeUserFromGroup ($user) {
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
		$prefix = $this->db->getPrefix ();
		$groupID = $this->getID ();
		$sql = "SELECT userID FROM ".$prefix."group_users WHERE groupID='$groupID'";
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
		$prefix = $this->db->getPrefix ();
		$sql = "SELECT translatedGroupID FROM ".$prefix."translatedgroups";
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