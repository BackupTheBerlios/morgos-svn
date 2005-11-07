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
	$module = $_GET['module']; 
} else {
	$module = 'index';
}

switch ($module) {
	case 'database':
		$UI->loadPage ('admin/database.html', NULL, true, true);
		break;
	case 'databasesave':
		if ($_POST['submit'] == 'ADMIN_DATABASE_FORM_INSTALL_NEW_DATABASE') {
			// install the database again (and maybe copy from the old one)
		}
		if ($UI->saveAdmin ($_POST, '/database/type', '/database/host', '/database/name', '/database/user', '/database/password')) {
			header ('Location: admin.php?module=database');
		}
	case 'users':
		$UI->loadPage ('admin/users.html', NULL, true, true);
		break;
	case 'news':
		$UI->loadPage ('admin/news.html', NULL, true, true);
		break;
	case 'general':
		$UI->loadPage ('admin/general.html', NULL, true, true);
		break;
	case 'generalsave':
		if ($UI->saveAdmin ($_POST, '/general/sitename')) {
			header ('Location: admin.php?module=general');
		}
		break;
	case 'pages':
		$UI->loadPage ('admin/pages.html', NULL, true, true);
		break;
	case 'pagessave':
		if (! array_key_exists ('submit', $_POST)) {
			foreach ($_POST as $key => $value) {
				if (substr ($key, 0, 9) == 'VIEW_PAGE') {
					$language = $_POST['LANGUAGE_' . substr ($key, 9)];
					$module = str_replace ('_html', '.html', substr ($key, 9));
					$UI->loadPage ($module, $language);
				} elseif (substr ($key, 0, 8) == 'ADD_PAGE') {
					$module = str_replace ('_html', '.html', substr ($key, 8));
					header ('Location: admin.php?module=addpage&tomodule=' . $module);
				}
			}
		} elseif ($_POST['submit'] == $UI->i10nMan->translate ('Add module')) {
			$UI->addModule ($_POST['NEW_MODULE_NAME'], $_POST['NEW_MODULE_NEEDAUTHORIZE']);
			header ('Location: admin.php?module=pages');
		} elseif ($_POST['submit'] == 'EDIT_PAGE') {
		} elseif ($_POST['submit'] == $UI->i10nMan->translate ('Save settings')) {
			foreach ($UI->getAllAvailableModules () as $module) {
				$module['module'] = str_replace ('.html', '_html', $module['module']);
				if (array_key_exists ('NEED_AUTHORIZE' . $module['module'], $_POST)) {
					$needAuthorize = true;
				} else {
					$needAuthorize = false;
				}
				$module['module'] = str_replace ('_html', '.html', $module['module']);
				$UI->changeSettingsModule ($module['module'], $needAuthorize);
			}
			header ('Location: admin.php?module=pages');
		} else {
			header ('Location: admin.php?module=pages');
		}
		break;
	case 'addpage':
		// we need some global variables here to make the vars in core/uimanager.vars.class.php correct
		$addToModule = $_GET['tomodule'];
		$UI->loadPage ('admin/addpage.html', NULL, true, true);
		break;
	case 'addpagesave':
		$UI->addPage ($_POST['module'], $_POST['language'], $_POST['name'], $_POST['content']);
		header ('Location: admin.php?module=pages');
		break;
	case 'index':
		// do the default one
	default:
		$UI->loadPage ('admin/index.html', NULL, true, true);
		break;
}
?>
