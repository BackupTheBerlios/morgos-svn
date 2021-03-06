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
global $startTime;
list($usec, $sec) = explode(" ",microtime());
$startTime = ((float)$usec + (float)$sec);
include ('core/uimanager.class.php');
$UI = new UIManager ();

if (array_key_exists ('module', $_GET)) {
	$module = $_GET['module']; 
} else {
	$module = 'index';
}

switch ($module) {
	case 'databasesave':
		if ($_POST['submit'] == 'ADMIN_DATABASE_FORM_INSTALL_NEW_DATABASE') {
			// install the database again (and maybe copy from the old one)
		}
		if ($UI->saveAdmin ($_POST, '/database/type', '/database/host', '/database/name', '/database/user', '/database/password')) {
			header ('Location: admin.php?module=database');
		}
	case 'generalsave':
		if ($UI->saveAdmin ($_POST, '/general/sitename')) {
			header ('Location: admin.php?module=general');
		}
		break;
	case 'pagessave':
		if (! array_key_exists ('submit', $_POST)) {
			foreach ($_POST as $key => $value) {
				if (substr ($key, 0, 9) == 'VIEW_PAGE') {
					$language = $_POST['LANGUAGE_' . substr ($key, 9)];
					$module = substr ($key, 9);
					$UI->loadPage ($module, $language);
				} elseif (substr ($key, 0, 8) == 'ADD_PAGE') {
					$module = substr ($key, 8);
					header ('Location: admin.php?module=addpage&tomodule=' . $module);
				} elseif (substr ($key, 0, 11) == 'DELETE_PAGE')  {
					$module = substr ($key, 11);
					$UI->deletePage ($module, $_POST['LANGUAGE_' . substr ($key, 11)]);
					header ('Location: admin.php?module=pages');
				} elseif (substr ($key, 0, 13) == 'DELETE_MODULE')  {
					$module = substr ($key, 13);
					$UI->deleteModule ($module);
					header ('Location: admin.php?module=pages');
				} elseif ((substr ($key, 0, 9) == 'EDIT_PAGE') and (substr ($key, 9, 5) != '_SAVE')) {
					$editPageModule = substr ($key, 9);
					$editPageLanguage = $_POST['LANGUAGE_' . $editPageModule];
					$UI->loadPage ('admin/editpage');
				} elseif (substr ($key, 0, 14) == 'EDIT_PAGE_SAVE')  {
					$module = substr ($key, 14);
					$UI->pages->editPage ($module, $_POST['language'], $_POST['newname'], $_POST['newcontent']);
					header ('Location: admin.php?module=pages');
				}
			}
		} elseif ($_POST['submit'] == $UI->i10nMan->translate ('Add module')) {
		 	// The post item is only created when it is checked
			if (array_key_exists ('NEW_MODULE_NEEDAUTHORIZE', $_POST)) {
				$needAuthorize = true;
			} else {
				$needAuthorize = false;
			}
			$newModule = str_replace (' ', '_', $_POST['NEW_MODULE_NAME']);
			$UI->addModule ($newModule, $needAuthorize, false);
			header ('Location: admin.php?module=pages');
		} elseif ($_POST['submit'] == $UI->i10nMan->translate ('Save settings')) {
			foreach ($UI->getAllAvailableModules () as $module) {
				if (array_key_exists ('NEED_AUTHORIZE' . $module['module'], $_POST)) {
					$needAuthorize = true;
				} else {
					$needAuthorize = false;
				}
				
				if (array_key_exists ('ADMIN_ONLY' . $module['module'], $_POST)) {
					$adminOnly = true;
				} else {
					$adminOnly = false;
				}
				
				$UI->changeSettingsModule ($module['module'], $needAuthorize, $adminOnly);
			}
			header ('Location: admin.php?module=pages');
		} else {
			header ('Location: admin.php?module=pages');
		}
		break;
	case 'addpage':
		// we need some global variables here to make the vars in core/uimanager.vars.class.php correct
		$addToModule = $_GET['tomodule'];
		$UI->loadPage ('admin/addpage', NULL, true, true);
		break;
	case 'addpagesave':
		$UI->addPage ($_POST['module'], $_POST['language'], $_POST['name'], $_POST['content']);
		header ('Location: admin.php?module=pages');
		break;
	case 'saveusers':
		$UI->user = new user ($UI->genDB);
		foreach ($UI->user->getAllUsers () as $user) {
			if (array_key_exists ($user['username'], $_POST)) {
				$UI->user->setAdmin ($user['username'], true);
			} else {
				$UI->user->setAdmin ($user['username'], false);
			}
		}
		
		$UI->setRunning (true);
		trigger_error ('NOTICE: User options are saved');
		$UI->setRunning (false);
		$UI->loadPage ('admin/users', NULL, true, true);
		break;
	case 'saveextensions':
		$config = &$UI->getConfigClass ();
		$config->removeConfigItem ('/extensions');
		foreach ($_POST as $key => $item) {
			if (substr ($key, 0, 4) == 'load') {
				$extensionName = substr ($key, 4);
				$extensionName = str_replace ('_', ' ', $extensionName);
				if ($item == 'on') {
					$config->addConfigItem ('/extensions/' . $extensionName, true, TYPE_BOOL);
					if (array_key_exists ($extensionName, $UI->extensions)) {
						if ($UI->extensions[$extensionName]['installable'] == true) {
							$UI->installExtension ($extensionName);
						}
					}
				}
			}
		}
		$config->addConfigItem ('/extensions/WHATEVER', false, TYPE_BOOL);
		$UI->saveAdmin (array ());
		$UI = NULL;
		$UI = new UIManager (); // to reload all extensions
		$UI->loadPage ('admin/extensions');
		break;
	case 'installextension':
		$UI->installExtension ($_GET['name']);
		$UI = NULL;
		$UI = new UIManager (); // to reload all extensions
		$UI->setRunning (true);
		trigger_error ('NOTICE: Extension is installed in the database.');
		$UI->setRunning (false);
		$UI->loadPage ('admin/extensions');
		break;
	case 'uninstallextension':
		$UI->setRunning (true);
		trigger_error ('NOTICE: Extension is uninstalled in the database.');
		if ($UI->config->exists ('/extensions/' . $_GET['name'])) {
			$UI->config->removeConfigItem ('/extensions/' . $_GET['name']);
			$UI->saveAdmin (array ());
		}
		$UI->unInstallExtension ($_GET['name']);
		$UI->setRunning (false);
		$UI = new UIManager (); // to reload all extensions
		$UI->loadPage ('admin/extensions');
		break;	
	default:
		$pages = &$UI->getPagesClass ();
		$allModules = $pages->getAllAvailableModules (true);
		if (array_key_exists ('admin/' . $module, $allModules)) {
			$UI->loadPage ('admin/' . $module);
		} else {
			header ('Location: http://127.0.0.1'); // Nice joke for hackers
		}
		break;
}
?>
