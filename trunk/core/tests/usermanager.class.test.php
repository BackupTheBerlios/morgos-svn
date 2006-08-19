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
include_once ('core/user/usermanager.class.php');
class userManagerTest extends TestCase {
	var $userManager;
	var $db;

	function setUp () {
		global $dbModule;
		$this->db = $dbModule;
		$this->userManager = new userManager ($this->db);
	}
	
	function testNewUser () {
		$user = $this->userManager->newUser ();
		$this->assertTrue (is_object ($user)); // this is not waterproof!
		// maybe we can implement something with Reflection that gets the 'type' if the obect
		$r = $user->initFromDatabaseLogin ('notExistingLogin');
		$this->assertEquals ("ERROR_USER_LOGIN_DONT_EXISTS notExistingLogin", $r, 'Wrong error returned');
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
		
		$user = $this->userManager->newUser ();
		if (isError ($user)) {
			$this->fail ($user);
		}
		$a = array ();
		$a['login'] = 'ANOTHERLOGIN';
		$a['email'] = 'ANOTHEREMAIL';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals (null, $result);
		
		$user = $this->userManager->newUser ();
		if (isError ($user)) {
			$this->fail ($user);
		}
		$a = array ();
		$a['login'] = 'ANOTHERLOGIN';
		$a['email'] = 'ANOTHERANOTHEREMAIL';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals ("ERROR_USERMANAGER_LOGIN_EXISTS ANOTHERLOGIN", $result);
		
		$user = $this->userManager->newUser ();
		if (isError ($user)) {
			$this->fail ($user);
		}
		$a = array ();
		$a['login'] = 'ANOTHERANOTHERLOGIN';
		$a['email'] = 'ANOTHEREMAIL';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals ("ERROR_USERMANAGER_EMAIL_EXISTS ANOTHEREMAIL", $result);
	}
	
	function testAddOptionForUser () {
		$oldAllOptions = $this->userManager->getAllOptionsForUser ();
		$oldAllOptions['preName'] = null;
		$r = $this->userManager->addOptionToUser ('preName', 'varchar (255)');
		$this->assertEquals (null, $r);
		$newAllOptions = $this->userManager->getAllOptionsForUser ();
		$this->assertEquals ($oldAllOptions, $newAllOptions);
		
		$r = $this->userManager->addOptionToUser ('preName', 'varchar (255)');
		$this->assertEquals ("ERROR_USERMANAGER_OPTION_FORUSER_EXISTS preName", $r);
	}
	
	function testRemoveOptionForUser () {
		$oldAllOptions = $this->userManager->getAllOptionsForUser ();
		unset ($oldAllOptions['preName']);
		$r = $this->userManager->removeOptionToUser ('preName');
		$this->assertEquals (null, $r);
		$newAllOptions = $this->userManager->getAllOptionsForUser ();
		$this->assertEquals ($oldAllOptions, $newAllOptions);

		$r = $this->userManager->removeOptionToUser ('preName');
		$this->assertEquals ("ERROR_USERMANAGER_OPTION_FORUSER_DONT_EXISTS preName", $r);
	}
	
	function testGetAllUsers () {
		// We can not test getAllUsersID but this test depends on it.
		$allUsers = $this->userManager->getAllUsers ();
		$this->assertEquals (2, count ($allUsers));
	}
	
	function testRemoveUserFromDatabase () {
		$user = $this->userManager->newUser ();
		$r = $user->initFromDatabaseLogin ('ANOTHERLOGIN');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$r = $this->userManager->removeUserFromDatabase ($user);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$allUsers = $this->userManager->getAllUsers ();
		$this->assertEquals (1, count ($allUsers), 'Not expected nymber users in DB');
		
		$user = $this->userManager->newUser ();
		$a = array ();
		$a['login'] = 'ANOTHERANOTHERLOGIN';
		$a['email'] = 'ANOTHERANOTHEREMAIL';
		$user->initFromArray ($a);
		$r = $this->userManager->removeUserFromDatabase ($user);
		$this->assertEquals ("ERROR_DATABASEOBJECT_NOT_IN_DATABASE", $r, 'Wrong error');
	
		$allUsers = $this->userManager->getAllUsers ();
		$this->assertEquals (1, count ($allUsers), 'Not expected number users in DB2');
	}
	
	/*=== Group Tests ===*/
	function testNewGroup () {
		$group = $this->userManager->newGroup ();
		$this->assertTrue (is_object ($group)); // this is not waterproof!
		// maybe we can implement something with Reflection that gets the 'type' if the obect
		
		$r = $group->initFromDatabaseGenericName ('notExistingGroup');
		$this->assertEquals ('ERROR_GROUP_GENERICNAME_DONT_EXISTS notExistingGroup', $r);
	}
	
	function testIsGroupNameRegistered () {
		$isRegistered = $this->userManager->isGroupNameRegistered ('administrator');
		$this->assertTrue ($isRegistered);
		$isRegistered = $this->userManager->isGroupNameRegistered ('normal');
		$this->assertFalse ($isRegistered);	
	}	
	
	function testAddGroupToDatabase () {
		$group = $this->userManager->newGroup ();
		$groupA = array ('genericName' => 'aGroup', 'genericDescription' => 'A group');
		$group->initFromArray ($groupA);
		$result = $this->userManager->addGroupToDatabase ($group);
		$this->assertFalse (isError ($result));
		$this->assertTrue ($this->userManager->isGroupNameRegistered ($group->getGenericName ()));
		
		$group = $this->userManager->newGroup ();
		$groupA = array ('genericName' => 'aGroup', 'genericDescription' => 'A group');
		$group->initFromArray ($groupA);
		$result = $this->userManager->addGroupToDatabase ($group);
		$this->assertEquals ("ERROR_USERMANAGER_GROUP_ALREADY_EXISTS aGroup", $result);
	}
	
	function testRemoveGroupFromDatabase () {
		$group = $this->userManager->newGroup ();
		$result = $this->userManager->removeGroupFromDatabase ($group);
		$this->assertEquals ("ERROR_DATABASEOBJECT_NOT_IN_DATABASE", $result, "Not a correct errormessage");
		
		$result = $group->initFromDatabaseGenericName ('aGroup');
		$this->assertFalse (isError ($result), "An unexpected error is returnd");
		$result = $this->userManager->removeGroupFromDatabase ($group);
		$this->assertFalse (isError ($result), "An unexpected error is returnd");
		$this->assertFalse ($this->userManager->isGroupNameRegistered ('aGroup'), "group is not deleted");
	}
	
	function testAddGroupOption () {
		$this->assertEquals (array (), $this->userManager->getAllOptionsForGroup (), 'Options are not empty');
		$r = $this->userManager->addOptionToGroup ('anOption', 'varchar(255)');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array ('anOption' => null), $this->userManager->getAllOptionsForGroup (), 'Not added');
		$r = $this->userManager->addOptionToGroup ('anOption', 'varchar(255)');
		$this->assertEquals ("ERROR_USERMANAGER_OPTION_FORGROUP_EXISTS anOption", $r, 'Wrong error returned');
	}
	
	function testRemoveGroupOption () {
		$r = $this->userManager->removeOptionFromGroup ('anOption');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array (), $this->userManager->getAllOptionsForGroup (), 'Not removed');
		$r = $this->userManager->removeOptionFromGroup ('anOption');
		$this->assertEquals ("ERROR_USERMANAGER_OPTION_FORGROUP_DONT_EXISTS anOption", $r, 'Wrong error returned');
	}
	
	function testGetAllGroups () {
		$allGroups = $this->userManager->getAllGroups ();
		$this->assertFalse (isError ($allGroups), 'Unexpected error');
		$this->assertEquals (1, count ($allGroups), 'Not the expected number of groups');
	}	
	
	function testUserAddToGroup () {
		$user = $this->userManager->newUser ();
		$user->initFromDatabaseLogin ('abcd');
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('existingGroupInGroup');
		$r = $user->addToGroup ($group);
		$this->assertFalse (isError ($r), 'Unexpected error returned: ' . $r);
		
		$r = $user->addToGroup ($group);
		$this->assertEquals ('ERROR_GROUP_USER_ALREADY_IN_GROUP', $r, 'Wrong error returned');
	}
	
	function testUserRemoveFromGroup () {
		$user = $this->userManager->newUser ();
		$user->initFromDatabaseLogin ('abcd');
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('existingGroupInGroup');
		$r = $user->removeFromGroup ($group);
		$this->assertFalse (isError ($r), 'Unexpected error returned');
		
		$r = $user->removeFromGroup ($group);
		$this->assertEquals ("ERROR_GROUP_USER_NOT_INDATABASE", $r, 'Wrong error returned');
	}
	
	function testIsUserInGroup () {
		$user = $this->userManager->newUser ();
		$user->initFromDatabaseLogin ('abcd');
		$group = $this->userManager->newGroup ();
		
		$group->initFromDatabaseGenericName ('existingGroupInGroup');
		$r = $user->isInGroup ($group);
		$this->assertTrue ($r, 'Wrong result returned');
		
		$group->initFromDatabaseGenericName ('existingGroupNotInGroup');
		$r = $user->isInGroup ($group);
		$this->assertFalse ($r, 'Wrong result returned');
	}
}

?>