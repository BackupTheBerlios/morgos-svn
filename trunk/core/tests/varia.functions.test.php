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
/** \file varia.functions.test.php
 * File that take care of testing various functions laying arround.
 *
 * @since 0.2
 * @author Nathan Samson
*/

include_once ('core/varia.functions.php');
class variaTest extends TestCase {

	function testIsError () {		
		$this->assertTrue (isError (new Error ('TEST')));
		$this->assertFalse (isError (null));
		$this->assertFalse (isError (false));
	}
	
	function testErrorIsError () {
		$a = new Error ('SOME_ERROR', 1, 2, 3);
		$b = new Error ('SOME_ERROR', 4, 5, 6);
		$c = new Error ('SOME_ERROR', 4, 5, 6);
		$d = new Error ('SOME_OTHER_ERROR');
		$this->assertTrue ($a->is ('SOME_ERROR'), 'String comp fails');
		$this->assertTrue ($a->is ($b), 'Object comp fails (diff params)');
		$this->assertTrue ($b->is ($c), 'Object comp fails (same params)');
		$this->assertTrue ($b == $c, 'Object == fails (same)');
		
		$this->assertFalse ($a == $c, 'Object == fails (different)');
		$this->assertFalse ($a->is ($d), 'Object comp fails (diff error)');
	}
}
?>