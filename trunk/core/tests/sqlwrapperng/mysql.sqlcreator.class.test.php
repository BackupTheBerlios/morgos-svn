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

class DataMySQLCompatLayerTest extends DataSQLCreatorTest {
	function testCreateDatabaseSQL () {
		$sqlfac = new DataMySQLCompatLayer ();
		$actual = $sqlfac->createTableSQL ($this->_testTable);
		$exp = "CREATE TABLE SomeTable (field1 TINYINT UNSIGNED NOT NULL,field2 SMALLINT NOT NULL,field3 MEDIUMINT UNSIGNED NOT NULL,field4 INT NOT NULL,field5 INT(6) NOT NULL,field6 BIGINT UNSIGNED NOT NULL,stringfield1 varchar(10) NOT NULL,stringfield2 varchar(1) NOT NULL,stringfield3 varchar(255) NOT NULL,enum1 ENUM('Y','N') NOT NULL,enum2 ENUM('Yes \' sir','No, pa') NOT NULL )";
		$this->assertEquals ($exp, $actual);
	}
}