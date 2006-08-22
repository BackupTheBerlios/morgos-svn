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

include_once ('core/user/user.class.php');
include_once ('core/user/usergroup.class.php');

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
	 * The constructor
	 *
	 * @param $db (dbModule object)
	*/
	function userManager ($db) {
		$this->db = $db;
		$this->allOptionsForUser = null;
		$this->allOptionsForGroup = null;
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
					return "ERROR_USERMANAGER_EMAIL_EXISTS $email";
				} else {
					return $eIR;
				}
			}
		} else {
			if (! isError ($lIR)) {
				$login = $user->getLogin ();
				return "ERROR_USERMANAGER_LOGIN_EXISTS $login";
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
	 * @param $newOption (string) the name of the new option
	 * @param $sqlType (string) the sqltype possible options: 
	 *   - Varchar (length)
	 *   - Int
	 *   - enum('a', 'b', 'c')
	 *   - text
	 * @warning old user objects don't profit of this. 
	 *  Wait for a restart of the system (reload of the page) to be sure its applied.
	 * @bug when after adding one, someone ask wich exists the new is not added in.
	 *    if that "asker" want to do something with it on an old user object it can cause weird errors.
	 * @return (error) if one
	 * @public
	*/
	function addOptionToUser ($newOption, $sqlType) {
		$curOptions = $this->getAllOptionsForUser ();
		if (! isError ($curOptions)) {
			if (! array_key_exists ($newOption, $curOptions)) {
				$prefix = $this->db->getPrefix ();
				$sql = "ALTER TABLE ".$prefix."users ADD $newOption $sqlType";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
				$this->allOptionsForUser[$newOption] = null;
			} else {
				return "ERROR_USERMANAGER_OPTION_FORUSER_EXISTS $newOption";
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
				$prefix = $this->db->getPrefix ();
				$sql = "ALTER TABLE ".$prefix."users DROP $optionName";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
				unset ($this->allOptionsForUser[$optionName]);
			} else {
				return "ERROR_USERMANAGER_OPTION_FORUSER_DONT_EXISTS $optionName";
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
			$fields = $this->db->getAllFields ($this->db->getPrefix ().'users');
			if (! isError ($fields)) {
				$allOptions = array ();
				foreach ($fields as $field) {
					if (! ($field == 'userID' or $field == 'login' or $field == 'email')) {
						$allOptions[$field] = null;
					}
				}
				$this->allOptionsForUser = $allOptions;
				return $allOptions;
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
			var_dump ($q);
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
				return "ERROR_USERMANAGER_GROUP_ALREADY_EXISTS $groupName";
			}
		} else {
			return $gIR;
		}
	}
	
	/**
	 * Adds an option for a group item.
	 *
	 * @param $newOption (string) the name of the option
	 * @param $sqlType (string) The sqltype
	 * @public
	*/
	function addOptionToGroup ($newOption, $sqlType) {
		$curOptions = $this->getAllOptionsForGroup ();
		if (! isError ($curOptions)) {
			if (! array_key_exists ($newOption, $curOptions)) {
				$prefix = $this->db->getPrefix();
				$sql = "ALTER TABLE ".$prefix."groups ADD $newOption $sqlType";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
				$this->allOptionsForGroup[$newOption] = null;
			} else {
				return "ERROR_USERMANAGER_OPTION_FORGROUP_EXISTS $newOption";
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
				$prefix = $this->db->getPrefix();
				$sql = "ALTER TABLE ".$prefix."groups DROP $optionName";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
				unset ($this->allOptionsForGroup[$optionName]);
			} else {
				return "ERROR_USERMANAGER_OPTION_FORGROUP_DONT_EXISTS $optionName";
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
			$fields = $this->db->getAllFields ($this->db->getPrefix ().'groups');
			if (! isError ($fields)) {
				$allOptions = array ();
				foreach ($fields as $field) {
					if (! ($field == 'groupID' or $field == 'genericDescription' or $field == 'genericName')) {
						$allOptions[$field] = null;
					}
				}
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
}