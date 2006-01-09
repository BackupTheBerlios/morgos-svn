<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005 MorgOS
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
*/global $startTime;
list($usec, $sec) = explode(" ",microtime());
$startTime = ((float)$usec + (float)$sec);
include ('core/uimanager.class.php');
$UI = new UIManager ();

if (array_key_exists ('module', $_GET)) {
	$choosenModule = $_GET['module'];
} else {
	$choosenModule = 'index';
}

$pages = $UI->getPagesClass ();
$availableModules = $pages->getAllAvailableModules (true);
if ($choosenModule == 'viewadmin') {
	header ('Location: admin.php');
} elseif ($choosenModule == 'login') {
	$UI->signalMan->execSignal ('login', $_POST['loginname'], $_POST['password']);
	$user = $UI->getUserClass ();
	$UI->setRunning (true);
	$success = $user->login ($_POST['loginname'], $_POST['password']);
	if ($success) {
		trigger_error ('NOTICE: You are now logged in.');
	} else {
		trigger_error ('ERROR: You are not logged in.');
	}
	$UI->setRunning (false);
	$UI->loadPage ('index');
} elseif ($choosenModule == 'logout') {
	$UI->signalMan->execSignal ('logout');
	$user = $UI->getUserClass ();
	$UI->setRunning (true);
	$success = $user->logout ();
	if ($success) {
		trigger_error ('NOTICE: You are now logged out.');
	} else {
		trigger_error ('ERROR: You are not logged out.');
	}
	$UI->setRunning (false);
	$UI->loadPage ('index');
} elseif ($choosenModule == 'registeruser') {
	$UI->signalMan->execSignal ('registeruser');
	$user = $UI->getUserClass ();
	$UI->setRunning (true);
	if ($_POST['account-password'] != $_POST['account-password2']) {
		trigger_error ('ERROR: The 2 passwords are not equal');
		$UI->setRunning (false);
		$UI->loadPage ('register');
	} else {
		$settings = array ();
		$settings['language'] = 'english';
		$settings['contentlanguage'] = 'english';
		$settings['skin'] = 'MorgOS Default';
		$success = $user->insertUser ($_POST['account-name'], $_POST['account-email'], $_POST['account-password'], false, $settings);
		if ($success) {
			trigger_error ('NOTICE: You are registerd now.');
			$UI->setRunning (false);
			$UI->loadPage ('index');
		} else {
			trigger_error ('NOTICE: You are not registerd.');
			$UI->setRunning (false);
			$UI->loadPage ('register');
		}
	}
} elseif ($choosenModule == 'saveusersettings') {
	$userClass = $UI->getUserClass ();
	$user = $userClass->getUser ();
	$username = $user['username'];
	$UI->setRunning (true);
	if ($_POST['account-password1'] != $_POST['account-password2']) {
		trigger_error ('ERROR: Your settings aren\'t saved');
		trigger_error ('ERROR: The 2 passwords are not equal');
		$UI->setRunning (false);
		$UI->loadPage ('usersettings');
	} else {
		$settings = array ();
		$settings['language'] = $_POST['language'];
		$settings['contentlanguage'] = $_POST['contentlanguage'];
		$settings['skin'] = $_POST['skin'];
		$success = $userClass->updateUser ($username, $_POST['account-email'], $settings, $_POST['account-password1']);
		if ($success) {
			trigger_error ('NOTICE: Your settings are saved.');
		} else {
			trigger_error ('ERROR: Your settings aren\'t saved');
		}
		$UI->setRunning (false);
		$UI->loadPage ('usersettings');
	}
} elseif ($choosenModule == 'sendpass') {
	$UI->setrunning (true);
	if (!empty ($_POST['username']) && empty ($_POST['useremail'])) {
		$UI->user = new user ($UI->genDB);
		$newp = $UI->user->changePasswordFromUsername ($_POST['username']);
		if ($newp !== false) {
			$user = $UI->user->getUser ($_POST['username']);
			$useremail = $user['email'];
			$username = $_POST['username'];
			$sitename = $UI->config->getConfigItem ('/general/sitename', TYPE_STRING);
			$subject = $UI->i10nMan->translate ('Your new requested password on: %1', $sitename);
			$message = $UI->i10nMan->translate ("Dear %1 \nYou have requested your login-detail on %2.\n Your login-name: %1\n Your new Password %3.\n It is recommendend that you change your password after logging in.", $username, $sitename, $newp);
			$from = 'noreply';
			mail ($useremail, $subject, $message, "FROM: $from \r\n");
			trigger_error ('NOTICE: Your password is send to your email adress');
			$UI->setrunning (false);
			$UI->loadPage ('index');
		} else {
			$UI->setrunning (false);
			$UI->loadPage ('forgotpass');
		}
	} elseif (!empty ($_POST['useremail']) && empty ($_POST['username'])) {
		$UI->user = new user ($UI->genDB);
		$newp = $UI->user->changePasswordFromEmail ($_POST['useremail']);
		if ($newp !== false) {
			$useremail = $_POST['useremail'];
			$user = $UI->user->getUserFromEmail ($_POST['useremail']);
			$username = $user['username'];
			$sitename = $UI->config->getConfigItem ('/general/sitename', TYPE_STRING);
			$subject = $UI->i10nMan->translate ('Your new requested password on: %1', $sitename);
			$message = $UI->i10nMan->translate ("Dear %1 \nYou have requested your login-detail on %2.\n Your login-name: %1\n Your new Password %3.\n It is recommendend that you change your password after logging in.", $username, $sitename, $newp);
			$from = 'noreply';
			mail ($useremail, $subject, $message, "FROM: $from \r\n");
			trigger_error ('NOTICE: Your password is send to your email adress');
			$UI->setrunning (false);
			$UI->loadPage ('index');
		} else {
			$UI->setrunning (false);
			$UI->loadPage ('forgotpass');
		}
	} elseif (empty ($_POST['username']) && empty ($_POST['useremail'])) {
		trigger_error ('ERROR: You need to fill your username OR your email in.');
		$UI->setrunning (false);
		$UI->loadPage ('forgotpass');
	} else {
		trigger_error ('ERROR: You need to fill your username OR your email in.');
		$UI->setrunning (false);
		$UI->loadPage ('forgotpass');
	}	
} elseif (array_key_exists ($choosenModule, $availableModules)) {
	$UI->loadPage ($choosenModule);
}  else {
	trigger_error ('ERROR: Can\'t load this page, doesn\'t exists');
}
?>
