<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2007 MorgOS
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
/** \file user.class.php
 * File that take care of one user
 *
 * @ingroup user core
 * @since 0.2
 * @author Nathan Samson
*/

/**
 * A class that represents a user
 *
 * @ingroup user core
 * @since 0.2
 * @author Nathan Samson
*/
class User extends DBTableObject {
	/**
	 * Constructor
	 *
	 * @param $db (object database) the database module
	 * @param $parent (object)
	 * @param $extraFields (dbField array) an array with extra fields
	 * @param $extraJoins (dbGenericJoinField array) an array with extra joins
	*/
	function User ($db, &$parent, $extraFields = array (), $extraJoins = array ()) {
		$login = new dbField ('login', DB_TYPE_STRING, 255);
		$email = new dbField ('email', DB_TYPE_STRING, 255);
		$pass = new dbField ('password', DB_TYPE_STRING, 32); // md5ied always length 32
		$ID = new dbField ('user_id', DB_TYPE_INT, 11);	
		
		$groupJoin = new MultipleToMultipleJoinField ('groups', 'group', 'group_id', $ID,
			'groupUsers');
	
		parent::DBTableObject ($db, array ($login, $email, $pass), 'users', 'user_id', 
			$parent, $extraFields, array_merge (array ($groupJoin), $extraJoins));
	}
	
	/*Public initters*/

	/**
	 * Initialize the user from the database with login $login
	 *
	 * @param $login (string) The database login
	 * @return (error)
	 * @public
	*/
	function initFromDatabaseLogin ($login) {
		$fullTableName = $this->getFullTableName ();
		$login = $this->_db->escapeString ($login);
		$sql = "SELECT * FROM ".$fullTableName." WHERE login='$login'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			if ($this->_db->numRows ($q) == 1) {
				$row = $this->_db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setField ('ID', $row[$this->getIDName ()]);
			} else {
				return new Error ('USER_LOGIN_DONT_EXISTS', $login);
			}
		} else {
			return $q;
		}
	}
	
	/**
	 * Initialize the user from the database with email $email
	 *
	 * @param $email (string) The database email
	 * @return (error)
	 * @public
	*/
	function initFromDatabaseEmail ($email) {
		$fullTableName = $this->getFullTableName ();
		$email = $this->_db->escapeString ($email);
		$sql = "SELECT * FROM $fullTableName WHERE email='$email'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$row = $this->_db->fetchArray ($q);
			$this->initFromArray ($row);
			$this->setField ('ID', $row[$this->getIDName ()]);
		} else {
			return $q;
		}
	}
	
	/**
	 * Add the user to a group
	 *
	 * @param $group (group object) The group where it should be added to
	 * @public
	*/
	function addToGroup ($group) {
		return $group->addUserToGroup ($this);
	}
	
	/**
	 * Removes the user from a group
	 *
	 * @param $group (group object) The group
	 * @public
	*/	
	function removeFromGroup ($group) {
		return $group->removeUserFromGroup ($this);
	}
	
	/**
	 * Checks that a user is in a group
	 *
	 * @param $group (group object) The group
	 * @return (bool)
	 * @public
	*/
	function isInGroup ($group) {
		if (is_string ($group)) {
			$groupO = $this->_creator->newGroup ();
			$groupO->initFromDatabaseGenericName ($group);
			return $this->isInGroup ($groupO);
		} else {
			return $group->isUserInGroup ($this);
		}		
	}

	
	/**
	 * Returns that a user has a permission to do something
	 *
	 * @param $permissionName
	 * @param $default (bool) is default false
	 * @public
	 * @return (bool)
	*/
	function hasPermission ($permissionName, $default = false) {
		foreach ($this->getAllGroups () as $group) {
			if ($group->hasPermission ($permissionName, $default)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Returns the login of the user.
	 *
	 * @return (string)
	 * @public
	*/
	function getLogin () { return $this->getFieldValue ('login'); }
	
	/**
	 * Returns the email of the user.
	 *
	 * @return (string)
	 * @public
	*/
	function getEmail () { return $this->getFieldValue ('email'); }
	
	/**
	 * Gets all the groups where the user is in
	 *
	 * @public
	 * @return (object group array)
	*/	
	function getAllGroups () {
		$prefix = $this->_db->getPrefix ();
		$ID = $this->getID ();
		$sql = "SELECT group_id FROM {$prefix}groupUsers WHERE user_id='$ID'";
		$q = $this->_db->query ($sql);
		if (! isError ($q)) {
			$allGroups = array ();
			while ($row = $this->_db->fetchArray ($q)) {
				$c = $this->getCreator ();
				$g = $c->newGroup ();
				$g->initFromDatabaseID ($row['group_id']);
				$allGroups[] = $g;
			}
			return $allGroups;
		} else {
			return $q;
		}
	}
	
	/**
	 * Checks that a password is valid for this user.
	 *
	 * @param $password (string) Can be both md5-ed or plain
	*/
	function isValidPassword ($password) {
		return ($this->getFieldValue ('password') == $password) OR 
			($this->getFieldValue ('password') == md5 ($password)); 
	}
	
	/**
	 * Change the password for the user
	 *
	 * @public
	 * @param $newPassword (string) plain password
	 * @since 0.3
	*/
	function changePassword ($newPassword) {
		$this->setField ('password', md5 ($newPassword));
		return $this->updateToDatabase ();
	}
	
	/**
	 * Resets the user password, and returns a new one.
	 *
	 * @public
	 * @since 0.3
	 * @param $length (int) the length of the password default = 6
	 * @return (string) the new password
	*/
	function resetPassword ($length = 6) {
		if ($this->isInDatabase ()) {
			$new = '';
			while (strlen ($new) < $length) {
				// mt_srand is not required since 4.2.0
				$char = chr (mt_rand (48, 122));
				if (eregi ('[a-z|0-9]', $char)) {
					$new .= $char;
				}
			}
			$this->changePassword ($new);
			return $new;
		} else {
			return new Error ('USER_NOT_IN_DATABASE');
		}
	}
	
	/**
	 * Returns the user group
	 *
	 * @since 0.3
	 * @public
	 * @return (object UserGroup)
	*/
	function getUserGroup () {
		$group = $this->_creator->newGroup ();
		$group->initFromDatabaseGenericName ($this->getLogin ());
		return $group;
	}
	
	function initFromArray ($array) {
		if (strlen ($array['password']) != 32) { 
			// not always correct, but I guess nobody will take a password of 32 chars
			$array['password'] = md5 ($array['password']);
		}
		parent::initFromArray ($array);
	}
}
