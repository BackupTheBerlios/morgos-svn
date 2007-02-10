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
 * File that take care of the base SQL wrapper
 *
 * @since 0.4
 * @author Nathan Samson
*/

class MockDBDriver {
	function getSQLCreator () {
		return 'DataSQLCreator';
	}
}

class DataSQLCreatorTest extends TestCase {
	function setUp () {
		$dbDriver = new MockDBDriver ();
		$table = null;
		$fields = array ();
		// INT types
		$fields[] = new DataFieldInt ('field1', $table, 1, false);
		$fields[] = new DataFieldInt ('field2', $table, 2);
		$fields[] = new DataFieldInt ('field3', $table, 3, false);
		$fields[] = new DataFieldInt ('field4', $table);
		$fields[] = new DataFieldInt ('field5', $table, 6);
		$fields[] = new DataFieldInt ('field6', $table, 8, false);
		
		// STRING types
		$fields[] = new DataFieldString ('stringfield1', $table, 10);
		$fields[] = new DataFieldString ('stringfield2', $table, 1);
		$fields[] = new DataFieldString ('stringfield3', $table);	
	
		// ENUM types
		$fields[] = new DataFieldEnum ('enum1', $table, array ('Y', 'N'));
		$fields[] = new DataFieldEnum ('enum2', $table, 
			array ('Yes \' sir', 'No, pa'));
		$this->_testTable = new DataTable ('SomeTable', $fields, $dbDriver); 
	}

	function testCreateDatabaseSQL () {
		$sqlfac = new DataSQLCreator ();
		$actual = $sqlfac->createTableSQL ($this->_testTable);
		$exp = "CREATE TABLE SomeTable (field1 INT(1) UNSIGNED NOT NULL,field2 INT(2) NOT NULL,field3 INT(3) UNSIGNED NOT NULL,field4 INT(4) NOT NULL,field5 INT(6) NOT NULL,field6 INT(8) UNSIGNED NOT NULL,stringfield1 varchar(10) NOT NULL,stringfield2 varchar(1) NOT NULL,stringfield3 varchar(255) NOT NULL,enum1 ENUM('Y','N') NOT NULL,enum2 ENUM('Yes \' sir','No, pa') NOT NULL )";
		$this->assertEquals ($exp, $actual);
	}
}
