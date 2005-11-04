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
	$choosenModule = 'index.html';
}

$availableModules = $UI->getAllAvailableModules ();
if (in_array ($choosenModule, $availableModules)) {
	$UI->loadModule ($choosenModule);
} else {
	trigger_error ('Can\'t load this page, doesn\'t exists', E_USER_ERROR);
}
?>
