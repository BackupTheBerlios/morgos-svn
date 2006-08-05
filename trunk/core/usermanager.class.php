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

include_once ('core/user.class.php');
include_once ('core/usergroup.class.php');

class userManager {
	var $db;

	function userManager ($db) {
		$this->db = $db;
	}
	
	/*Public functions*/
	/*User functions*/
	
	/**
	 * Creates a user object. This is the only good method to create one.
	 * Do not use new user (); directly.
	 *
	 * @return (object user)
	*/
	function newUser () {
		$allOptions = $this->getAllOptionsForUser ();
		if (! isError ($allOptions)) {
			return new user ($this->db, $allOptions);
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
		$sql = "SELECT COUNT(login) FROM {$this->db->getPrefix ()}users WHERE login='$login'";
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
		$sql = "SELECT COUNT(email) FROM {$this->db->getPrefix ()}users WHERE email='$email'";
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
					return "ERROR_USERMANAGER_EMAIL_EXISTS {$user->getEmail ()}";
				} else {
					return $eIR;
				}
			}
		} else {
			if (! isError ($lIR)) {
				return "ERROR_USERMANAGER_LOGIN_EXISTS {$user->getLogin ()}";
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
	 * @bug when after adding one, someone ask wich exists the new is added in.
	 *    if that "asker" want to do something with it on an old user object it can cause weird errors.
	 * @return (error) if one
	*/
	function addOptionToUser ($newOption, $sqlType) {
		$curOptions = $this->getAllOptionsForUser ();
		if (! isError ($curOptions)) {
			if (array_key_exists ($newOption, $curOptions)) {
				$sql = "ALTER TABLE {$this->db->getPrefix()}users ADD $newOption $sqlType";
				$q = $this->db->query ($sql);
				if (isError ($q)) {
					return $q;
				}
			} else {
				return "ERROR_USERMNAGER_OPTION_FORUSER_EXISTS $newOption";
			}
		} else {
			return $curOptions;
		}
	}
	
	/**
	 * Returns an associative array with values null, and keys the name of the option
	 *
	 * @return (null array)
	*/
	function getAllOptionsForUser () {
		$fields = $this->db->getAllFields ($this->db->getPrefix ().'users');
		if (! isError ($fields)) {
			$allOptions = array ();
			foreach ($fields as $field) {
				if (! ($field == 'userID' or $field == 'login' or $field == 'email')) {
					$allOptions[$field] = null;
				}
			}
			return $allOptions;
		} else {
			return $fields;
		}
	}

	function getAllUsers () {
	}

	/**
	 * Returns an array of all users that are stored in the database.
	 *
	 * @return (int array)
	 * @public
	*/
	function getAllUsersID () {
		$sql = "SELECT userID FROM {$this->db->getPrefix ()}users";
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
	
	function groupExists () {
	}	
	
	function addGroupToDatabase () {
	}
	
	function addOptionToGroup () {
	}
	
	function getAllOptionsForGroup () {
	}
	
	function removeGroupFromDatabase () {
	}
		
	function getAllGroups () {
	}
}