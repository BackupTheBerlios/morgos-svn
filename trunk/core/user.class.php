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
/** \file uimanager.class.php
 * File that take care of the main user system
 *
 * $Id$
 * \author Sam Heijens
 * \author Nathan Samson
*/
session_start ();
define ('TBL_USERS', TBL_PREFIX . 'users');
/** \class user
 * class that take care of the main user system
 * \todo make login safer (max 10 logins, wait one second, password min 6 characters, ...)
 * \bug insertUser queries to much 2 + number of settings, should be 2 or maybe 3
 *
 * \author Sam Heijens
 * \author Nathan Samson
*/
class user {
	function user (&$genDB, &$i10nMan) {
		$this->__construct (&$genDB, $i10nMan);
	}
	
	function __construct (&$genDB, &$i10nMan) {
		$this->genDB = &$genDB;
		$this->i10nMan = &$i10nMan;
	}
	
	/* \fn login ($username, $password)
	 * This function logs a in.
	 *
	 * \param $username (string) this contains the username of a user
	 * \param $password (string) this contains the password of a user
	 * \return (bool) returns true if the user is logged in
	*/
	function login ($username, $password) {
		$username = addslashes ($username);
		$query = $this->genDB->query ("SELECT username, password FROM ".TBL_USERS." WHERE username = '$username'");
		if ($this->genDB->num_rows ($query) == 0) {
			trigger_error ('WARNING: ' . $this->i10nMan->translate ('Username does not exists.'));
			return;
		}
		$user = $this->genDB->fetch_array ($query);

		if (md5 ($password) == $user['password']) {
			$_SESSION['username'] = $username;
			$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['pass'] = md5 ($password);
			session_register('username');
			session_register('ip');
			session_register('pass');
			return true;
		} else {
			trigger_error ('WARNING: ' . $this->i10nMan->translate ('Wrong password.'));
			return false;
		}
	}
	
	/* \fn isLoggedIn ()
	 * This function Checks if the user is logged in
	 *
	 * \return (bool) is true if the users is logged in
	*/
	function isLoggedIn () {
		if (array_key_exists ('username', $_SESSION)) {
			$user = $this->getUser ($_SESSION['username']);
			if (($_SESSION['pass'] == $user['password']) and ($_SERVER['REMOTE_ADDR'] == $_SESSION['ip'])) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/* \fn isAdmin ()
	 * This function checks if the user admin or not
	 *
	 * \return (bool) is true if the user is admin
	*/
	function isAdmin () {
		if ($this->isLoggedIn ()) {
			$user = $this->getUser ($_SESSION['username']);
			if ($user['isadmin'] == 'yes') {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/* \fn logout ()
	 * This function logs the current user out.
	 *
	 * \return (bool) returns always true
	*/	
	function logout () {
		unset ($_SESSION['username']);
		unset ($_SESSION['pass']);
		unset ($_SESSION['ip']);
		session_destroy (); // ?this is required? (only for (?some?) version of PHP 4)
		return true;
	}
	
	/* \fn insertUser ($username, $email, $password, $isAdmin, $settings = array ())
	 * this function inserts a new user into the database
	 *
	 * \param $username (string) the name of the user
	 * \param $email (string) the e-mail adress of the user
	 * \param $password (string) the password of the user
	 * \param $isAdmin (bool) is true if the user has to be an admin
	 * \param $settings (array)
	 * \return (bool) true on success, false on failure
	*/
	function insertUser ($username, $email, $password, $isAdmin, $settings = array ()) {
		if ($isAdmin) {
			$isAdmin = 'yes';
		} else {
			$isAdmin = 'no';
		}
		$username = addslashes ($username);
		$email = addslashes ($email);
		$password = md5 ($password);
		if ($this->userExist ($username) == true) {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('User already exists'));
			return false;
		} elseif ($this->emailExist ($email)) {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('Email already exists'));
			return false;
		} else  {
			$this->genDB->query ("INSERT INTO ".TBL_USERS." (username, email, password, isadmin) VALUES ('$username', '$email', '$password', '$isAdmin')");
			foreach ($settings as $setting => $value) {
				$setting = addslashes ($setting);
				$value = addslashes ($value);
				$this->genDB->query ("UPDATE " . TBL_USERS . " set $setting='$value' WHERE username='$username'");
			}
			return true;
		}
	}
	
	/* \fn getUser ($username = NULL)
	 * this function gets every data available from the database from that particular user
	 *
	 * \param $username (string) this contains the username of a user
	 * \return (array | bool) returns an array with all info, or false on failure
	*/
	function getUser ($username = NULL) {
		if ($username == NULL && array_key_exists ('username', $_SESSION)) {
			$username = $_SESSION['username'];
		}
		$username = addslashes ($username);
		$query = $this->genDB->query ("SELECT * FROM ".TBL_USERS." WHERE username='$username'");
		if ($this->genDB->num_rows ($query) == 0) {
			return false;
		} else {
			return $this->genDB->fetch_array ($query);
		}
	}
	
	/* \fn getUserFromEmail ($useremail)
	 * this function gets every data available from the database from that particular user
	 *
	 * \param $useremail (string) this contains the emailadress of a user
	 * \return (array | bool) returns an array with all info, or false on failure
	*/
	function getUserFromEmail ($useremail) {
		$useremail = addslashes ($useremail);
		$query = $this->genDB->query ("SELECT * FROM ".TBL_USERS." WHERE email='$useremail'");
		if ($this->genDB->num_rows ($query) == 0) {
			return false;
		} else {
			return $this->genDB->fetch_array ($query);
		}
	}
	
	/* \fn updateUser ($username, $newEmail, $newSettings = array (), $newPass = NULL)
	 * this function updates the data of one user in the database
	 *
	 * \param $username (string) this contains the name of the user
	 * \param $newEmail (string) this contains the new e-mailadress for the user
	 * \param $newSettings (array)
	 * \param $newpass (string) this contains the new password for the user
	 * \return (bool) true on success, false on failure
	*/
	function updateUser ($username, $newEmail, $newSettings = array (), $newPass = NULL) {
		$username = addslashes ($username);
		$newEmail = addslashes ($newEmail);
		$SQL = "UPDATE ".TBL_USERS." SET  email='$newEmail'";
		if ($newPass != NULL) {
			$newPass = md5 ($newPass);
			$_SESSION['pass'] = $newPass;
			$SQL .= ",password='$newPass' ";
		}
		foreach ($newSettings as $setting => $value) {
			$setting = addslashes ($setting);
			$value = addslashes ($value);
			$SQL .= ", $setting='$value' ";
		}		
		$SQL .= "WHERE username='$username'";
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return true;
		} else {
			return false;
		}
	}
	
	/* \fn getAllUsers ($sortOn, $asc)
	 * this function gets all data from all users from the database
	 *
	 * \param $sortOn (string) herein is stated in wich order the data should be sorted in
	 * \param $asc (bool) is true if the list should be downwards
	 * \return (array) 
	*/	
	function getAllUsers ($sortOn = 'username', $asc = true) {
		if ($asc) {
			$asc = 'asc';
		} else {
			$asc = 'desc';
		}
		$SQL = "SELECT * FROM " . TBL_USERS . " ORDER BY '" . $sortOn . "' $asc";
		$result = $this->genDB->query ($SQL);
		$allUsers = array ();
		while ($user = $this->genDB->fetch_array ($result)) {
			$allUsers[] = $user;
		}
		return $allUsers;
	}
	
	/* \fn updateUser ($username, $newIsAdmin)
	 * this function can change the status of a user.
	 *
	 * \param $username (string) this contains the name of the user
	 * \param $newIsAdmin (bool) this is true when the user has to be admin, false if the user has to be a regular user
	*/
	function setAdmin ($username, $newIsAdmin) {
		if ($newIsAdmin) {
			$newIsAdmin = 'yes';
		} else {
			$newIsAdmin = 'no';
		}
		$SQL = "UPDATE " . TBL_USERS . " set isadmin='$newIsAdmin' WHERE username='$username'";
		$result = $this->genDB->query ($SQL);
		if ($result !== false) {
			return true;
		} else {
			return true;
		}
	}
	
	/* \fn userExist ($username)
	 * this function checks if the username already exists
	 *
	 * \param $username (string) this contains the username that should be checked
	*/
	function userExist ($username) {
		$username = addslashes ($username);
		$result = $this->genDB->query ("SELECT username FROM ".TBL_USERS . " WHERE username='$username'");
		if ($this->genDB->num_rows ($result) != 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/* \fn emailExist ($email)
	 * this function checks if the emailadres already exists
	 *
	 * \param $email (string) this contains the email that should be checked
	*/
	function emailExist ($email) {
		$email = addslashes ($email);
		$result = $this->genDB->query ("SELECT username FROM ".TBL_USERS . " WHERE email='$email'");
		if ($this->genDB->num_rows ($result) != 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/* \fn randomPassword ()
	 * this function returns a random password
	*/
	function randomPassword () {
		$newPassword = NULL;
		mt_srand (microtime() * 1000000); // needed for PHP < 4.2
		while (strlen ($newPassword) <= 7) {
			$i = chr (mt_rand (47,123));
			if (ereg ("^[a-zA-Z0-9]$", $i)) {
				$newPassword .= $i;
			}
		}
		return $newPassword;
	}
	
	/* \fn changePasswordFromUsername ($username)
	 * this function changes the password when only the username is given
	 *
	 * \param $username (string) this contains the username where the password has to be changed
	*/
	function changePasswordFromUsername ($username) {
		$username = addslashes ($username);
		if ($this->userExist ($username)) {
			$newPassword = $this->randomPassword ();
			$passwordInDB = md5 ($newPassword);
			$SQL = "UPDATE " . TBL_USERS . " set password='$passwordInDB' WHERE username='$username'";
			$query = $this->genDB->query ($SQL);
			return $newPassword;
		} else {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('User doesn\'t exists'));
			return false;
		}
	}
	
	/* \fn changePasswordFromEmail ($email)
	 * this function changes the password when only the emailadress is given
	 *
	 * \param $email (string) this contains the email from the user where the password has to be changed
	*/
	function changePasswordFromEmail ($email) {
		$email = addslashes ($email);
		if ($this->emailExist ($email)) {
			$newPassword = $this->randomPassword ();
			$passwordInDB = md5 ($newPassword);
			$SQL = "UPDATE " . TBL_USERS . " set password='$passwordInDB' WHERE email='$email'";
			$query = $this->genDB->query ($SQL);
			return $newPassword;
		} else {
			trigger_error ('ERROR: ' . $this->i10nMan->translate ('Email doesn\'t exists'));
			return false;
		}
	}
}
?>
