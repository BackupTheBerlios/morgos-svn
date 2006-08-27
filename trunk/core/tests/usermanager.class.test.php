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
		$this->assertFalse (isError ($user));
		$a = array ();
		$a['login'] = 'THELOGIN';
		$a['email'] = 'THEEMAIL';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals (null, $result);
		$loginExists = $this->userManager->loginIsRegistered ('THELOGIN');
		$this->assertTrue ($loginExists, 'Login not found');	
		$emailExists = $this->userManager->emailIsRegistered ('THEEMAIL');
		$this->assertTrue ($emailExists, 'Email not found');
		
		$user = $this->userManager->newUser ();
		$this->assertFalse (isError ($user));
		$a = array ();
		$a['login'] = 'ANOTHERLOGIN';
		$a['email'] = 'ANOTHEREMAIL';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals (null, $result);
		
		$user = $this->userManager->newUser ();
		$this->assertFalse (isError ($user));
		$a = array ();
		$a['login'] = 'ANOTHERLOGIN';
		$a['email'] = 'ANOTHERANOTHEREMAIL';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals ("ERROR_USERMANAGER_LOGIN_EXISTS ANOTHERLOGIN", $result);
		
		$user = $this->userManager->newUser ();
		$this->assertFalse (isError ($user));
		$a = array ();
		$a['login'] = 'ANOTHERANOTHERLOGIN';
		$a['email'] = 'ANOTHEREMAIL';
		$user->initFromArray ($a);
		$result = $this->userManager->addUserToDatabase ($user);
		$this->assertEquals ("ERROR_USERMANAGER_EMAIL_EXISTS ANOTHEREMAIL", $result);
	}
	
	function testAddOptionForUser () {
		$preName = new dbField ();
		$preName->name = 'preName';
		$preName->type = 'varchar(255)';
		$oldAllOptions = $this->userManager->getAllOptionsForUser ();
		$preName2 = $preName;
		$preName2->canBeNull = true;
		$oldAllOptions['preName'] = $preName2;
		$r = $this->userManager->addOptionToUser ($preName);
		$this->assertFalse (isError ($r), 'Unexpectd error');
		$newAllOptions = $this->userManager->getAllOptionsForUser ();
		$this->assertEquals ($oldAllOptions, $newAllOptions, 'Wrong options returned');
		
		$r = $this->userManager->addOptionToUser ($preName);
		$this->assertEquals ("ERROR_USERMANAGER_OPTION_FORUSER_EXISTS preName", $r);
		
		/*Hack to clean allOptionsForUser cache*/
		$this->userManager->allOptionsForUser = null;
		$newAllOptions = $this->userManager->getAllOptionsForUser ();
		$this->assertEquals ($oldAllOptions, $newAllOptions, 'Wrong options returned');
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
		$this->assertEquals (4, count ($allUsers));
	}
	
	function testRemoveUserFromDatabase () {
		$user = $this->userManager->newUser ();
		$r = $user->initFromDatabaseLogin ('ANOTHERLOGIN');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$r = $this->userManager->removeUserFromDatabase ($user);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$allUsers = $this->userManager->getAllUsers ();
		$this->assertEquals (3, count ($allUsers), 'Not expected number users in DB');
		
		$user = $this->userManager->newUser ();
		$a = array ();
		$a['login'] = 'ANOTHERANOTHERLOGIN';
		$a['email'] = 'ANOTHERANOTHEREMAIL';
		$user->initFromArray ($a);
		$r = $this->userManager->removeUserFromDatabase ($user);
		$this->assertEquals ("ERROR_DATABASEOBJECT_NOT_IN_DATABASE", $r, 'Wrong error');
	
		$allUsers = $this->userManager->getAllUsers ();
		$this->assertEquals (3, count ($allUsers), 'Not expected number users in DB2');
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
		$anOption = new dbField ();
		$anOption->name = 'anOption';
		$anOption->type = 'varchar(255)';
		$anOption2 = $anOption;
		$anOption2->canBeNull = true;
		$r = $this->userManager->addOptionToGroup ($anOption);
		$oldAllOptions = $this->userManager->getAllOptionsForGroup ();
		$oldAllOptions['anOption'] = $anOption2;
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals ($oldAllOptions, $this->userManager->getAllOptionsForGroup (), 'Not added');
		$r = $this->userManager->addOptionToGroup ($anOption);
		$this->assertEquals ("ERROR_USERMANAGER_OPTION_FORGROUP_EXISTS anOption", $r, 'Wrong error returned');
		
		/*Hack to clean allOptionsForGroup cache*/
		$this->userManager->allOptionsForGroup = null;
		$newAllOptions = $this->userManager->getAllOptionsForGroup ();
		$this->assertEquals ($oldAllOptions, $newAllOptions, 'Wrong options returned');
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
		$this->assertEquals (3, count ($allGroups), 'Not the expected number of groups');
	}
	
	function testUserAddToGroup () {
		$user = $this->userManager->newUser ();
		$r = $user->initFromDatabaseLogin ('administrator');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$group = $this->userManager->newGroup ();
		$r = $group->initFromDatabaseGenericName ('administrator');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$r = $user->addToGroup ($group);
		$this->assertFalse (isError ($r), 'Unexpected error returned: ' . $r);
		
		$r = $user->addToGroup ($group);
		$this->assertEquals ('ERROR_GROUP_USER_ALREADY_IN_GROUP', $r, 'Wrong error returned');
	}
	
	function testUserRemoveFromGroup () {
		$user = $this->userManager->newUser ();
		$user->initFromDatabaseLogin ('administrator');
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('administrator');
		$r = $user->removeFromGroup ($group);
		$this->assertFalse (isError ($r), 'Unexpected error returned: '. $r);
		
		$r = $user->removeFromGroup ($group);
		$this->assertEquals ("ERROR_GROUP_USER_NOT_IN_GROUP", $r, 'Wrong error returned: '.$r);
	}
	
	function testIsUserInGroup () {
		$user = $this->userManager->newUser ();
		$user->initFromDatabaseLogin ('normalUser');
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('normalUsers');
		$user->addToGroup ($group);		
		
		$r = $user->isInGroup ($group);
		$this->assertTrue ($r, 'Wrong result returned, should be true');
		
		$group->initFromDatabaseGenericName ('administrator');
		$r = $user->isInGroup ($group);
		$this->assertFalse ($r, 'Wrong result returned, should be false');
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
		
		$gFR_BE = $group->getTranslation ('FR-BE'); // doesn't exists, FR-FR exists
		$this->assertFalse (isError ($gFR_BE), 'Unexpected error?');
		$this->assertEquals ('NL', $gNL_BE->getName ());
	}	
	
	function testGetAllTranslations () {
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('translatedGroup');
		$this->assertEquals (array ( 'FR-FR','NL', 'NL-NL'), $group->getAllTranslations ());
	}	
	
	function testAddTranslatedGroupToDatabase () {
		$group = $this->userManager->newGroup ();
		$group->initFromDatabaseGenericName ('translatedGroup');
		
		$groupFR_BE = $this->userManager->newTranslatedGroup ();
		$a = array ();
		$a['languageCode'] = 'FR-BE';
		$a['name'] = 'FR-BE';
		$a['description'] = 'French';
		$b = $groupFR_BE->initFromArray ($a);
		$this->assertFalse (isError ($b), 'Wrong array initialization');
		$r = $group->addTranslationToDatabase ($groupFR_BE);		
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array ('FR-BE','FR-FR','NL', 'NL-NL'), $group->getAllTranslations (), 'Returned wrong languages');
		
		$r = $group->addTranslationToDatabase ($groupFR_BE);		
		$this->assertEquals ("ERROR_GROUP_TRANSLATION_EXISTS FR-BE", $r);
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
		$this->assertEquals ("ERROR_GROUP_TRANSLATION_DOESNT_EXISTS FR-BE", $r);
	}
	
	function testAddTranslatedGroupOption () {
		var_dump ($this->userManager->getAllOptionsForTranslatedGroup ());
		$this->assertEquals (array (), $this->userManager->getAllOptionsForTranslatedGroup (), 'Options are not empty');
		$anOption = new dbField ();
		$anOption->name = 'anOption';
		$anOption->type = 'varchar(255)';
		$anOption2 = $anOption;
		$anOption2->canBeNull = true;
		$r = $this->userManager->addOptionToTranslatedGroup ($anOption);
		$oldAllOptions = $this->userManager->getAllOptionsForTranslatedGroup ();
		$oldAllOptions['anOption'] = $anOption2;
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals ($oldAllOptions, $this->userManager->getAllOptionsForTranslatedGroup (), 'Not added');
		$r = $this->userManager->addOptionToTranslatedGroup ($anOption);
		$this->assertEquals ("ERROR_USERMANAGER_OPTION_FORTRANSLATEDGROUP_EXISTS anOption", $r, 'Wrong error returned');
		
		/*Hack to clean allOptionsForGroup cache*/
		$this->userManager->allOptionsForTranslatedGroup = null;
		$newAllOptions = $this->userManager->getAllOptionsForTranslatedGroup ();
		$this->assertEquals ($oldAllOptions, $newAllOptions, 'Wrong options returned');
	}
	
	function testTranslatedGroupOption () {
		$r = $this->userManager->removeOptionFromTranslatedGroup ('anOption');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array (), $this->userManager->getAllOptionsForTranslatedGroup (), 'Not removed');
		$r = $this->userManager->removeOptionFromTranslatedGroup ('anOption');
		$this->assertEquals ("ERROR_USERMANAGER_OPTION_FORTRANSLATEDGROUP_DONT_EXISTS anOption", $r, 'Wrong error returned');
	}
	
}
?>
