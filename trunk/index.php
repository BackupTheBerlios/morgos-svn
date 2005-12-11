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
*/
include ('core/uimanager.class.php');
$UI = new UIManager ();

if (array_key_exists ('module', $_GET)) {
	$choosenModule = $_GET['module'];
} else {
	$choosenModule = 'index';
}

$availableModules = $UI->getAllAvailableModules ();
if ($choosenModule == 'viewadmin') {
	header ('Location: admin.php');
} elseif ($choosenModule == 'login') {
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
	$user = $UI->getUserClass ();
	$UI->setRunning (true);
	if ($_POST['account-password'] != $_POST['account-password2']) {
		trigger_error ('ERROR: The 2 passwords are not equal');
		$UI->setRunning (false);
		$UI->loadPage ('register');
	} else {
		$success = $user->insertUser ($_POST['account-name'], $_POST['account-email'], $_POST['account-password'], false);
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
} elseif (array_key_exists ($choosenModule, $availableModules)) {
	$UI->loadPage ($choosenModule);
}  else {
	trigger_error ('ERROR: Can\'t load this page, doesn\'t exists');
}
?>
