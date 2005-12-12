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
error_reporting (E_ALL);
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

if (array_key_exists ('phase', $_GET) == false) {
	$phase = 'start';
} else {
	$phase = $_GET['phase'];
}
switch ($phase) {
	case 'start':
		include ('install/license.php');
		break;
	case 'check':
		include ('install/check.php');
		break;
	case 'config':
		include ('install/config.php');
		break;
	case 'checkdbconn':
		include ('install/checkdbconn.php');
		break;
	case 'install':
		include ('install/installing.php');
		break;
	case 'installdb':
		include ('install/installingdb.php');
		break;
	default:
		include ('install/license.php');
		break;
}
?>
