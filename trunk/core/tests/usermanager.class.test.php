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
		global $dbModule, $u;
		$this->db = $dbModule;
		$this->userManager = $u;/*new userManager ($this->db);*/
	}
	
	function testNewUser () {
		$user = $this->userManager->newUser ();
		$c = get_class ($user);
		$this->assertEquals ("User", $c);
		$r = $user->initFromDatabaseLogin ('notExistingLogin');
		$this->assertEquals (
			new Error ('USER_LOGIN_DONT_EXISTS', 'notExistingLogin'), $r, 
			'Wrong error returned');
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
		$this->assertFalse (isError ($user));
		$a = array ();
		$a['login'] = 'THELOGIN';
		$a['email'] = 'THEEMAIL';
		$a['password'] = 'APASS';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertFalse (isError ($result), 'Unexpecter error');
		$loginExists = $this->userManager->loginIsRegistered ('THELOGIN');
		$this->assertTrue ($loginExists, 'Login not found');	
		$emailExists = $this->userManager->emailIsRegistered ('THEEMAIL');
		$this->assertTrue ($emailExists, 'Email not found');
		$this->assertFalse ($user->getID () == -1, 'Userid not changed');
		
		$user = $this->userManager->newUser ();
		$this->assertFalse (isError ($user));
		$a = array ();
		$a['login'] = 'ANOTHERLOGIN';
		$a['email'] = 'ANOTHEREMAIL';
		$a['password'] = 'aPassword';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals (null, $result);
		
		$user = $this->userManager->newUser ();
		$this->assertFalse (isError ($user));
		$a = array ();
		$a['login'] = 'ANOTHERLOGIN';
		$a['email'] = 'ANOTHERANOTHEREMAIL';
		$a['password'] = 'APASS';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals (
			new Error ('USERMANAGER_LOGIN_EXISTS', 'ANOTHERLOGIN'), $result);
		
		$user = $this->userManager->newUser ();
		$this->assertFalse (isError ($user));
		$a = array ();
		$a['login'] = 'ANOTHERANOTHERLOGIN';
		$a['email'] = 'ANOTHEREMAIL';
		$a['password'] = 'APASS';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals (
			new Error ('USERMANAGER_EMAIL_EXISTS', 'ANOTHEREMAIL'), $result);
	}
	
	function testGetAllUsers () {
		// We can not test getAllUsersID but this test depends on it.
		$allUsers = $this->userManager->getAllUsers ();
		$this->assertEquals (2, count ($allUsers));
	}
	
	/*=== Group Tests ===*/
	function testNewGroup () {
		$group = $this->userManager->newGroup ();
		$this->assertTrue (get_class ($group) == "UserGroup");
		
		$r = $group->initFromDatabaseGenericName ('notExistingGroup');
		$this->assertEquals (
			new Error ('GROUP_GENERICNAME_DONT_EXISTS', 'notExistingGroup'), $r);
	}
	
	function testIsGroupNameRegistered () {
		$isRegistered = $this->userManager->isGroupNameRegistered ('normal');
		$this->assertFalse ($isRegistered);	
	}	
	
	function testAddGroupToDatabase () {
		$group = $this->userManager->newGroup ();
		$groupA = array ('generic_name' => 'aGroup', 'generic_description' => 'A group');
		$group->initFromArray ($groupA);
		$result = $this->userManager->addGroupToDatabase ($group);
		$this->assertFalse (isError ($result));
		$this->assertTrue (
			$this->userManager->isGroupNameRegistered ($group->getGenericName ()));
		
		$group = $this->userManager->newGroup ();
		$groupA = array ('generic_name' => 'aGroup', 'generic_description' => 'A group');
		$group->initFromArray ($groupA);
		$result = $this->userManager->addGroupToDatabase ($group);
		$this->assertEquals (
			new Error ('USERMANAGER_GROUP_ALREADY_EXISTS', 'aGroup'), $result);
	}
	
	function testGetAllGroups () {
		$allGroups = $this->userManager->getAllGroups ();
		$this->assertFalse (isError ($allGroups), 'Unexpected error');
		$this->assertEquals (1, count ($allGroups), 'Not the expected number of groups');
	}
	
	function testUserAddToGroup () {
		$user = $this->userManager->newUser ();
		$r = $user->initFromDatabaseLogin ('ANOTHERLOGIN');
		$this->assertFalse (isError ($r), 'Unexpected error 1');
		$group = $this->userManager->newGroup ();
		$r = $group->initFromDatabaseGenericName ('aGroup');
		$this->assertFalse (isError ($r), 'Unexpected error 2');
		$r = $user->addToGroup ($group);
		$this->assertFalse (isError ($r), 'Unexpected error returned: ' . $r);
		
		$r = $user->addToGroup ($group);
		$this->assertEquals (
			new Error ('GROUP_USER_ALREADY_IN_GROUP'), $r, 'Wrong error returned');
	}
	
	function testUserRemoveFromGroup () {
		$user = $this->userManager->newUser ();
		$user->initFromDatabaseLogin ('ANOTHERLOGIN');
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('aGroup');
		$r = $user->removeFromGroup ($group);
		$this->assertFalse (isError ($r), 'Unexpected error returned: '. $r);
		
		$r = $user->removeFromGroup ($group);
		$this->assertEquals (new Error ('GROUP_USER_NOT_IN_GROUP'), $r, 
			'Wrong error returned: '.$r);
		$user->addToGroup ($group);
	}
	
	function testIsUserInGroup () {
		$user = $this->userManager->newUser ();
		$user->initFromDatabaseLogin ('ANOTHERLOGIN');
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('aGroup');
		$user->addToGroup ($group);		
		
		$r = $user->isInGroup ($group);
		$this->assertTrue ($r, 'Wrong result returned, should be true');
		
		/*$group->initFromDatabaseGenericName ('administrator');
		$r = $user->isInGroup ($group);
		$this->assertFalse ($r, 'Wrong result returned, should be false');*/
	}
	
	
	function testAssignPermission () {
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('aGroup');
		$group->assignPermission ('read_admin', false);
		$this->assertFalse ($group->hasPermission ('read_admin'), 
			'Wrong user permission');		
		$this->assertFalse ($group->hasPermission ('not_existing_permission'), 
			'Wrong not existing permission');

		$group->assignPermission ('read_admin', true);
		$this->assertTrue ($group->hasPermission ('read_admin'), 'Wrong admin permission');
		$group->assignPermission ('read_admin', false);
		$this->assertFalse ($group->hasPermission ('read_admin'), 
			'Wrong admin permission (update 1)');
		$group->assignPermission ('read_admin', true);
		$this->assertTrue ($group->hasPermission ('read_admin'), 
			'Wrong admin permission (update 2)');
	}
	
	function testUserHasPermission () {
		$admin = $this->userManager->newGroup ();
		$admin->initFromDatabaseGenericName ('ANOTHERLOGIN');
		$this->assertTrue ($admin->hasPermission ('read_admin'), 'Admin error');
	}	
	
	function testUserIsValidPassword () {
		$administrator = $this->userManager->newUser ();
		$administrator->initFromDatabaseLogin ('ANOTHERLOGIN');
		$this->assertTrue ($administrator->isValidPassword ('aPassword'), 'correct pass');
		$this->assertTrue ($administrator->isValidPassword (md5 ('aPassword')), 
			'md5 problem');
		$this->assertFalse ($administrator->isValidPassword ('wrongPassword'), 
			'Wrong pass');
		
	}
	
	function testUserLogin () {
		$a = $this->userManager->login ('notAUser', 'aPassword');
		$this->assertTrue ($a->is ('USERMANAGER_LOGIN_FAILED_INCORRECT_INPUT'), 
			'wrong error');
		$this->assertFalse ($this->userManager->isLoggedIn (), 'wrong login');		
		
		$a = $this->userManager->login ('ANOTHERLOGIN', 'wrongPassword');
		$this->assertTrue ($a->is ('USERMANAGER_LOGIN_FAILED_INCORRECT_INPUT'), 
			'wrong error 2');
		$this->assertFalse ($this->userManager->isLoggedIn (), 'wrong login 2');
		
		$a = $this->userManager->login ('ANOTHERLOGIN', 'aPassword');
		$this->assertFalse (isError ($a), 'unexpected error');
		$this->assertTrue ($this->userManager->isLoggedIn (), 'not logged in');
	}
	
	function testUserLogout () {
		$a = $this->userManager->logout ();
		$this->assertFalse ($this->userManager->isLoggedIn (), 'not logged out');
	}	
	
	/*translated groups functions*/
	
	function testGetTranslation () {
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('translatedGroup');
		
		$gNL_NL = $group->getTranslation ('NL-NL');
		$this->assertFalse (isError ($gNL_NL));
		$this->assertEquals ('NL-NL', $gNL_NL->getName ());
		
		$gNL_BE = $group->getTranslation ('NL-BE'); // doesn't exists, NL exists
		$this->assertFalse (isError ($gNL_BE));
		$this->assertEquals ('NL', $gNL_BE->getName ());
	}	
	
	function testGetAllTranslations () {
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('translatedGroup');
		$this->assertEquals (array ( 'FR-FR','NL', 'NL-NL'), 
			$group->getAllTranslations ());
	}	
	
	function testAddTranslatedGroupToDatabase () {
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('translatedGroup');
		
		$groupFR_BE = $this->userManager->newTranslatedGroup ();
		$a = array ();
		$a['language_code'] = 'FR-BE';
		$a['name'] = 'FR-BE';
		$a['description'] = 'French';
		$b = $groupFR_BE->initFromArray ($a);
		$this->assertFalse (isError ($b), 'Wrong array initialization');
		$r = $group->addTranslationToDatabase ($groupFR_BE);		
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array ('FR-BE','FR-FR','NL', 'NL-NL'), 
			$group->getAllTranslations (), 'Returned wrong languages');
		
		$r = $group->addTranslationToDatabase ($groupFR_BE);		
		$this->assertEquals (new Error ('GROUP_TRANSLATION_EXISTS', 'FR-BE'), $r);
	}	
	
	function testRemoveTranslatedGroupFromDatabase () {
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('translatedGroup');
		
		$groupFR_BE = $this->userManager->newTranslatedGroup ();
		$groupFR_BE->initFromDatabaseGroupIDandLanguageCode ($group->getID (), 'FR-BE');
		$r = $group->removeTranslationFromDatabase ($groupFR_BE);		
		$this->assertFalse (isError ($r));
		$this->assertEquals (array ('FR-FR','NL', 'NL-NL'), $group->getAllTranslations ());
		
		$r = $group->removeTranslationFromDatabase ($groupFR_BE);	
		$this->assertEquals (new Error ('GROUP_TRANSLATION_DOESNT_EXISTS', 'FR-BE'), $r);
	}
	
	function testRemoveUserFromDatabase () {
		$user = $this->userManager->newUser ();
		$r = $user->initFromDatabaseLogin ('ANOTHERLOGIN');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$r = $this->userManager->removeUserFromDatabase ($user);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$allUsers = $this->userManager->getAllUsers ();
		$this->assertEquals (1, count ($allUsers), 'Not expected number users in DB');
		
		$user = $this->userManager->newUser ();
		$a = array ();
		$a['login'] = 'ANOTHERANOTHERLOGIN';
		$a['email'] = 'ANOTHERANOTHEREMAIL';
		$user->initFromArray ($a);
		$r = $this->userManager->removeUserFromDatabase ($user);
		$this->assertEquals (
			new Error ('DATABASEOBJECT_NOT_IN_DATABASE'), $r, 'Wrong error');
	
		$allUsers = $this->userManager->getAllUsers ();
		$this->assertEquals (1, count ($allUsers), 'Not expected number users in DB2');
	}
	
	function testRemoveGroupFromDatabase () {
		$group = $this->userManager->newGroup ();
		$result = $this->userManager->removeGroupFromDatabase ($group);
		$this->assertEquals (
			new Error ('DATABASEOBJECT_NOT_IN_DATABASE'), $result, 
			"Not a correct errormessage");
		
		$result = $group->initFromDatabaseGenericName ('aGroup');
		$this->assertFalse (isError ($result), "An unexpected error is returnd");
		$result = $this->userManager->removeGroupFromDatabase ($group);
		$this->assertFalse (isError ($result), "An unexpected error is returnd");
		$this->assertFalse (
			$this->userManager->isGroupNameRegistered ('aGroup'), "group is not deleted");
	}
}
?>
