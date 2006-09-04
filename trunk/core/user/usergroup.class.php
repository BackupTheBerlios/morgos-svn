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
	
	/**
	 * Returns that a group (or better the users) has a permission
	 *
	 * @param $permissionName (string)
	 * @public
	 * @return (bool)
	*/
	function hasPermission ($permissionName) {
		$permissionName = $this->db->escapeString ($permissionName);
		$sql = "SELECT enabled FROM {$this->db->getPrefix ()}groupPermissions WHERE groupID='{$this->getID ()}' AND permissionName='$permissionName'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				$row = $this->db->fetchArray ($q);
				if ($row['enabled'] == 'Y') {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Assign a permission to a group
	 *
	 * @param $permissionName (string)
	 * @param $enabled (bool) If true group can do it.
	 * @public
	*/
	function assignPermission ($permissionName, $enabled) {
		$permissionName = $this->db->escapeString ($permissionName);
		$sql = "SELECT enabled FROM {$this->db->getPrefix ()}groupPermissions WHERE groupID='{$this->getID ()}' AND permissionName='$permissionName'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				if ($enabled) {
					$enabled = 'Y';
				} else {
					$enabled = 'N';
				}
				$sql = "UPDATE {$this->db->getPrefix ()}groupPermissions SET enabled='$enabled' WHERE groupID='{$this->getID ()}' AND permissionName='$permissionName'";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
			} else {
				if ($enabled) {
					$enabled = 'Y';
				} else {
					$enabled = 'N';
				}
				$groupID = $this->getID ();
				$sql = "INSERT INTO {$this->db->getPrefix ()}groupPermissions (groupID, permissionName, enabled) VALUES ('$groupID', '$permissionName', '$enabled')";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
			}
		} else {
			return $q;
		}
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
	 * Checks that a translation already exists.
	 *
	 * @param $lCode (string) the languageCode
	 * @public
	 * @return (bool)
	*/
	function existsTranslatedGroup ($lCode) {
		if (in_array ($lCode, $this->getAllTranslations ())) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns all languagecodes the group is translated to.
	 *
	 * @public
	 * @return (string array)
	*/
	function getAllTranslations () {
		$fullTranslationTableName = $this->db->getPrefix ().'translatedGroups';
		$sql = "SELECT languageCode FROM $fullTranslationTableName WHERE groupID='{$this->getID ()}' ORDER BY languageCode ASC";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$lCodes = array ();
			while ($row = $this->db->fetchArray ($q)) {
				$lCodes[] = $row['languageCode'];
			}
			return $lCodes;
		} else {
			return $q;
		}
	}
	
	/**
	 * Returns a specific translation of the group.
	 *
	 * @param $lCode (string) If not found the main language translation is returned.
	 * @public
	 * @return (object translatedGroup)
	*/
	function getTranslation ($lCode) {
		if ($this->existsTranslatedGroup ($lCode)) {
			$c = $this->getCreator ();
			$tPage = $c->newTranslatedGroup ();
			$tPage->initFromDatabaseGroupIDandLanguageCode ($this->getID (), $lCode);
			return $tPage;
		} else {
			if (strlen ($lCode) > 2) {
				$lLang = substr ($lCode, 0, 2);
				return $this->getTranslation ($lLang);
			} else {
				return "ERROR_GROUP_TRANSLATION_DOESNT_EXISTS";
			}
		}
	}
	
	/**
	 * Adds a translated group to this group.
	 *
	 * @param $translatedGroup (object translatedGroup)
	 * @public
	*/
	function addTranslationToDatabase ($translatedGroup) {
		if (! $this->existsTranslatedGroup ($translatedGroup->getLanguageCode ())) {
			$translatedGroup->setOption ('groupID', $this->getID ());
			return $translatedGroup->addToDatabase ();
		} else {
			return "ERROR_GROUP_TRANSLATION_EXISTS {$translatedGroup->getLanguageCode ()}";
		}
	}
	
	/**
	 * Removes a translated group from this group.
	 *
	 * @param $translatedGroup (object translatedGroup)
	 * @public
	*/
	function removeTranslationFromDatabase ($translatedGroup) {
		if ($this->existsTranslatedGroup ($translatedGroup->getLanguageCode ())) {
			return $translatedGroup->removeFromDatabase ();
		} else {
			return "ERROR_GROUP_TRANSLATION_DOESNT_EXISTS {$translatedGroup->getLanguageCode ()}";
		}
	}

}
