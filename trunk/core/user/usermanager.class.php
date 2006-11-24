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
/** \file usermanager.class.php
 * File that take care of the users
 *
 * @since 0.2
 * @author Nathan Samson
*/
session_start ();

/**
 * Manage the users
 *
 * @defgroup user User
*/

include_once ('core/user/user.class.php');
include_once ('core/user/usergroup.class.php');
include_once ('core/user/usertranslatedgroups.class.php');

/**
 * A class that represents a permisson
 *
 * @ingroup user core
 * @since 0.3
 * @author Nathan Samson
*/
class GroupPermission extends DBTableObject {

	function GroupPermission ($db, &$creator) {
		$enabled = new dbEnumField ('enabled', DB_TYPE_ENUM, 'Y', 'N');
		$permName = new dbField ('permission_name', DB_TYPE_STRING, 255);
		$groupID = new dbField ('group_id', DB_TYPE_INT, 255);
		$groupJoin = new oneToOneJoinField ('group', 'groups', 'group_id', $groupID);
		
		parent::DBTableObject ($db, array ($enabled, $permName, $groupID), 
			'groupPermissions', 'permission_id', $creator);
	}

}


/**
 * The User manager
 *
 * @ingroup user core
 * @since 0.2
 * @author Nathan Samson
*/
class UserManager extends DBTableManager {
	/**
	 * The constructor
	 *
	 * @param &$db (dbModule object)
	*/
	function UserManager (&$db) {
		parent::DBTableManager (&$db, 'users', 'User', 
			'groups', 'UserGroup', 
			'translatedGroups', 'UserTranslatedGroup', 
			'groupPermissions', 'GroupPermission');
	}
	
	/*Public functions*/
	/*User functions*/
	
	/**
	 * Creates a user object. This is the only good method to create one.
	 * Do not use new user (); directly.
	 *
	 * @return (object user)
	 * @public
	*/
	function newUser () {
		return $this->createObject ('users');
	}	
	
	/**
	 * Check that an username is already registered.
	 * 
	 * @param $login (string) the login.
	 * @return (bool). True if user exists, false if not
	 * @public
	*/
	function loginIsRegistered ($login) {
		$prefix = $this->_db->getPrefix ();
		$login = $this->_db->escapeString ($login);
		$sql = "SELECT COUNT(login) AS logins FROM ".$prefix."users WHERE login='$login'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$row = $this->_db->fetchArray ($q);
			if ($row['logins'] == 0) {
				return false;
			} else {
				return true;
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Check that an email is already registered.
	 * 
	 * @param $email (string) the email.
	 * @return (bool). True if email exists, false if not
	 * @public
	*/
	function emailIsRegistered ($email) {
		$prefix = $this->_db->getPrefix ();
		$email = $this->_db->escapeString ($email);
		$sql = "SELECT COUNT(email) AS emails FROM ".$prefix."users WHERE email='$email'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$row = $this->_db->fetchArray ($q);
			if ($row['emails'] == 0) {
				return false;
			} else {
				return true;
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Insert an user into the database.
	 *
	 * @since 0.3 It creates automatically a group with the same name, and add the user
	 *  to this group.
	 * @param $user (object user)  The user.
	 * @public
	*/
	function addUserToDatabase (&$user) {
		$lIR = $this->loginIsRegistered ($user->getLogin ()); 
		if ($lIR == false) {
			$eIR = $this->emailIsRegistered ($user->getEmail ()); 
			if ($eIR == false) {
				$group = $this->newGroup ();
				$group->initFromArray (array (
					'generic_name'=>$user->getLogin (),
					'generic_description'=>$user->getLogin ()));
				$a = $this->addGroupToDatabase ($group);
				$user->addToDatabase ();
				$user->addToGroup ($group);
			} else {
				if (! isError ($eIR)) {
					$email = $user->getEmail ();
					return new Error ('EMAIL_ALREADY_REGISTERED', $email);
				} else {
					return $eIR;
				}
			}
		} else {
			if (! isError ($lIR)) {
				$login = $user->getLogin ();
				return new Error ('LOGIN_ALREADY_REGISTERED', $login);
			} else {
				return $lIR;
			}
		}
	}
	
	/**
	 * Removes an user from the database.
	 *
	 * @param $user (object user) The user to delete.
	 * @since 0.3 It deleteses also the associated group.
	 * @public
	*/
	function removeUserFromDatabase ($user) {
		$r = $user->removeFromDatabase ();
		if (! isError ($r)) {
			$group = $this->newGroup ();
			$group->initFromDatabaseGenericName ($user->getLogin ());
			return $this->removeGroupFromDatabase ($group);
		} else {
			return $r;
		}
	}
	
	/**
	 * Returns an array of all users that are stored in the database.
	 *
	 * @warning newly created users that aren't yet stored in the DB are not given here.
	 * @return (int array)
	 * @public
	*/
	function getAllUsers () {
		return $this->getAllRowsFromTable ('users');
	}
	
	/**
	 * Returns the current logged in user. If not logged in returns null.
	 * @public
	 * @return (object user|null)
	*/
	function getCurrentUser () {
		if ($this->isLoggedIn ()) {
			$u = $this->newUser ();
			$u->initFromDatabaseID ($_SESSION['userID']);
			if ($u->isValidPassword ($_SESSION['userPassword'])) {
				return $u;
			} else {
				return new Error ('SESSION_LOGIN_FAILED_INCORRECT_VALUES');
			}
		} else {
			return null;
		}
	}
	
	/**
	 * Checks that the current user is logged in.
	 * @warning Does not try of it is a valid login.
	 * @public
	 * @return (bool)
	*/
	function isLoggedIn () {
		if (array_key_exists ('userID', $_SESSION)) {
			if (array_key_exists ('userPassword', $_SESSION)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Logs a user in.
	 *
	 * @param $login (string)
	 * @param $password (string)
	*/
	function login ($login, $password) {
		$u = $this->newUser ();
		$a = $u->initFromDatabaseLogin ($login);
		if (! isError ($a)) {
			if ($u->isValidPassword ($password)) {
				$_SESSION['userID'] = $u->getID ();
				$_SESSION['userPassword'] = md5 ($password);
			} else {
				return new Error ("LOGIN_FAILED_INCORRECT_VALUES");
			}
		} else {
			if ($a->is ('USER_LOGIN_DONT_EXISTS')) {
				return new Error ("LOGIN_FAILED_INCORRECT_VALUES");
			} else {
				return $a;
			}
		}
	}
	
	/**
	 * Logs the current user off
	*/
	function logout () {
		unset ($_SESSION['userID']);
		unset ($_SESSION['userPassword']);
	}
	
	/*Group functions*/
	
	/**
	 * Creates a group object. This is the only good method to create one.
	 * Do not use new group (); directly.
	 *
	 * @return (object group)
	 * @public
	*/
	function newGroup () {
		return $this->createObject ('groups');
	}	
	
	/**
	 * Checks that a group is already registered into the database.
	 *
	 * @param $groupName (string) the name of the group
	 * @return (bool)
	*/
	function isGroupNameRegistered ($groupName) {
		$prefix = $this->_db->getPrefix ();
		$groupName = $this->_db->escapeString ($groupName);
		$sql = "SELECT COUNT(group_id) as groups FROM ".$prefix."groups 
				WHERE generic_name='$groupName'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$row = $this->_db->fetchArray ($q);
			if ($row['groups'] == 0) {
				return false;
			} else {
				return true;
			}
		} else {
			return $q;
		}
	}	
	
	/**
	 * Adds a group to the database
	 *
	 * @param $group (object group)
	*/
	function addGroupToDatabase (&$group) {
		$gIR = $this->isGroupNameRegistered ($group->getGenericName ());
		if (! isError ($gIR)) {
			if ($gIR == false)  {
				return $group->addToDatabase ();
			} else {
				$groupName = $group->getGenericName ();
				return new Error ('GROUPNAME_ALREADY_REGISTERED', $groupName);
			}
		} else {
			return $gIR;
		}
	}
	
	/**
	 * Removes a group from the database
	 *
	 * @param $group (group object) The group to be removed
	 * @public
	*/
	function removeGroupFromDatabase ($group) {
		return $group->removeFromDatabase ();
	}
		
	/**
	 * Returns an array with all groups
	 *
	 * @return (object array)
	 * @public
	*/
	function getAllGroups () {
		return $this->getAllRowsFromTable ('groups');
	}
	
	/**
	 * Returns an array with all groups, but skips these that belongs to an user
	 *
	 * @return (object array)
	 * @since 0.3
	 * @public
	*/
	function getAllNonUserGroups () {
		$result = array ();
		foreach ($this->getAllRowsFromTable ('groups') as $group) {
			if (! $this->loginIsRegistered ($group->getGenericName ())) {
				$result[] = $group;
			}
		}
		return $result;
	}
	
	/**
	 * Creates a new translated group.
	 *
	 * @public
	 * @return (object translatedGroup)
	*/
	function newTranslatedGroup () {
		return $this->createObject ('translatedGroups');
	}
}
