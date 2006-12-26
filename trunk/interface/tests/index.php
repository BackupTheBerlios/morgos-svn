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

if (version_compare (PHP_VERSION, '5', '>=')) {
	$config = parse_ini_file ('options.ini');
	
	//var_dump ($config['phpUnitCC']);
	if ($config['phpUnitCC'] == true) {
		$config['phpUnitParameters'] .= ' --report ' . $config['phpUnitCCOutputPath'];
	}	
	
	$statement = $config['phpUnitPath'] . ' ' . 
		$config['phpUnitParameters'] . ' MorgOSInterface interface/tests/runtests.5.php';

	chdir ('../..');
	
	ob_start ();
	system ($statement, $returnVar);
	$exec = ob_get_contents ();
	ob_end_clean ();
	
	if (! $returnVar) {
		if ($config['phpUnitCC'] ) {
			echo ('<a href="../../'.$config['phpUnitCCOutputPath'].'">Visit output</a><br /><br />');
		}
	}
	echo nl2br ($exec);
} elseif (version_compare (PHP_VERSION, '4', '>=')) {
	chdir ('../..');
	include_once ('interface/tests/base.php');
	$suite = new TestSuite ();
	loadSuite ($suite);
	
	require_once ('PHPUnit/GUI/HTML.php');
	$GUI = new PHPUnit_GUI_HTML ($suite->getAllTests ());
	$GUI->show ();
}