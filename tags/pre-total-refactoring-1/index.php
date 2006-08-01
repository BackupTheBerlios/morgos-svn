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
/** \file index.php
 * Main file
 *
 * \todo change everything to invalidLink
 * \todo implement invalidLink
 * $Id$
 * \author Nathan Samson
*/
error_reporting (E_ALL);
global $startTime;
list($usec, $sec) = explode(" ",microtime());
$startTime = ((float)$usec + (float)$sec);
include ('core/uimanager.class.php');
$UI = new UIManager ();

if (array_key_exists ('module', $_GET)) {
	$selectedModule = $_GET['module'];
} else {
	$selectedModule = 'index';
}

$pages = $UI->getPagesClass ();
$availableModules = $pages->getAllAvailableModules (true);
if ($selectedModule == 'viewadmin') {
	header ('Location: admin.php');
} elseif ($selectedModule == 'login') {
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
} elseif ($selectedModule == 'logout') {
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
} elseif ($selectedModule == 'registeruser') {
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
} elseif ($selectedModule == 'saveusersettings') {
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
} elseif ($selectedModule == 'sendpass') {
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
} elseif ($selectedModule == 'postnews') {
	$UI->setrunning (true);
	$do = true;
	$s = getFrom ('post', 'subject', $subject);
	if ($s == false) {
		trigger_error ('ERROR: ' . $UI->i10nMan->translate ('News was not posted, required field subject was not filled in'));
		$do = false;
	}
	$m = getFrom ('post', 'message', $message);
	if ($m == false) {
	 	trigger_error ('ERROR: ' . $UI->i10nMan->translate ('News was not posted, required field message was not filled in'));
	 	$do = false;
	}
	$t = getFrom ('post', 'topic', $topic);
	if ($t == false) {
	 	trigger_error ('ERROR: ' . $UI->i10nMan->translate ('News was not posted, required field topic was not filled in'));
		$do = false;
	}
	$topic = 'image';
	$UI->setrunning (false);
	if ($do == true) {
		$user = $UI->getUserClass ();
		if ($user->isLoggedIn ()) {
			$u = $user->getUser ();
			$username = $u['username'];
			$UI->setrunning (true);
			$language = $u['contentlanguage'];
			if ($UI->news->addNewsItem ($subject, $message, $topic, $username, $language)) {
				trigger_error ('NOTICE: ' . $UI->i10nMan->translate ('Your newsitem was successfully posted.'));
				getFrom (array ('post', 'session'), 'fromPage', $from, 'index');
				$UI->setrunning (false);
				$UI->loadPage ($from);
			} else {
				trigger_error ('ERROR: ' . $UI->i10nMan->translate ('Your newsitem was not posted.'));
				$UI->loadPage ('formpostnews');
			}
		} else {
			$UI->setrunning (true);
			trigger_error ('ERROR: You are not logged in, please login first.');
			$UI->setrunning (false);
			$UI->loadPage ('formpostnews');
		}
	} else {
		$UI->loadPage ('formpostnews');
	}
} elseif ($selectedModule == 'viewnewsitem') {
	$f = getFrom ('get', 'newsID', $ID);
	if ($f == false) {
		$UI->loadPage ('invalidLink');
	}
	
	global $newsID;
	$newsID = $ID;
	$UI->loadPage ('viewnewsitem');
	
} elseif ($selectedModule == 'formpostcomment') {
	$f = getFrom ('get', 'onItem', $onItemID);
	if ($f == false) {
		$UI->loadPage ('invalidLink');
	}
	
	$f = getFrom ('get', 'onNews', $onNews);
	if ($f == false) {
		$UI->loadPage ('invalidLink');	
	}
	
	global $onItemID, $onNews;
	$UI->loadPage ('formpostcomment');
} elseif ($selectedModule == 'postcomment') {
	$f = getFrom ('post', array ('subject', 'message'), $infoArrayUser, array (), array ('subject', 'message'));
	if ($f !== true) {
		$UI->setRunning (true);
		foreach ($f as $e) {
			trigger_error ($UI->i10nMan->translate ('You need to fill in %s', $this->i10nMan->translate ($e)));
		}
		$UI->setRunning (false);
		$UI->loadPage ('index');
	} else {
		$f = getFrom ('post', array ('onitem_number', 'onnews'), $infoArrayNews);
		if ($f !== true) {
			$UI->loadPage ('invalidLink');
		} else {
			$user = $UI->getUserClass ();
			$userInfo = $user->getUser ();
			$userID = $userInfo['username'];
			$language = $userInfo['contentlanguage'];
			$UI->setRunning (true);
			$result = $UI->news->addComment ($infoArrayUser['subject'], $infoArrayUser['message'], $language, $infoArrayNews['onnews'], $infoArrayNews['onitem_number'], $userID);
			if ($result == true) {
				trigger_error ('NOTICE: ' . $UI->i10nMan->translate ('Your comment is successfully posted.'));	
			} else {
				trigger_error ('ERROR: ' . $UI->i10nMan->translate ('Your comment is not posted.'));	
			}
			$UI->setRunning (false);
			$UI->loadPage ('index');
		}
	} 	
} elseif (array_key_exists ($selectedModule, $availableModules)) {
	$UI->loadPage ($selectedModule);
}  else {
	trigger_error ('ERROR: Can\'t load this page, doesn\'t exists');
}
?>
