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
	var $uM;
	
	function setUserManager () {
		if (! $this->uM) {
			global $dbModule;
			$this->uM = new UserManager ($dbModule);
		}
	}
	
	function setUp () {
		$this->setUserManager ();
	}
	
	function testInstall () {
		$this->uM->installAllTables ();
		$this->assertTrue ($this->uM->isInstalled ());
	}
	
	function testAddNewUserToDatabase () {
		$admin = $this->uM->newUser ();
		$admin->initFromArray (array (
			'login'=>'administrator',
			'email'=>'email@host.be',
			'password'=>'AdminPassword'));
		
		$r = $this->uM->addUserToDatabase ($admin);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertTrue ($this->uM->loginIsRegistered ('administrator'));
		$this->assertTrue ($this->uM->emailIsRegistered ('email@host.be'));
		$this->assertTrue ($this->uM->isGroupNameRegistered ('administrator'));
		$this->assertTrue ($admin->isInGroup ('administrator'));
		
		
	}
	
	function testAddNewUserToDatabaseErrors () {
		$normal = $this->uM->newUser ();
		$normal->initFromArray (array (
			'login'=>'normalUser',
			'email'=>'normal@host.com',
			'password'=>'NormalPassword'));
		$r = $this->uM->addUserToDatabase ($normal);
		$this->assertFalse (isError ($r), 'Unexpected error');
		
		$secondNormal = $this->uM->newUser ();
		$secondNormal->initFromArray (array (
			'login'=>'normalUser',
			'email'=>'email@other.be',
			'password'=>'Empty'));
		$r = $this->uM->addUserToDatabase ($secondNormal);
		$this->assertTrue ($r->is ('LOGIN_ALREADY_REGISTERED'));
		
		$thirdNormal = $this->uM->newUser ();
		$thirdNormal->initFromArray (array (
			'login'=>'thirdNormal',
			'email'=>'normal@host.com',
			'password'=>'Empty'));
		$r = $this->uM->addUserToDatabase ($thirdNormal);
		$this->assertTrue ($r->is ('EMAIL_ALREADY_REGISTERED'));
	}
	
	function testUserInitFromEmail () {
		$user = $this->uM->newUser ();
		$user->initFromDatabaseEmail ('normal@host.com');
		$this->assertEquals ('normalUser', $user->getLogin ());
	}	
	
	function testGetAllUsers () {
		$allUsers = $this->uM->getAllUsers ();
		$this->assertEquals (2, count ($allUsers), 'Wrong number of users');
		foreach ($allUsers as $i=>$user) {
			$this->assertEquals ('user', 
				strtolower (get_class ($user)), 'Wrong item: '.$i);
		}
	}
	
	function testUserLogin () {
		$cUser = $this->uM->getCurrentUser ();
		$this->assertNull ($cUser, 'Current user is not null');
		
		$r = $this->uM->login ('noUser', 'NoPassword');
		$this->assertTrue ($r->is ('LOGIN_FAILED_INCORRECT_VALUES'));
		$r = $this->uM->login ('normalUser', 'WrongPassword');
		$this->assertTrue ($r->is ('LOGIN_FAILED_INCORRECT_VALUES'));
		
		$cUser = $this->uM->getCurrentUser ();
		$this->assertNull ($cUser, 'Current user is not null');
		
		$r = $this->uM->login ('normalUser', 'NormalPassword');
		$user = $this->uM->newUser ();
		$user->initFromDatabaseLogin ('normalUser');
		$this->assertTrue ($this->uM->isLoggedIn ());
		$cUser = $this->uM->getCurrentUser ();
		$this->assertEquals ('normalUser', $cUser->getLogin ());
		// used later
		$realUser = $cUser;		
		
		$this->uM->logout ();
		$this->assertFalse ($this->uM->isLoggedIn ());
		$cUser = $this->uM->getCurrentUser ();
		$this->assertNull ($cUser, 'Current user is not null');
		
		$this->uM->login ('normalUser', md5 ('NormalPassword'));
		$this->assertTrue ($this->uM->isLoggedIn ());
		$this->uM->logout ();
		$this->assertFalse ($this->uM->isLoggedIn ());		
		
		// hacking the session test
		$_SESSION['userID'] = $realUser->getID ();
		$_SESSION['userPassword'] = md5 ('WrongPassword');
		$cUser = $this->uM->getCurrentUser ();
		$this->assertTrue (isError ($cUser), 'Should return erorr');
		$this->assertTrue ($cUser->is ('SESSION_LOGIN_FAILED_INCORRECT_VALUES'));
	}
	
	function testChangeUserPassword () {
		$user = $this->uM->newUser ();
		$user->initFromDatabaseEmail ('normal@host.com');
		$user->changePassword ('PHPRocks');
		$this->assertTrue ($user->isValidPassword ('PHPRocks'));
		$this->assertFalse ($user->isValidPassword ('NormalPassword'));
	}
	
	function testNewGroup () {
		$group = $this->uM->newGroup ();
		$group->initFromArray (array (
			'generic_name'=>'Developers',
			'generic_description'=>'A nerdy geeky group'
			));
		$this->uM->addGroupToDatabase ($group);
		$this->assertTrue ($this->uM->isGroupNameRegistered ('Developers'));
		
		$group = $this->uM->newGroup ();
		$group->initFromArray (array (
			'generic_name'=>'Developers',
			'generic_description'=>'Do not try this at home'
			));
		$r = $this->uM->addGroupToDatabase ($group);
		$this->assertTrue ($r->is ('GROUPNAME_ALREADY_REGISTERED'));
		
		$group = $this->uM->newGroup ();
		$group->initFromArray (array (
			'generic_name'=>'Testers',
			'generic_description'=>'A less nerdy beta group'
			));
		$this->uM->addGroupToDatabase ($group);
		$this->assertTrue ($this->uM->isGroupNameRegistered ('Testers'));
	}
	
	function testGetAllGroups () {
		$allGroups = $this->uM->getAllGroups ();
		$this->assertEquals (4, count ($allGroups));
		
		foreach ($allGroups as $i=>$group) {
			$this->assertEquals ('usergroup', 
				strtolower (get_class ($group)), 'Wrong item: '.$i);
			if ($group->getGenericName () == 'Developers') {
				$this->assertEquals ('A nerdy geeky group',
					$group->getGenericDescription ());
			}
		}
	}
	
	function testGetAllNonUserGroups () {
		$allGroups = $this->uM->getAllNonUserGroups ();
		$this->assertEquals (2, count ($allGroups));
		
		foreach ($allGroups as $i=>$group) {
			$this->assertEquals ('usergroup', 
				strtolower (get_class ($group)), 'Wrong item: '.$i);
		}
	}
	
	function testUserGroupInt () {
		$user = $this->uM->newUser ();
		$user->initFromDatabaseLogin ('normalUser');
		$group = $this->uM->newGroup ();
		$group->initFromDatabaseGenericName ('Developers');
		
		$user->addToGroup ($group);
		$this->assertTrue ($user->isInGroup ('Developers'));
		$r = $user->addToGroup ($group);
		$this->assertTrue ($r->is ('USER_ALREADY_IN_GROUP'));
		
		$allGroups = $user->getAllGroups ();
		$this->assertEquals (2, count ($allGroups));
		
		foreach ($allGroups as $i=>$group) {
			$this->assertEquals ('usergroup', 
				strtolower (get_class ($group)), 'Wrong item: '.$i);
		}
	}
	
	function testPermissions () {
		$user = $this->uM->newUser ();
		$user->initFromDatabaseLogin ('normalUser');
		$group = $this->uM->newGroup ();
		$group->initFromDatabaseGenericName ('Developers');
		$userGroup = $this->uM->newGroup ();
		$userGroup->initFromDatabaseGenericName ('normalUser');
		
		$this->assertFalse ($user->hasPermission ('do_something'));
		$group->assignPermission ('do_something', false);
		$this->assertFalse ($user->hasPermission ('do_something'));
		
		$group->assignPermission ('do_something_else', true);
		$this->assertTrue ($user->hasPermission ('do_something_else'));
		
		$userGroup->assignPermission ('do_something_else', false);
		$this->assertTrue ($user->hasPermission ('do_something_else'));
		
		$group->assignPermission ('do_something_else', false);
		$this->assertFalse ($user->hasPermission ('do_something_else'));
		// testing default values
		$this->assertFalse ($user->hasPermission ('do_something_else', true));
		$this->assertFalse ($user->hasPermission ('do_something_not_in_db', false));
		$this->assertTrue ($user->hasPermission ('do_something_not_in_db', true));
	}
	
	function testGroupGetAllUsers () {
		$admin = $this->uM->newUser ();
		$admin->initFromDatabaseLogin ('administrator');	
	
		$group = $this->uM->newGroup ();
		$group->initFromDatabaseGenericName ('Developers');
		$admin->addToGroup ($group);		
		
		$allUsers = $group->getAllUsers ();
		
		$this->assertEquals (2, count ($allUsers), 'Wrong number of users');
		foreach ($allUsers as $i=>$user) {
			$this->assertEquals ('user', 
				strtolower (get_class ($user)), 'Wrong item: '.$i);
		}
	}
	
	function testRemoveUserFromGroup () {
		$admin = $this->uM->newUser ();
		$admin->initFromDatabaseLogin ('administrator');	
	
		$group = $this->uM->newGroup ();
		$group->initFromDatabaseGenericName ('Developers');
		$admin->removeFromGroup ($group);
		$allUsers = $group->getAllUsers ();
		$this->assertEquals (1, count ($allUsers), 'Wrong number of users');
	}
	
	function testNewUserLoginIsGroupName () {
	}
	
	function testRemoveGroup () {
		$user = $this->uM->newUser ();
		$user->initFromDatabaseLogin ('normalUser');
		$group = $this->uM->newGroup ();
		$group->initFromDatabaseGenericName ('Developers');
		
		$this->uM->removeGroupFromDatabase ($group);
		$allGroups = $user->getAllGroups ();
		$this->assertEquals (2, count ($allGroups));
		
		$r = $this->uM->removeGroupFromDatabase ($group);
		$this->assertTrue ($r->is ('OBJECT_NOT_IN_DATABASE'));
	}	
	
	function testRemoveUserFromDatabase () {
		$user = $this->uM->newUser ();
		$user->initFromDatabaseLogin ('normalUser');
		$r = $this->uM->removeUserFromDatabase ($user);
		$this->assertFalse (isError ($r));
		$this->assertFalse ($this->uM->isGroupNameRegistered ('normalUser'));
	}
}
?>
