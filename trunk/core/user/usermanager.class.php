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

include_once ('core/user/user.class.php');
include_once ('core/user/usergroup.class.php');
include_once ('core/user/usertranslatedgroups.class.php');

class userManager {
	/**
	 * The database module
	 * @protected
	*/
	var $db;
	/**
	 * An array of all options of the users. Can be null
	 * @protected
	*/
	var $allOptionsForUser;
	/**
	 * An array of all options of the groups. Can be null
	 * @protected
	*/
	var $allOptionsForGroup;
	/**
	 * An array of all options of the translatedgroups. Can be null
	 * @protected
	*/
	var $allOptionsForTranslatedGroup;

	/**
	 * The constructor
	 *
	 * @param $db (dbModule object)
	*/
	function userManager ($db) {
		$this->db = $db;
		$this->allOptionsForUser = null;
		$this->allOptionsForGroup = null;
		$this->allOptionsForTranslatedGroup = null;
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
		$allOptions = $this->getAllOptionsForUser ();
		if (! isError ($allOptions)) {
			return new user ($this->db, $allOptions, $this);
		} else {
			return $allOptions;
		}
	}	
	
	/**
	 * Check that an username is already registered.
	 * 
	 * @param $login (string) the login.
	 * @return (bool). True if user exists, false if not
	 * @public
	*/
	function loginIsRegistered ($login) {
		$prefix = $this->db->getPrefix ();
		$login = $this->db->escapeString ($login);
		$sql = "SELECT COUNT(login) FROM ".$prefix."users WHERE login='$login'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			if ($row['COUNT(login)'] == 0) {
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
		$prefix = $this->db->getPrefix ();
		$email = $this->db->escapeString ($email);
		$sql = "SELECT COUNT(email) FROM ".$prefix."users WHERE email='$email'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			if ($row['COUNT(email)'] == 0) {
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
	 * @param $user (object user)  The user.
	 * @return (error). If an error occurs return it.
	 * @public
	*/
	function addUserToDatabase ($user) {
		$lIR = $this->loginIsRegistered ($user->getLogin ()); 
		if ($lIR == false) {
			$eIR = $this->emailIsRegistered ($user->getEmail ()); 
			if ($eIR == false) {
				return $user->addToDatabase ();
			} else {
				if (! isError ($eIR)) {
					$email = $user->getEmail ();
					return new Error ('USERMANAGER_EMAIL_EXISTS', $email);
				} else {
					return $eIR;
				}
			}
		} else {
			if (! isError ($lIR)) {
				$login = $user->getLogin ();
				return new Error ('USERMANAGER_LOGIN_EXISTS', $login);
			} else {
				return $lIR;
			}
		}
	}
	
	/**
	 * Removes an user from the database.
	 *
	 * @param $user (object user) The user to delete.
	 * @return (error). An error if occurs
	 * @public
	*/
	function removeUserFromDatabase ($user) {
		return $user->removeFromDatabase ();
	}
	
	/**
	 * Adds an extra option to the database for the users.
	 *
	 * @param $newOption (object dbField) the new option
	 *
	 * @warning old user objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @bug when after adding one, someone ask wich exists the new is not added in.
	 *    if that "asker" want to do something with it on an old user object it can cause weird errors.
	 * @return (error) if one
	 * @public
	*/
	function addOptionToUser ($newOption) {
		$curOptions = $this->getAllOptionsForUser ();
		if (! isError ($curOptions)) {
			if (! array_key_exists ($newOption->name, $curOptions)) {
				$newOption->canBeNull = true;
				$r = $this->db->addNewField ($newOption, $this->db->prefix.'users');
				if (! isError ($r)) {
					$this->allOptionsForUser[$newOption->name] = $newOption;
				} else {
					return $r;
				}
			} else {
				return new Error ('USERMANAGER_OPTION_FORUSER_EXISTS', $newOption->name);
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Removes an extra option to the database for the users.
	 *
	 * @param $optionName (string) the name of the option
	 * @warning old user objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @return (error) if one
	 * @public
	*/
	function removeOptionToUser ($optionName) {
		$curOptions = $this->getAllOptionsForUser ();
		if (! isError ($curOptions)) {
			if (array_key_exists ($optionName, $curOptions)) {
				$r = $this->db->removeField ($optionName, $this->db->getPrefix ().'users');
				if (! isError ($r)) {
					unset ($this->allOptionsForUser[$optionName]);
				} else {
					return $r;
				}
			} else {
				return new Error ('USERMANAGER_OPTION_FORUSER_DONT_EXISTS', $optionName);
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Returns an associative array with values null, and keys the name of the option
	 *
	 * @return (null array)
	 * @public
	*/
	function getAllOptionsForUser () {
		if ($this->allOptionsForUser === null) {
			$fields = $this->db->getAlldbFields ($this->db->getPrefix ().'users', array ('userID','login', 'email', 'password'));
			if (! isError ($fields)) {
				$this->allOptionsForUser = $fields;
				return $fields;
			} else {
				return $fields;
			}
		} else {
			return $this->allOptionsForUser;
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
		$usersID = $this->getAllUsersID ();
		if (! isError ($usersID)) {
			$allUsers = array ();
			foreach ($usersID as $ID) {
				$user = $this->newUser ();
				$r = $user->initFromDatabaseID ($ID);
				if (isError ($r)) {
					return $r;
				}
				$allUsers[] = $user;
			}
			return $allUsers;
		} else {
			return $usersID;
		}
	}

	/**
	 * Returns an array of all users ID that are stored in the database.
	 *
	 * @return (int array)
	 * @public
	*/
	function getAllUsersID () {
		$prefix = $this->db->getPrefix ();
		$sql = "SELECT userID FROM ".$prefix."users";
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
				return new Error ('USERMANAGER_INVALID_LOGIN');
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
				return new Error ("USERMANAGER_LOGIN_FAILED");
			}
		} else {
			return $a;
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
		return new group ($this->db, $this->getAllOptionsForGroup (), $this);
	}	
	
	/**
	 * Checks that a group is already registered into the database.
	 *
	 * @param $groupName (string) the name of the group
	 * @return (bool)
	*/
	function isGroupNameRegistered ($groupName) {
		$prefix = $this->db->getPrefix ();
		$groupName = $this->db->escapeString ($groupName);
		$sql = "SELECT COUNT(groupID) FROM ".$prefix."groups WHERE genericName='$groupName'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			if ($row['COUNT(groupID)'] == 0) {
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
	function addGroupToDatabase ($group) {
		$gIR = $this->isGroupNameRegistered ($group->getGenericName ());
		if (! isError ($gIR)) {
			if ($gIR == false)  {
				return $group->addToDatabase ();
			} else {
				$groupName = $group->getGenericName ();
				return new Error ('USERMANAGER_GROUP_ALREADY_EXISTS', $groupName);
			}
		} else {
			return $gIR;
		}
	}
	
	/**
	 * Adds an option for a group item.
	 *
	 * @param $newOption (object dbField) the  the option
	 * @public
	*/
	function addOptionToGroup ($newOption) {
		$curOptions = $this->getAllOptionsForGroup ();
		if (! isError ($curOptions)) {
			if (! array_key_exists ($newOption->name, $curOptions)) {
				$newOption->canBeNull = true;
				$r = $this->db->addNewField ($newOption, $this->db->getPrefix().'groups');
				if (! isError ($r)) {
					$this->allOptionsForGroup[$newOption->name] = $newOption;
				} else {
					return $r;
				}
			} else {
				return new Error ('USERMANAGER_OPTION_FORGROUP_EXISTS', $newOption->name);
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Removes a group option
	 *
	 * @param $optionName (string) The name of the option
	*/
	function removeOptionFromGroup ($optionName) {
		$curOptions = $this->getAllOptionsForGroup ();
		if (! isError ($curOptions)) {
			if (array_key_exists ($optionName, $curOptions)) {
				$r = $this->db->removeField ($optionName, $this->db->getPrefix ().'groups');
				if (! isError ($r)) {
					unset ($this->allOptionsForGroup[$optionName]);
				} else {
					return $r;
				}
			} else {
				return new Error ('USERMANAGER_OPTION_FORGROUP_DONT_EXISTS', $optionName);
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Gets an array of all options for a groupitem
	 *
	 * @return (null array)
	 * @public
	*/
	function getAllOptionsForGroup () {
		if ($this->allOptionsForGroup === null) {
			$fields = $this->db->getAlldbFields ($this->db->getPrefix ().'groups');
			if (! isError ($fields)) {
				$allOptions = $this->db->getAlldbFields ($this->db->getPrefix ().'groups', array ('groupID', 'genericDescription', 'genericName'));
				$this->allOptionsForGroup = $allOptions;
				return $allOptions;
			} else {
				return $fields;
			}
		} else {
			return $this->allOptionsForGroup;
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
		$groupsID = $this->getAllGroupsID ();
		if (! isError ($groupsID)) {
			$allGroups = array ();
			foreach ($groupsID as $groupID) {
				$group = $this->newGroup ();
				$r = $group->initFromDatabaseID ($groupID);
				if (! isError ($r)) {
					$allGroups[] = $group;
				} else {
					return $r;
				}
			}
			return $allGroups;
		} else {
			return $groupsID;
		}
	}
	
	/**
	 * Returns an array with values of all the groups IDs.
	 *
	 * @return (int array)
	 * @public
	*/
	function getAllGroupsID () {
		$prefix = $this->db->getPrefix ();
		$sql = "SELECT groupID FROM ".$prefix."groups";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$allGroups = array ();
			while ($row = $this->db->fetchArray ($q)) {
				$allGroups[] = $row['groupID'];
			}
			return $allGroups;
		}
	}
	
	/**
	 * Creates a new translated group.
	 *
	 * @public
	 * @return (object translatedGroup)
	*/
	function newTranslatedGroup () {
		return new translatedGroup ($this->db, $this->getAllOptionsForTranslatedGroup (), $this);
	}
	
	/**
	 * Adds an extra option to the database for translated group.
	 *
	 * @param $newOption (object dbField) the new option
	 *
	 * @warning old translated group objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @bug when after adding one, someone ask wich exists the new is not added in.
	 *    if that "asker" want to do something with it on an old translated group object it can cause weird errors.
	 * @public
	*/
	function addOptionToTranslatedGroup ($newOption) {
		$curOptions = $this->getAllOptionsForTranslatedGroup ();
		if (! isError ($curOptions)) {
			if (! array_key_exists ($newOption->name, $curOptions)) {
				$newOption->canBeNull = true;
				$r = $this->db->addNewField ($newOption, $this->db->prefix.'translatedGroups');
				if (! isError ($r)) {
					$this->allOptionsForTranslatedGroup[$newOption->name] = $newOption;
				} else {
					return $r;
				}
			} else {
				return new Error ('USERMANAGER_OPTION_FORTRANSLATEDGROUP_EXISTS', $newOption->name);
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Removes an extra option to the database for the translated groups.
	 *
	 * @param $optionName (string) the name of the option
	 * @warning old translated group objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @return (error) if one
	 * @public
	*/
	function removeOptionFromTranslatedGroup ($optionName) {
		$curOptions = $this->getAllOptionsForTranslatedGroup ();
		if (! isError ($curOptions)) {
			if (array_key_exists ($optionName, $curOptions)) {
				$r = $this->db->removeField ($optionName, $this->db->getPrefix ().'translatedGroups');
				if (! isError ($r)) {
					unset ($this->allOptionsForTranslatedGroup[$optionName]);
				} else {
					return $r;
				}
			} else {
				return new Error ('USERMANAGER_OPTION_FORTRANSLATEDGROUP_DONT_EXISTS', $optionName);
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Returns an associative array with values of type object dbField
	 *
	 * @return (object dbField array)
	 * @public
	*/
	function getAllOptionsForTranslatedGroup () {
		if ($this->allOptionsForTranslatedGroup === null) {
			$fields = $this->db->getAlldbFields ($this->db->getPrefix ().'translatedGroups', array ('translatedGroupID','groupID', 'name', 'description', 'languageCode'));
			if (! isError ($fields)) {
				$this->allOptionsForTranslated = $fields;
				return $fields;
			} else {
				return $fields;
			}
		} else {
			return $this->allOptionsForTranslatedGroup;
		}
	}
}
