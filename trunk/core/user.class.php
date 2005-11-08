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
class user {
	function user ($genDB) {
		$this->__construct ($genDB);
	}
	
	function __construct ($genDB) {
		session_start ();
		$this->genDB = $genDB;
	}

	function login ($password, $username) {
		$query = $this->genDB->query ("SELECT username, pass FROM ".TBL_USERS." WHERE username = $username");
		$user = $this->genDB ($query);
		if (md5 ($password) == $user['pass']) {
			$_SESSION['username'] = $username;
			$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['pass'] = md5 ($password);
			session_register('username');
			session_register('ip');
			session_register('pass');
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
		session_start();
		unset ($_SESSION['username']);
		unset ($_SESSION['pass']);
		unset ($_SESSION['ip']);
	}
	
	function insertUser ($username, $email, $password, $isAdmin) {
		if ($isAdmin) {
			$isAdmin = 'yes';
		} else {
			$isAdmin = 'no';
		}
		$result = $this->genDB->query ("SELECT username FROM ".TBL_USERS);
		$row = $this->genDB->fetch_array ($result);
		$usernameDB = $row['name'];
		if ($this->genDB->num_rows ($result) != 0) {
			trigger_error ('User already exists');
		} else  {
			$this->genDB->query("INSERT INTO ".TBL_USERS." (username, email, password, isadmin) VALUES ('$username', '$email', '$password', '$isAdmin')");
		}
	}
	
	function getUser ($username) {
		$query = $genDB->query ("SELECT * FROM ".TBL_USERS." WHERE username='$username'");
		return $genDB->fetch_array($query);
	}
	
	function updateUser ($username, $email , $pass, $id) {
		$pass = md5 ($pass);
		$this->genDB->query ("UPDATE ".TBL_USERS." SET  email='$email', pass='$pass' WHERE username='$username'");
	}
}
?>