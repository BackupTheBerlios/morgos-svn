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

class user {
	var $db;

	var $allOptions;	
	var $ID;
	var $login;
	var $email;

	/**
	 * Constructor
	 *
	 * @param $db (object database) the database module
	 * @param $allOptions (null array) an array with empty values. The keys are the extra options.
	*/
	function user ($db, $allOptions) {
		$this->db = $db;
		if (! isError ($allOptions)) {
			$this->allOptions = $allOptions; 
		} else {
			return $allOptions;
		}
		$this->initEmpty ();
	}
	
	/*Public initters*/
	
	/**
	 * Initialize the user from the database with key $ID
	 *
	 * @param $ID (int) The database ID
	 * @return (error)
	*/
	function initFromDatabaseID ($ID) {
		$sql = "SELECT * FROM {$this->db->getPrefix ()}users WHERE userID='$ID'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			$this->initFromArray ($row);
			$this->ID = $row['userID'];
		} else {
			return $q;
		}
	}
	
	/**
	 * Initialize the user from the database with login $login
	 *
	 * @param $login (string) The database login
	 * @return (error)
	*/
	function initFromDatabaseLogin ($login) {
		$sql = "SELECT * FROM {$this->db->getPrefix ()}users WHERE login='$login'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			$this->initFromArray ($row);
			$this->ID = $row['userID'];
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
		$sql = "SELECT * FROM {$this->db->getPrefix ()}users WHERE email='$email'";
		$q = $this->db->query ($sql);
		if (! isError ($q)) {
			$row = $this->db->fetchArray ($q);
			$this->initFromArray ($row);
			$this->ID = $row['userID'];
		} else {
			return $q;
		}
	}
	
	/**
	 * Initializes an user from arrayvalues.
	 *
	 * @param $array (mixed array) The required values (keys) are login and email.
	 * @public
	*/
	function initFromArray ($array) {
		$this->initEmpty ();
		foreach ($array as $key => $value) {
			$this->setOption ($key, $value); // No error
		}
		$this->login = $array['login'];
		$this->email = $array['email'];
	}
	
	/*Public functions*/
	
	/**
	 * Adds the user to the database.
	 *
	 * @return (error)
	 * @public
	*/
	function addToDatabase () {
		if (! $this->isInDatabase ()) {
			$sql = "INSERT into {$this->db->getPrefix ()}users (login, email,";
			foreach ($this->getAllOptions () as $key => $value) {
				$sql .= "$key,";
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )
			
			$sql .= ' VALUES(';
			$sql .= "'{$this->getLogin ()}', '{$this->getEmail ()}',";
			foreach ($this->getAllOptions () as $key => $value) {
				$sql .= "'$value',";
			}
			$sql[strlen ($sql)-1] = ')'; // remove latest , with )			
			$q = $this->db->query ($sql);
			if (! isError ($q)) {
				$this->ID = $this->db->latestInsertID ($q);
			} else {
				return $q;
			}
		} else {
			return "ERROR_USER_ALREADY_IN_DATABASE";
		}
	}
	
	/**
	 * Removes the user from the database;
	 *
	 * @return (error)
	 * @public
	*/
	function removeFromDatabase () {
		if ($this->isInDatabase ()) {
			$sql = "DELETE FROM {$this->db->getPrefix ()}users WHERE userID='{$this->getID ()}'";
			$q = $this->db->query ($sql);
			if (isError ($q)) {
				return $q;
			}
			$this->ID = -1;
		} else {
			return "ERROR_USER_NOT_IN_DATABASE";
		}
	}
	
	function updateToDatabase () {
	}
	
	function updateFromArray () {
	}
	
	function addToGroup () {
	}
	
	function hasRight () {
	}
	
	function getLogin () { return $this->login; }
	function getEmail () { return $this->email; }
	function getID () { return $this->ID; }
	function getAllGroups () {}
	
	/**
	 * Returns an extra option with name
	 *
	 * @param $name (string) The name of the option
	 * @return (string, error)
	 * @public
	*/
	function getOption ($name) {
		if (array_key_exists ($name, $this->getAllOptions ())) {
			return $this->allOptions[$name];
		} else {
			return "ERROR_USER_OPTION_DOES_NOT_EXISTS $name";
		}
	}
	
	/*Private functions*/	
	/**
	 * Returns an array of all extra options with their values.
	 *
	 * @return (mixed array)
	 * @private
	*/
	function getAllOptions () {
		return $this->allOptions;
	}
	
	/**
	 * Set options $name on $value
	 *
	 * @param $name (string) the name of the option
	 * @param $value (mixed) the new value of the options
	 * @return (error)
	 * @private
	*/
	function setOption ($name, $value) {
		if (array_key_exists ($name, $this->getAllOptions ())) {
			$this->allOptions[$name] = $value;
		} else {
			return "ERROR_USER_OPTION_DOES_NOT_EXISTS $name";
		}
	}
	
	/**
	 * Checks if the user is already stored in the database.
	 *
	 * @return (bool)
	 * @private
	*/
	function isInDatabase () {
		if ($this->getID () < 0) {
			return false;
		} else {
			return true;
		}
	}
	
	/*Private initters*/
	/**
	 * Initialize the user with default values.
	 *
	 * @private
	*/
	function initEmpty () {
		foreach ($this->getAllOptions () as $key => $value) {
			$this->allOptions[$key] = 'NOT SET';
		}
		$this->ID = -1;
		$this->login = 'NOT SET';
		$this->email = 'NOT SET';
	}
}