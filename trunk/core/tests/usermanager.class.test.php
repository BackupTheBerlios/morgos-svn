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
/** \file usermanager.class.test.php
 * File that take care of the usermanager tester
 *
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/varia.functions.php');
include_once ('core/usermanager.class.php');
class userManagerTest extends PHPUnit2_Framework_TestCase {
	var $userManager;
	var $db;

	function setUp () {
		global $dbModule;
		$this->db = $dbModule;
		$this->userManager = new userManager ($this->db);
	}
	
	function testEmpty () {
		$result = $this->userManager->getAllUsersID ();
		if (! isError ($result)) {
			foreach ($result as $ID) {
				$user = new user ($this->db, $this->userManager->getAllOptionsForUser ());
				$user->initFromDatabaseID ($ID);
				$r = $this->userManager->removeUserFromDatabase ($user);
				if (isError ($r)) {
					$this->fail ($r);
				}
			}
			$this->assertEquals (0, count ($this->userManager->getAllUsersID ()));
		} else {
			$this->fail ($result);
		}
	}
	
	function testLoginIsRegistered () {
		$result = $this->userManager->loginIsRegistered ('NOTEXISTINGONE');
		$this->assertEquals (false, $result);
	}	
	
	function testEmailIsRegistered () {
		$result = $this->userManager->emailIsRegistered ('NOTEXISTINGONE');
		$this->assertEquals (false, $result);
	}
	
	function testAddUserToDatabase () {
		$user = $this->userManager->newUser ();
		if (isError ($user)) {
			$this->fail ($user);
		}
		$a = array ();
		$a['login'] = 'THELOGIN';
		$a['email'] = 'THEEMAIL';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals (null, $result);
		$loginExists = $this->userManager->loginIsRegistered ('THELOGIN');
		$this->assertEquals (true, $loginExists);	
		$emailExists = $this->userManager->emailIsRegistered ('THEEMAIL');
		$this->assertEquals (true, $emailExists);
	}
	
	function testAddOptionForUser () {
		$oldAllOptions = $this->userManager->getAllOptionsForUser ();
		$oldAllOptions['preName'] = null;
		$r = $this->userManager->addOptionToUser ('preName', 'varchar (255)');
		$this->assertEquals (null, $r);
		$newAllOptions = $this->userManager->getAllOptionsForUser ();
		$this->assertEquals ($oldAllOptions, $newAllOptions);
	}
}

?>