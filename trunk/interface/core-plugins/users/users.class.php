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
/**
 * This is the users class.
 *
 * @since 0.2
 * @author Sam Heijens
 * @author Nathan Samson
*/
class userCorePlugin extends InstallablePlugin {
	var $_adminPlugin;	
	
	function userCorePlugin ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Users core plugin';
		$this->_ID = '{5df79e7c-2c14-4ad2-b13e-5c420d33182a}';
		$this->_minMorgOSVersion = MORGOS_VERSION;
		$this->_maxMorgOSVersion = MORGOS_VERSION;
		include ($this->getLoadedDir ().'/users.admin.plugin.php');
		$this->_adminPlugin = new adminCoreUserAdminPlugin ($this->getLoadedDir ());
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);		
		$this->_adminPlugin->load ($this->_pluginAPI);
		$am = &$this->_pluginAPI->getActionManager ();
		$am->addAction (new action ('userLogin', 'POST',  array ($this, 'onLogin'), 
			array (new StringInput ('login'), new StringInput ('password')), array ()));
		$am->addAction (new action ('userLogout', 'POST',  array ($this, 'onLogout'), 
			array (), array ()));
		$am->addAction (
			new action ('userRegisterForm', 'GET',  array ($this, 'onRegisterForm'), 
				array (), array (), 'MorgOS_RegisterForm'));
		$am->addAction (
			new action ('userRegister', 'POST',  array ($this, 'onRegister'), 
				array (new StringInput ('login'), new EmailInput ('email'), 
					new PasswordNewInput ('password')), 
				array ()));
				
		$am->addAction (
			new action ('userMyAccount', 'GET',  array ($this, 'onMyAccountForm'), 
				array (), array (), 'MorgOS_User_MyAccount'));
				
		$am->addAction (
			new action ('userChangePassword', 'POST',  
				array ($this, 'onChangePassword'),
				array (new StringInput ('oldPassword'), 
					new PasswordNewInput ('newPassword')), array (), 
					'MorgOS_User_MyAccount'));
					
		$am->addAction (
			new action ('userChangeAccount', 'POST',  
				array ($this, 'onChangeAccount'),
				array (), array (new EmailInput ('newEmail')), 
					'MorgOS_User_MyAccount'));
		
		$em = &$this->_pluginAPI->getEventManager ();
		$em->subscribeToEvent ('viewPage', new callback ('userVars', 
			array ($this, 'setUserVars')));
	}
	
	function onLogin ($login, $password) {
		$userManager = &$this->_pluginAPI->getUserManager ();
		$am = &$this->_pluginAPI->getActionManager ();
		$a = $userManager->login ($login, $password);
		if (isError ($a)) {
			if ($a->is ('USERMANAGER_LOGIN_FAILED_INCORRECT_INPUT')) {
				$sm = &$this->_pluginAPI->getSmarty ();
				$this->_pluginAPI->addRuntimeMessage (
					'Given a wrong password/username.', ERROR);
				$this->_pluginAPI->executePreviousAction ();
			} else {
				return $a;
			}
		} else {
			$this->_pluginAPI->addMessage ('You are now logged in.', NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onLogout () {
		$userManager = &$this->_pluginAPI->getUserManager ();
		$a = $userManager->logout ();
		if (isError ($a)) {
			return $a;
		} else {
			$this->_pluginAPI->addMessage ('You are logged out.', NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onRegisterForm () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->appendTo ('MorgOS_CurrentPage_Content', $sm->fetch ('user/register.tpl'));
		$sm->display ('genericpage.tpl');
	}	
	
	function onRegister ($login, $email, $password) {
		$uM = &$this->_pluginAPI->getUserManager ();
		$u = $uM->newUser ();
		$u->initFromArray (array ('login'=>$login, 'email'=>$email, 
			'password'=>md5 ($password)));
		$r = $uM->addUserToDatabase ($u);
		if (! isError ($r)) {
			$this->_pluginAPI->addMessage (
				'Your account was succesfully created', NOTICE);
		} elseif ($r->is ('USERMANAGER_LOGIN_EXISTS')) {
			$this->_pluginAPI->addMessage (
				'This login is already used, try another one.', ERROR);			
		} elseif ($r->is ('USERMANAGER_EMAIL_EXISTS')) {
			$this->_pluginAPI->addMessage (
				'This email is already used, try another one.', ERROR);
		} else {
			$this->_pluginAPI->addMessage (
				'There was a problem with adding you to the database', ERROR);
		}
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onForgotPassword () {
	}
	
	function onMyAccountForm () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$userM = &$this->_pluginAPI->getUserManager ();
		$user = $userM->getCurrentUser ();
		$sm->assign ('MorgOS_User_MyAccount_OldEmail', $user->getFieldValue ('email'));
		$sm->appendTo ('MorgOS_CurrentPage_Content', $sm->fetch ('user/myaccount.tpl'));
		$sm->display ('genericpage.tpl');
	}
	
	function onChangePassword ($oldPassword, $newPassword) {
		$userM = &$this->_pluginAPI->getUserManager ();
		$user = $userM->getCurrentUser ();
		if ($user->isValidPassword ($oldPassword)) {
			$user->changePassword ($newPassword);
			$userM->logout ();
			$userM->login ($user->getLogin (), $newPassword);
			$this->_pluginAPI->addMessage ('Your password is changed', NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		} else {
			$this->_pluginAPI->addMessage ('Wrong password', NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onChangeAccount ($newEmail) {
		$userM = &$this->_pluginAPI->getUserManager ();
		$user = $userM->getCurrentUser ();
		$user->updateFromArray (array ('email'=>$newEmail));
		$user->updateToDatabase ();
		$this->_pluginAPI->addMessage ('Your account settings are changed.', NOTICE);
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function setUserVars () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$um = &$this->_pluginAPI->getUserManager ();
		$curUser = $um->getCurrentUser ();
		if ($curUser) {
			$curUserArray = array ('Name'=>$curUser->getLogin ());
			$sm->assign ('MorgOS_CurUser', $curUserArray);
		} else {
			$sm->assign ('MorgOS_CurUser', null);
		}
		$sm->assign ('MorgOS_RegisterFormLink', 'index.php?action=userRegisterForm');
		$sm->appendTo ('MorgOS_Sidebar_Content', 
			$sm->fetch ('user/sideboxcontent.tpl'));
		return true;
	}

	function isCorePlugin () {return true;}
	
	function install (&$pluginAPI, &$dbModule, $siteDefaultLanguage) {
		$uM = new UserManager ($dbModule);
		$uM->installAllTables ();
		
		$pageM = new pageManager ($dbModule);
		$t = &$pluginAPI->getI18NManager();
		$site = $pageM->getSitePage ();
		$myaccount = $pageM->newPage ();
		$regform = $pageM->newPage ();
		
		$regform->initFromArray (array (
				'name'=>'MorgOS_RegisterForm',  
				'parent_page_id'=>$site->getID (), 'action'=>'userRegisterForm', 
				'place_in_menu'=>0, 'plugin_id'=>MORGOS_USER_PLUGINID));
		$myaccount->initFromArray (array (
				'name'=>'MorgOS_User_MyAccount',  
				'parent_page_id'=>$site->getID (), 'action'=>'userMyAccount', 
				'place_in_menu'=>0, 'plugin_id'=>MORGOS_USER_PLUGINID));
		
		$pageM->addPageToDatabase ($regform);
		$pageM->addPageToDatabase ($myaccount);		
		
		$tMyAccount = $pageM->newTranslatedPage ();
		$tRegForm = $pageM->newTranslatedPage ();
		
		$tMyAccount->initFromArray (array (
				'language_code'=>$siteDefaultLanguage, 
					'translated_title'=>$t->translate ('My Account'), 
					'translated_content'=>
						$t->translate ('This is your account page.')));
		$tRegForm->initFromArray (array (
				'language_code'=>$siteDefaultLanguage, 
					'translated_title'=>$t->translate ('Registration'), 
					'translated_content'=>
						$t->translate ('Give up all your user details in order to registrate to this site.')));
		$regform->addTranslation ($tRegForm);
		$myaccount->addTranslation ($tMyAccount);
		$this->_adminPlugin->install ($pluginAPI, $dbModule, $siteDefaultLanguage);
	}
	
	function isInstalled (&$pluginAPI) {
		$db = &$pluginAPI->getDBModule ();
		return $db->tableExists ('groupPermissions') && 
			$db->tableExists ('groups') && $db->tableExists ('translatedGroups') && 
			$db->tableExists ('groupUsers') && $db->tableExists ('users');
	}
}
?>
