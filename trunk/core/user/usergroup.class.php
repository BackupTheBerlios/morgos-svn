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
 * @ingroup user core
 * @since 0.2
 * @author Nathan Samson
*/

/**
 * A class that represents a group
 *
 * @ingroup user core
 * @since 0.2
 * @author Nathan Samson
*/
class UserGroup extends DBTableObject {
	
	/**
	 * Constructor
	 *
	 * @param $db (object database) the database module
	 * @param $creator (object)
	 * @param $extraFields (dbField array)
	 * @param $extraJoins (dbGenericJoinField array)
	*/
	function UserGroup ($db, &$creator, $extraFields = array (), $extraJoins = array ()) {
		$genericName = new dbField ('generic_name', DB_TYPE_STRING, 255);
		$genericDescription = new dbField ('generic_description', DB_TYPE_TEXT);
		
		parent::DBTableObject ($db, array ($genericName, $genericDescription), 'groups', 'group_id', $creator, $extraFields);
	}
	
	/**
	 * Initializes the group from the genericname.
	 *
	 * @param $genericName (string)
	 * @public
	*/
	function initFromDatabaseGenericName ($genericName) {
		$fullTableName = $this->getFullTableName ();
		$genericName = $this->_db->escapeString ($genericName);
		$sql = "SELECT * FROM $fullTableName WHERE generic_name='$genericName'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			if ($this->_db->numRows ($q)) {
				$row = $this->_db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setField ('ID',$row['group_id']);
			} else {
				return new Error ('GROUP_GENERICNAME_DONT_EXISTS', $genericName);
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
		$permissionName = $this->_db->escapeString ($permissionName);
		$prefix = $this->_db->getPrefix ();
		$ID = $this->getID ();
		$sql = "SELECT enabled FROM {$prefix}groupPermissions WHERE group_id='$ID' AND permission_name='$permissionName'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			if ($this->_db->numRows ($q) == 1) {
				$row = $this->_db->fetchArray ($q);
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
		$permissionName = $this->_db->escapeString ($permissionName);
		$prefix = $this->_db->getPrefix ();
		$ID = $this->getID ();
		$sql = "SELECT enabled FROM {$prefix}groupPermissions WHERE group_id='$ID' AND permission_name='$permissionName'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			if ($this->_db->numRows ($q) == 1) {
				if ($enabled) {
					$enabled = 'Y';
				} else {
					$enabled = 'N';
				}
				$ID = $this->getID ();
				$sql = "UPDATE {$prefix}groupPermissions SET enabled='$enabled' WHERE group_id='$ID' AND permission_name='$permissionName'";
				$q = $this->_db->query ($sql);
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
				$sql = "INSERT INTO {$prefix}groupPermissions (group_id, permission_name, enabled) VALUES ('$groupID', '$permissionName', '$enabled')";
				$q = $this->_db->query ($sql);
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
				$prefix = $this->_db->getPrefix();
				$groupID = $this->getID ();
				$userID = $user->getID ();

				if (! is_numeric ($groupID)) {
					return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
				}
				
				if (! is_numeric ($userID)) {
					return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
				}				
				$sql = "INSERT INTO ".$prefix."groupUsers (group_id, user_id) VALUES ('$groupID', '$userID')";
				$q = $this->_db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
			} else {
				return new Error ('GROUP_USER_ALREADY_IN_GROUP');
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
				$prefix = $this->_db->getPrefix();
				$groupID = $this->getID ();
				$userID = $user->getID ();
				if (! is_numeric ($groupID)) {
					return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
				}
				
				if (! is_numeric ($userID)) {
					return new Error ('DATABASEOBJECT_SQL_INJECTION_ATTACK_FAILED', __FILE__, __LINE__);
				}
				
				$sql = "DELETE FROM ".$prefix."groupUsers WHERE group_id='$groupID' AND user_id='$userID'";
				$q = $this->_db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
			} else {
				return new Error ('GROUP_USER_NOT_IN_GROUP');
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
		$prefix = $this->_db->getPrefix ();
		$groupID = $this->getID ();
		$sql = "SELECT user_id FROM ".$prefix."groupUsers WHERE group_id='$groupID'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$allUsers = array ();
			while ($row = $this->_db->fetchArray ($q)) {
				$allUsers[] = $row['user_id'];
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
		return $this->getFieldValue ('generic_name');
	}
	
	/**
	 * Returns the generic description of the group
	 *
	 * @public
	 * @return (string)
	*/
	function getGenericDescription () {
		return $this->getOptionValue ('generic_description');
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
		$sql = "SELECT translated_group_id FROM ".$prefix."translatedgroups";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$allGroups = array ();
			while ($row = $this->db->fetchArray ()) {
				$allGroups[] = $row['translated_group_id'];
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
		$fullTranslationTableName = $this->_db->getPrefix ().'translatedGroups';
		$ID = $this->getID ();
		$sql = "SELECT language_code FROM $fullTranslationTableName WHERE group_id='$ID' ORDER BY language_code ASC";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$lCodes = array ();
			while ($row = $this->_db->fetchArray ($q)) {
				$lCodes[] = $row['language_code'];
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
				return new Error ('GROUP_TRANSLATION_DOESNT_EXISTS');
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
			$translatedGroup->setField ('group_id', $this->getID ());
			return $translatedGroup->addToDatabase ();
		} else {
			return new Error ('GROUP_TRANSLATION_EXISTS', $translatedGroup->getLanguageCode ());
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
			return new Error ('GROUP_TRANSLATION_DOESNT_EXISTS', $translatedGroup->getLanguageCode ());
		}
	}

}
