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
		parent::databaseObject ($db, $allOptions, array ('login', 'email'), 'users', 'userID', $parent);
	}
	
	/*Public initters*/

	/**
	 * Initialize the user from the database with login $login
	 *
	 * @param $login (string) The database login
	 * @return (error)
	*/
	function initFromDatabaseLogin ($login) {
		$sql = "SELECT * FROM {$this->db->getPrefix ()}{$this->getTableName ()} WHERE login='$login'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			if ($this->db->numRows ($q) == 1) {
				$row = $this->db->fetchArray ($q);
				$this->initFromArray ($row);
				$this->ID = $row[$this->getIDName ()];
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
	*/
	function initFromDatabaseEmail ($email) {
		$sql = "SELECT * FROM {$this->db->getPrefix ()}{$this->getTableName ()} WHERE email='$email'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			$this->initFromArray ($row);
			$this->ID = $row[$this->getIDName ()];
		} else {
			return $q;
		}
	}
	
	
	function addToGroup ($group) {
		return $group->addUserToGroup ($this);
	}
	
	function removeFromGroup ($group) {
		return $group->removeUserFromGroup ($this);
	}
	
	function isInGroup ($group) {
		return $group->isUserInGroup ($this);		
	}

	
	function hasPermission () {
	}
	
	function getLogin () { return $this->getOption ('login'); }
	function getEmail () { return $this->getOption ('email'); }
	function getAllGroups () {}
}