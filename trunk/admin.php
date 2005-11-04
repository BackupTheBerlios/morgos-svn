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

switch ($_GET['module']) {
	case 'database':
		$UI->loadModule ('admin/database.html', true, true);
		break;
	case 'databasesave':
		if ($_POST['submit'] == $UI->vars['ADMIN_DATABASE_FORM_INSTALL_NEW_DATABASE']) {
			// install the database again (and maybe copy from the old one)
		}
		if ($UI->saveAdmin ($_POST, '/database/type', '/database/host', '/database/name', '/database/user', '/database/password')) {
			header ('Location: admin.php?module=database');
		}
	case 'users':
		$UI->loadModule ('admin/users.html', true, true);
		break;
	case 'news':
		$UI->loadModule ('admin/news.html', true, true);
		break;
	case 'general':
		$UI->loadModule ('admin/general.html', true, true);
		break;
	case 'generalsave':
		if ($UI->saveAdmin ($_POST, '/general/sitename')) {
			header ('Location: admin.php?module=general');
		}
		break;
	case 'pages':
		$UI->loadModule ('admin/pages.html', true, true);
		break;
	case 'index':
		// do the default one
	default:
		$UI->loadModule ('admin/index.html', true, true);
		break;
}
?>
