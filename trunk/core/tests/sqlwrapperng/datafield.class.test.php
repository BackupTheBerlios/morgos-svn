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
/**
 * File that take care of testing the new generation (ng) sqlwrapper classes.
 *
 * @since 0.4
 * @author Nathan Samson
*/

class DataFieldTest extends TestCase {
	function testInt () {
		$a = null;
		$int = new DataFieldInt ('name', $a);
		$this->assertTrue ($int->isValidValue (12345));
		$this->assertTrue ($int->isValidValue (-12345));
		$this->assertTrue ($int->isValidValue (0));
		// can not try out -2147483648
		// this is not an int according to PHP
		$this->assertTrue ($int->isValidValue (-2147483648));
		$this->assertTrue ($int->isValidValue (2147483647));
		
		$this->assertFalse ($int->isValidValue (-2147483649));
		$this->assertFalse ($int->isValidValue (2147483648));
		
		$this->assertFalse ($int->isValidValue (false));
		$this->assertFalse ($int->isValidValue (null));
		$this->assertFalse ($int->isValidValue (true));
		$this->assertFalse ($int->isValidValue (''));
		$this->assertFalse ($int->isValidValue ('01234'));
		$this->assertFalse ($int->isValidValue ('1234'));
		
		$int = new DataFieldInt ('name', $a, 1);
		$this->assertTrue ($int->isValidValue (127));
		$this->assertTrue ($int->isValidValue (-128));
		
		$this->assertFalse ($int->isValidValue (128));
		$this->assertFalse ($int->isValidValue (-129));
		
		$int = new DataFieldInt ('name', $a, 1, false);
		$this->assertTrue ($int->isValidValue (127));
		$this->assertTrue ($int->isValidValue (255));
		$this->assertTrue ($int->isValidValue (0));
		
		$this->assertFalse ($int->isValidValue (-128));
		$this->assertFalse ($int->isValidValue (256));
		
		$int = new DataFieldInt ('name', $a, 8);
		$this->assertTrue ($int->isValidValue (9223372036854775807));
		// shouldn't be true, but float is to inprecise
		// difference of 1025
		$this->assertTrue ($int->isValidValue (9223372036854776832));		
		
		$this->assertEquals (DATATYPE_INT, $int->getDataType ());
	}
	
	function testString () {
		$a = null;
		$int = new DataFieldString ('name', $a);
		$this->assertTrue ($int->isValidValue (0));
		$this->assertTrue ($int->isValidValue ('test'));
		$this->assertTrue ($int->isValidValue ('azerty^ä, bcde{}#'));
		$int = new DataFieldString ('name', $a, 10);
		$this->assertTrue ($int->isValidValue ('123456789'));
		$this->assertFalse ($int->isValidValue ('12345678910'));
		$int = new DataFieldString ('name', $a, -1); 
		// negative values are not allowed, and set to default value
		$this->assertTrue ($int->isValidValue ('12345678910'));
		$this->assertFalse ($int->isValidValue ('azertyuiopazertyuiopazertyuiop
			azertyuiopazertyuiopazertyuiopazertyuiopazertyuiop
			azertyuiopazertyuiopazertyuiopazertyuiopazertyuiop
			azertyuiopazertyuiopazertyuiopazertyuiopazertyuiop
			azertyuiopazertyuiopazertyuiopazertyuiopazertyuiop
			azertyuiopazertyuiopazertyuiopazertyuiopazertyuiop
			azertyuiopazertyuiopazertyuiopazertyuiopazertyuiop
			azertyuiopazertyuiopazertyuiopazertyuiopazertyuiop
		'));
		$this->assertEquals (DATATYPE_STRING, $int->getDataType ());
	}
	
	function testEnum () {
		$a = null;
		$enum = new DataFieldEnum ('name', $a, array ('a', 'b', 'C'));
		$this->assertTrue ($enum->isValidValue ('a'));
		$this->assertTrue ($enum->isValidValue ('b'));
		$this->assertTrue ($enum->isValidValue ('C'));
		
		$this->assertFalse ($enum->isValidValue ('A'));
		$this->assertFalse ($enum->isValidValue ('B'));
		$this->assertFalse ($enum->isValidValue ('c'));
		$this->assertEquals (DATATYPE_ENUM, $enum->getDataType ());
	}
}