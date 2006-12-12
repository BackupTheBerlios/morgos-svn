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
/** \file core/tests/index.php
 * Suite for the tests
 *
 * @since 0.2
 * @since 0.3 complete rewrite
 * @author Nathan Samson
*/

if (version_compare (PHP_VERSION, '5', '>=')) {
	$config = parse_ini_file ('options.ini');
	$statement = $config['phpUnitPath'] . ' ' . 
		$config['phpUnitParameters'] . ' MorgOSLoader core/tests/runtests.5.php';

	chdir ('../..');
	$exec = `$statement`;
	echo nl2br (htmlentities ($exec));
} elseif (version_compare (PHP_VERSION, '4', '>=')) {
	chdir ('../..');
	include_once ('core/tests/base.php');
	$suite = new TestSuite ();
	loadSuite ($suite);
	
	require_once ('PHPUnit/GUI/HTML.php');
	$GUI = new PHPUnit_GUI_HTML ($suite->getAllTests ());
	$GUI->show ();
}

?>
