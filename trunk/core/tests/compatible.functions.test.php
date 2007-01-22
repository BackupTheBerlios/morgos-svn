<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2007 MorgOS
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
/** \file compatible.functions.test.php
 * File that take care of testing compatibility functions laying arround.
 *
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/compatible.functions.php');
class compatibleTests extends TestCase {

	function testScanDir () {
		$r = scandir ('core/tests/scandir/');
		$this->assertEquals (array ('.', '..', '.svn','1','10','11','12','2','3','4', 'a1', 'empty',  'file1', 'file2'), $r, 'Regular dir fails');
		
		$r = scandir ('core/tests/scandir/empty'); // hmm, it is not really empty
		$this->assertEquals (array ('.', '..', '.svn'), $r, 'Empty dir fails');
		
		$r = @scandir ('core/tests/scandir/hithisdontexists');
		$this->assertFalse ($r, 'Not existing dir fails');
		// In the PHP4 implementation an error is not triggered
		// All calls to scandir in the program should have the @ symbol to surpress errors
	}
}
?>