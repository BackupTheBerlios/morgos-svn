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
/** \file user.class.php
 * File that take care of one user
 *
 * @since 0.2
 * @author Nathan Samson
*/

class user extends databaseObject {
	/**
	 * Constructor
	 *
	 * @param $db (object database) the database module
	 * @param $allOptions (null array) an array with empty values. The keys are the extra options.
	 * @param $parent (object)
	*/
	function user ($db, $allOptions, &$parent) {
		$login = new dbField ();
		$login->name = 'login';
		$login->type = 'varchar (255)';
	
		$email = new dbField ();
		$email->name = 'email';
		$email->type = 'varchar (255)';
	
		parent::databaseObject ($db, $allOptions, array ('login'=>$login, 'email'=>$email), 'users', 'userID', $parent);
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
		$login = $this->db->escapeString ($login);
		$sql = "SELECT * FROM ".$fullTableName." WHERE login='$login'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				$row = $this->db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->setOption ('ID', $row[$this->getIDName ()]);
			} else {
				return "ERROR_USER_LOGIN_DONT_EXISTS $login";
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
		$email = $this->db->escapeString ($email);
		$sql = "SELECT * FROM $fullTableName WHERE email='$email'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			$this->initFromArray ($row);
			$this->setOption ('ID', $row[$this->getIDName ()]);
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
		return $group->isUserInGroup ($this);		
	}

	
	function hasPermission () {
	}
	
	/**
	 * Returns the login of the user.
	 *
	 * @return (string)
	 * @public
	*/
	function getLogin () { return $this->getOption ('login'); }
	
	/**
	 * Returns the email of the user.
	 *
	 * @return (string)
	 * @public
	*/
	function getEmail () { return $this->getOption ('email'); }
	
	/**
	 * Gets all the groups where the user is ing
	 * @warning Unimplemented
	 *
	 * @returns (nothing)
	 * @public
	*/	
	function getAllGroups () {}
}
