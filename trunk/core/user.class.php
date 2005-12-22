<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005 MorgOS
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
 * \author Sam Heijens
 * \author Nathan Samson
*/
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
	function user ($genDB) {
		$this->__construct ($genDB);
	}
	
	function __construct ($genDB) {
		session_start ();
		$this->genDB = $genDB;
	}

	function login ($username, $password) {
		$username = addslashes ($username);
		$query = $this->genDB->query ("SELECT username, password FROM ".TBL_USERS." WHERE username = '$username'");
		if ($this->genDB->num_rows ($query) == 0) {
			trigger_error ('WARNING: Username does not exists.');
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
			trigger_error ('WARNING: Wrong password.');
			return false;
		}
	}
	
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
	
	function logout () {
		unset ($_SESSION['username']);
		unset ($_SESSION['pass']);
		unset ($_SESSION['ip']);
		return true;
	}
	
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
			trigger_error ('ERROR: User already exists');
			return false;
		} elseif ($this->emailExist ($email)) {
			trigger_error ('ERROR: Email already exists');
			return false;
		} else  {
			$this->genDB->query ("INSERT INTO ".TBL_USERS." (username, email, password, isadmin) VALUES ('$username', '$email', '$password', '$isAdmin')");
			foreach ($settings as $setting => $value) {
				$setting = addslashes ($setting);
				$value = addslashes ($value);
				$this->genDB->query ($SQL = "UPDATE " . TBL_USERS . " set $setting='$value' WHERE username='$username'");
			}
			return true;
		}
	}
	
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
	
	function getUserFromEmail ($useremail) {
		$useremail = addslashes ($useremail);
		$query = $this->genDB->query ("SELECT * FROM ".TBL_USERS." WHERE email='$useremail'");
		if ($this->genDB->num_rows ($query) == 0) {
			return false;
		} else {
			return $this->genDB->fetch_array ($query);
		}
	}
	
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
	
	function userExist ($username) {
		$username = addslashes ($username);
		$result = $this->genDB->query ("SELECT username FROM ".TBL_USERS . " WHERE username='$username'");
		if ($this->genDB->num_rows ($result) != 0) {
			return true;
		} else {
			return false;
		}
	}
	
	function emailExist ($email) {
		$email = addslashes ($email);
		$result = $this->genDB->query ("SELECT username FROM ".TBL_USERS . " WHERE email='$email'");
		if ($this->genDB->num_rows ($result) != 0) {
			return true;
		} else {
			return false;
		}
	}
	
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
	
	function changePasswordFromUsername ($username) {
		$username = addslashes ($username);
		if ($this->userExist ($username)) {
			$newPassword = $this->randomPassword ();
			$passwordInDB = md5 ($newPassword);
			$SQL = "UPDATE " . TBL_USERS . " set password='$passwordInDB' WHERE username='$username'";
			$query = $this->genDB->query ($SQL);
			return $newPassword;
		} else {
			trigger_error ('ERROR: User doesn\'t exists');
			return false;
		}
	}
	
	function changePasswordFromEmail ($email) {
		$email = addslashes ($email);
		if ($this->emailExist ($email)) {
			$newPassword = $this->randomPassword ();
			$passwordInDB = md5 ($newPassword);
			$SQL = "UPDATE " . TBL_USERS . " set password='$passwordInDB' WHERE email='$email'";
			$query = $this->genDB->query ($SQL);
			return $newPassword;
		} else {
			trigger_error ('ERROR: Email doesn\'t exists');
			return false;
		}
	}
}
?>
