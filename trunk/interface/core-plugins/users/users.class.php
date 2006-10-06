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
class userCorePlugin extends plugin {
	
	function userCorePlugin ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Users core plugin';
		$this->_ID = '{5df79e7c-2c14-4ad2-b13e-5c420d33182a}';
		$this->_minMorgOSVersion = '0.2';
		$this->_maxMorgOSVersion = '0.2';
	}
	
	function load (&$pluginAPI) {
		parent::load (&$pluginAPI);
		$am = &$this->_pluginAPI->getActionManager ();
		$am->addAction (new action ('userLogin', 'POST',  array ($this, 'onLogin'), 
			array ('login','password'), array ()));
		$am->addAction (new action ('userLogout', 'POST',  array ($this, 'onLogout'), 
			array (), array ()));
		$am->addAction (
			new action ('userRegisterForm', 'POST',  array ($this, 'onRegisterForm'), 
				array (), array ('pageLang')));
		$am->addAction (
			new action ('userRegister', 'POST',  array ($this, 'onRegister'), 
				array (new StringInput ('login'), new EmailInput ('email'), 
					new PasswordNewInput ('password')), 
				array ()));
		
		$em = &$this->_pluginAPI->getEventManager ();
		$em->subscribeToEvent ('viewPage', new callback ('userVars', array ($this, 'setUserVars')));
	}
	
	function onLogin ($login, $password) {
		$userManager = &$this->_pluginAPI->getUserManager ();
		$am = &$this->_pluginAPI->getActionManager ();
		$a = $userManager->login ($login, $password);
		if (isError ($a)) {
			if ($a->is ('USERMANAGER_LOGIN_FAILED_INCORRECT_INPUT')) {
				$sm = &$this->_pluginAPI->getSmarty ();
				$this->_pluginAPI->addRuntimeMessage ('Given a wrong password/username.', ERROR);
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
	
	function onRegisterForm ($pageLang) {
		$pM = &$this->_pluginAPI->getPageManager ();
		$regForm = $pM->newPage ();
		$regForm->initFromName ('MorgOS_registerForm');
	
		$em = &$this->_pluginAPI->getEventManager ();
		if ($pageLang == null) {
			$pageLang = 'en_UK';
		}
		$em->triggerEvent ('viewPage', array ($regForm->getID (), $pageLang));
		$this->setUserVars ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->display ('user/registerform.tpl');
	}	
	
	function onRegister ($login, $email, $password) {
		$uM = &$this->_pluginAPI->getUserManager ();
		$u = $uM->newUser ();
		$u->initFromArray (array ('login'=>$login, 'email'=>$email, 'password'=>md5 ($password)));
		$r = $uM->addUserToDatabase ($u);
		if (! isError ($r)) {
			$this->_pluginAPI->addMessage ('Your account was succesfully created', NOTICE);
		} elseif ($r->is ('USERMANAGER_LOGIN_EXISTS')) {
			$this->_pluginAPI->addMessage ('This login is already used, try another one.', ERROR);			
		} elseif ($r->is ('USERMANAGER_EMAIL_EXISTS')) {
			$this->_pluginAPI->addMessage ('This email is already used, try another one.', ERROR);
		} else {
			$this->_pluginAPI->addMessage ('There was a problem with adding you to the database', ERROR);
		}
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onForgotPassword () {
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
		return true;
	}

	function isCorePlugin () {return true;}
}
?>