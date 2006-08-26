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
	
	/**
	 * Constructor
	 *
	 * @param $db (object database) the database module
	 * @param $allExtraOptions (null array) an array with empty values. The keys are the extra options.
	 * @param $creator (object)
	*/
	function group ($db, $allExtraOptions, &$creator) {
		$genericName = new dbField ();
		$genericName->name = 'genericName';
		$genericName->type = 'varchar (255)';

		$genericDescription = new dbField ();
		$genericDescription->name = 'genericDescription';
		$genericDescription->type = 'text';
		
		parent::databaseObject ($db, $allExtraOptions, array ('genericName'=>$genericName, 'genericDescription'=>$genericDescription), 'groups', 'groupID', $creator);
	}
	
	/**
	 * Initializes the group from the genericname.
	 *
	 * @param $genericName (string)
	 * @public
	*/
	function initFromDatabaseGenericName ($genericName) {
		$fullTableName = $this->getFullTableName ();
		$genericName = $this->db->escapeString ($genericName);
		$sql = "SELECT * FROM $fullTableName WHERE genericName='$genericName'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q)) {
				$row = $this->db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setOption ('ID',$row['groupID']);
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
	
	/**
	 * Adds a user to the group.
	 *
	 * @param $user (object user) The user to add.
	 * @public
	*/
	function addUserToGroup ($user) {
		$isInGroup = $this->isUserInGroup ($user); 
		if (! isError ($isInGroup)) {
			if ($isInGroup == false) {
				$prefix = $this->db->getPrefix();
				$groupID = $this->getID ();
				$userID = $user->getID ();
				if (! is_numeric ($groupID)) {
					return "ERROR_DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED __FILE__::__LINE__";
				}
				
				if (! is_numeric ($userID)) {
					return "ERROR_DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED __FILE__::__LINE__";
				}				
				$sql = "INSERT INTO ".$prefix."group_users (groupID, userID) VALUES ('$groupID', '$userID')";
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
	
	/**
	 * Removes a user from a group
	 *
	 * @param $user (object user) The user to remove.
	 * @public
	*/
	function removeUserFromGroup ($user) {
		$isInGroup = $this->isUserInGroup ($user); 
		if (! isError ($isInGroup)) {
			if ($isInGroup == true) {
				$prefix = $this->db->getPrefix();
				$groupID = $this->getID ();
				$userID = $user->getID ();
				if (! is_numeric ($groupID)) {
					return "ERROR_DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED __FILE__::__LINE__";
				}
				
				if (! is_numeric ($userID)) {
					return "ERROR_DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED __FILE__::__LINE__";
				}
				
				$sql = "DELETE FROM ".$prefix."group_users WHERE groupID='$groupID' AND userID='$userID'";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
			} else {
				return "ERROR_GROUP_USER_NOT_IN_GROUP";
			}
		} else {
			return $isInGroup;			
		}
	}
	
	/**
	 * Returns all users that are in this group.
	 *
	 * @public
	 * @return (object user array)
	*/
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
	
	/**
	 * Return all IDS of users that are in the group
	 *
	 * @public
	 * @return (int array)
	*/
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
	
	/**
	 * Returns if a specific user is in the group.
	 *
	 * @param $user (object user)
	 * @public
	 * @return (bool)
	*/
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
	
	/**
	 * Returns the generic name of the group
	 *
	 * @public
	 * @return (string)
	*/
	function getGenericName () {
		return $this->getOption ('genericName');
	}
	
	/**
	 * Returns the generic description of the group
	 *
	 * @public
	 * @return (string)
	*/
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
	
	/**
	 * Returns all translated groups that are part of this group
	 *
	 * @public
	 * @return (object translatedGroup array)
	*/
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
	
	/**
	 * Returns all IDS of groups
	 *
	 * @public
	 * @return (int array)
	*/
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
	
	/**
	 * Creates a new translated group.
	 *
	 * @public
	 * @return (object translatedGroup)
	*/
	function newTranslatedGroup () {
		return new translatedGroup ($this->db, $this->getAllOptions, $this);
	}
	
	/**
	 * Adds a translated group to this group.
	 *
	 * @param $translatedGroup (object translatedGroup)
	 * @public
	*/
	function addTranslatedGroupToDatabase ($translatedGroup) {
		return $translatedGroup->addToDatabase ();
	}
	
	/**
	 * Removes a translated group from this group.
	 *
	 * @param $translatedGroup (object translatedGroup)
	 * @public
	*/
	function removeTranslatedGroupFromDatabase ($translatedGroup) {
		return $translatedGroup->removeFromDatabase ();
	}

}
