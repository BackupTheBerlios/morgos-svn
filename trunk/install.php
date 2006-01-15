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
error_reporting (E_ALL);
$SAVES = array ();
$execPhase = false;
define ('PHASE_START', 'start');
define ('PHASE_CHECK', 'check');
define ('PHASE_CONFIG', 'config');
define ('PHASE_INSTALL', 'install');
define ('PHASE_INSTALLDB', 'installdb');

/*Check that all required files exist*/
function checkFile ($file) {
	if (! file_exists ($file)) {
		trigger_error ("ERROR: A required file to install MorgOS is not found. ($file)");
	}
}

checkFile ('install/license.php');
checkFile ('install/check.php');
checkFile ('install/config.php');
//checkFile ('install/checkdbconn.php');
checkFile ('install/installing.php');
checkFile ('install/installingdb.php');
checkFile ('install/sql/news.sql');
checkFile ('install/sql/pages.sql');
checkFile ('install/sql/users.sql');
checkFile ('core/language.class.php');

include_once ('core/language.class.php');
$errors = array ();
function errorHandlerForInstaller ($errNo, $errStr, $errFile = NULL, $errLine = 0, $errContext = NULL) {
	global $i10nMan;
	if ($errNo == E_STRICT) {
		return;
	} elseif ($errNo != E_USER_NOTICE) {
		$type = 'PHP';
		$error = $errStr;
	} else {
		$pos = strpos ($errStr, ": ");
		if ($pos != 0) {
			$type = substr ($errStr, 0, $pos);
			$error = substr ($errStr, $pos + 2);
		} else {
			$type = 'UKNOWN';
			$error = $errStr;
		}
	}	
	switch ($type) {
		case "INTERNAL_ERROR":
			$die = true;
			break;
		case "DEBUG":
			$error = $error . ': ' . $errFile . '@' . $errLine;
			$die = false;
			break;
		case "NOTICE":
			$die = false;
			break;
		case "ERROR":
			$die = true;
			break;
		case "WARNING":
			$die = false;
			break;
		case "PHP":
			$error = $error . ': ' . $errFile . '@' . $errLine;
			$die = true;
			break;
		case "UKNOWN";
			$die = false;
			trigger_error ('DEBUG: ' . $i10nMan->translate ('Type is not set'));
			break;
		default:
			$die = true;
			//trigger_error ('INTERNAL_ERROR: Error type is unrecognized.');
	}
	global $execPhase;
	if (($die == true) and ($execPhase == false)) {
		// go the the previous page
		global $phaseProvided, $doPhase;
		switch ($doPhase) {
			case PHASE_START:
				$doPhase = PHASE_START;
				break;
			case PHASE_CHECK:
				$doPhase = PHASE_START;
				break;
			case PHASE_CONFIG:
				$doPhase = PHASE_CHECK;
				break;
			case PHASE_INSTALL:
				$doPhase = PHASE_CONFIG;
				break;
			case PHASE_INSTALLDB:
				$doPhase = PHASE_CONFIG;
				break;
			default:
				$doPhase = PHASE_START;
		}
	}
	global $errors;
	$errors[] = array ('type' => $type, 'error' => $error, 'die' => $die);
}

function showAllErrors () {
	global $errors;
	foreach ($errors as $error) {
		echo '<p>'.$error['type'] . ' ' . $error['error'] .'</p>';
	}
}

function checkParam ($array, $key, $name) {
	global $i10nMan;
	if (array_key_exists ($key, $array)) {
		if ((empty ($array[$key])) or ($array[$key] == NULL) or (trim ($array[$key]) == '')) {
			trigger_error ('ERROR: ' . $i10nMan->translate ('A required field is not filled in, please fill %1 in', $name));
			return false;
		} else {
			return true;
		}
	} else {
		trigger_error ('ERROR: ' . $i10nMan->translate ('A required field is not filled in, please fill %1 in', $name));
		return false;
	}
}

function saveParam ($array, $key) {
	global $SAVES;
	if (array_key_exists ($key, $array)) {
		$SAVES[$key] = $array[$key];
	}
}

function getParam ($key) {
	global $SAVES;
	if (array_key_exists ($key, $SAVES)) {
		return $SAVES[$key];
	} else {
		return NULL;
	}
}

if (array_key_exists ('phase', $_GET) == false) {
	$phaseProvided = PHASE_START;
} else {
	$phaseProvided = $_GET['phase'];
}
$doPhase = $phaseProvided;

set_error_handler ('errorHandlerForInstaller');
$i10nMan = new languages ('install/languages/', 'english');
if (array_key_exists ('language', $_COOKIE)) {
	$language = $_COOKIE['language'];
} elseif (array_key_exists ('language', $_GET)) {
	$language = $_GET['language'];
	setcookie ('language', addslashes ($language));
} else {
	$language = 'english';
}
$language = addslashes ($language);
$result = @$i10nMan->loadLanguage ($language); // if it couldn\'t load don\'t throw an error

$canrun = false;
while ($canrun == false) {
	switch ($doPhase) {
		case PHASE_START:
			$canrun = true;
			break;
		case PHASE_CHECK:
			if (array_key_exists ('agree', $_POST)) {
				if ($_POST['agree'] == 'no') {
					trigger_error ('ERROR: ' . $i10nMan->translate ('You need to agree with the license.'));
				} else {
					$canrun = true;
				}
			} else {
				trigger_error ('ERROR: ' . $i10nMan->translate ('You need to agree with the license.'));
			}
			break;
		case PHASE_CONFIG:
			if (array_key_exists ('canrun', $_POST)) {
				if ($_POST['canrun'] == 'no') {
					trigger_error ('ERROR: ' . $i10nMan->translate ('You need to install all requirements before you can proceed.'));
				} else {
					$canrun = true;
				}
			} else {
				trigger_error ('ERROR: ' . $i10nMan->translate ('You need to install all requirements before you can proceed.'));
			}
			break;
		/*case PHASE_CHECKDBCONN:
			include ('install/checkdbconn.php');
			break;*/
		case PHASE_INSTALL:
			$t = checkParam ($_POST, 'site-name', 'Sitename');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'database-type', 'Database type');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'database-host', 'Database host');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'database-user', 'Database user');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'database-password', 'Database password');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'database-name', 'Database name');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'admin-account', 'Admin name');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'admin-email', 'Admin email');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'admin-password', 'Admin password');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'admin-password2', 'Admin password repeat');
			if ($t == false) {
				break;
			}
			
			saveParam ($_POST, 'site-name');
			saveParam ($_POST, 'database-type');
			saveParam ($_POST, 'database-host');
			saveParam ($_POST, 'database-user');
			saveParam ($_POST, 'database-name');
			saveParam ($_POST, 'admin-account');
			saveParam ($_POST, 'admin-email');
			
			if ($_POST['admin-password'] != $_POST['admin-password2']) {
				trigger_error ('ERROR: ' . $i10nMan->translate ('Provided passwords doesn\'t match.'));
			} else {
				$canrun = true;
			}
			
			global $errors;
			$curErrors = $errors;
			include_once ('core/database.class.php');
			$DBMan = new genericDatabase ($i10nMan);
			$DB = $DBMan->load ($_POST['database-type']);
			$DB->connect ($_POST['database-host'], $_POST['database-user'], $_POST['database-password']);
			if ($errors == $curErrors) {
				$canrun = true;
			}
			
			break;
		case PHASE_INSTALLDB:
			$t = checkParam ($_POST, 'admin-email', 'Admin email');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'admin-password', 'Admin password');
			if ($t == false) {
				break;
			}
			$t = checkParam ($_POST, 'admin-account', 'Admin account');
			if ($t == false) {
				break;
			}
			
			$canrun = true;
			break;
		default:
			break;
	}
}

$execPhase = true;
switch ($doPhase) {
	case PHASE_START:
		include ('install/license.php');
		break;
	case PHASE_CHECK:
		include ('install/check.php');
		break;
	case PHASE_CONFIG:
		include ('install/config.php');
		break;
	/*case PHASE_CHECKDBCONN:
		include ('install/checkdbconn.php');
		break;*/
	case PHASE_INSTALL:
		include ('install/installing.php');
		break;
	case PHASE_INSTALLDB:
		include ('install/installingdb.php');
		break;
	default:
		include ('install/license.php');
		break;
}
?>
