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
/** \file core/tests/pagemanager.class.test.php
 * File that take care of the pagemanager
 *
 * @since 0.2
 * @author Nathan Samson
*/

class XMLSQLTest extends TestCase {
	var $_dbModule;

	function setUp () {
		$this->_dbModule = databaseLoadModule ('XML');
		$this->_dbModule->connect ('core/tests', 'nathan', 'nopass');
		$this->_dbModule->selectDatabase ('test');
	}
	
	function testSimpleSelect () {
		$sql = 'SELECT * FROM books';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$row1 = array ('Title'=>'Book 1', 'Author'=>'1', 'Rating'=>'6');
		$row2 = array ('Title'=>'Book 2', 'Author'=>'1', 'Rating'=>'5');
		$row3 = array ('Title'=>'Book 3', 'Author'=>'1', 'Rating'=>'4');
		$row4 = array ('Title'=>'Book 4', 'Author'=>'2', 'Rating'=>'7');
		$this->assertEquals (array ($row1, $row2, $row3, $row4), $allRows);
		$this->assertEquals (4, $this->_dbModule->numRows ($query));
	}
	
	function testSelectWithLimit () {
		$row1 = array ('Title'=>'Book 1', 'Author'=>'1', 'Rating'=>'6');
		$row2 = array ('Title'=>'Book 2', 'Author'=>'1', 'Rating'=>'5');
		$row3 = array ('Title'=>'Book 3', 'Author'=>'1', 'Rating'=>'4');
		$row4 = array ('Title'=>'Book 4', 'Author'=>'2', 'Rating'=>'7');
		$sql = 'SELECT * FROM books LIMIT 2';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$this->assertEquals (array ($row1, $row2), $allRows);
		$this->assertEquals (2, $this->_dbModule->numRows ($query));
		
		$sql = 'SELECT * FROM books LIMIT 1,2';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$this->assertEquals (array ($row2, $row3), $allRows);
		$this->assertEquals (2, $this->_dbModule->numRows ($query));
		
		$sql = 'SELECT * FROM books LIMIT 1,5';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$this->assertEquals (array ($row2, $row3, $row4), $allRows);
		$this->assertEquals (3, $this->_dbModule->numRows ($query));
		
		$sql = 'SELECT * FROM books LIMIT 4,5';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$this->assertEquals (array (), $allRows);
		$this->assertEquals (0, $this->_dbModule->numRows ($query));
	}
	
	function testSelectOnlySomeFields () {
		$sql = 'SELECT Title, Rating FROM books';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$row1 = array ('Title'=>'Book 1', 'Rating'=>'6');
		$row2 = array ('Title'=>'Book 2', 'Rating'=>'5');
		$row3 = array ('Title'=>'Book 3', 'Rating'=>'4');
		$row4 = array ('Title'=>'Book 4', 'Rating'=>'7');
		$this->assertEquals (array ($row1, $row2, $row3, $row4), $allRows);
		$this->assertEquals (4, $this->_dbModule->numRows ($query));
	}
	
	function testSelectWithOrder () {
		$this->fail ('Not Yet implemented');
	}
	
	function testSelectWithSimpleLiteralWhere () {
		$sql = 'SELECT * FROM books WHERE Title=\'Book 1\'';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$row1 = array ('Title'=>'Book 1', 'Author'=>'1', 'Rating'=>'6');
		$this->assertEquals (array ($row1), $allRows);
		$this->assertEquals (1, $this->_dbModule->numRows ($query));
	}
	
	function testSelectWithAdvanceWhere () {
		$sql = 'SELECT * FROM books WHERE Title=\'Book 1\' AND Author=\'1\' OR Rating=\'7\'';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$row1 = array ('Title'=>'Book 1', 'Author'=>'1', 'Rating'=>'6');
		$row4 = array ('Title'=>'Book 4', 'Author'=>'2', 'Rating'=>'7');
		$this->assertEquals (array ($row1, $row4), $allRows);
		$this->assertEquals (2, $this->_dbModule->numRows ($query));
	}
	
	function testSelectAdvanced () {
		$this->fail ('Not Yet implemented');
	}
	
	function testSelectWithCount () {
		$sql = 'SELECT COUNT(Title), Title, Rating FROM books';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$row1 = array ('COUNT(Title)'=>4, 'Title'=>'Book 1', 'Rating'=>'6');
		$row2 = array ('COUNT(Title)'=>4, 'Title'=>'Book 2', 'Rating'=>'5');
		$row3 = array ('COUNT(Title)'=>4, 'Title'=>'Book 3', 'Rating'=>'4');
		$row4 = array ('COUNT(Title)'=>4, 'Title'=>'Book 4', 'Rating'=>'7');
		$this->assertEquals (array ($row1, $row2, $row3, $row4), $allRows);
		$this->assertEquals (4, $this->_dbModule->numRows ($query));
	}
		
	function testSelectWithCountAndLimit () {
		$sql = 'SELECT COUNT(Title), Title, Rating FROM books LIMIT 2';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$row1 = array ('COUNT(Title)'=>2, 'Title'=>'Book 1', 'Rating'=>'6');
		$row2 = array ('COUNT(Title)'=>2, 'Title'=>'Book 2', 'Rating'=>'5');
		$this->assertEquals (array ($row1, $row2), $allRows);
		$this->assertEquals (2, $this->_dbModule->numRows ($query));
	}
	
	function testInsertSimple () {
		$sql = 'INSERT INTO books (Title, Author, Rating) VALUES (\'Book 5\', \'1\', \'3\')';
		$query = $this->_dbModule->query ($sql);
		$sql = 'INSERT INTO books (Title, Author, Rating) VALUES(\'Book 6\', \'1\', \'2\')';
		$query = $this->_dbModule->query ($sql);
		$this->assertFalse (isError ($query), 'Unexpected error');
		$sql = 'SELECT * FROM books';
		$query = $this->_dbModule->query ($sql);
		$allRows = array ();
		while ($row = $this->_dbModule->fetchArray ($query)) {
			$allRows[] = $row;
		}
		$row1 = array ('Title'=>'Book 1', 'Author'=>'1', 'Rating'=>'6');
		$row2 = array ('Title'=>'Book 2', 'Author'=>'1', 'Rating'=>'5');
		$row3 = array ('Title'=>'Book 3', 'Author'=>'1', 'Rating'=>'4');
		$row4 = array ('Title'=>'Book 4', 'Author'=>'2', 'Rating'=>'7');
		$row5 = array ('Title'=>'Book 5', 'Author'=>'1', 'Rating'=>'3');
		$row6 = array ('Title'=>'Book 6', 'Author'=>'1', 'Rating'=>'2');

		$this->assertEquals (array ($row1, $row2, $row3, $row4, $row5, $row6), $allRows);
		$this->assertEquals (6, $this->_dbModule->numRows ($query));
	}
	
	function testInsertWithDefaultValues () {
		$this->fail ('Not Yet implemented');
	}
	
	function testInsertUniqueKey () {
		$this->fail ('Not Yet implemented');
	}
	
	function testInsertAutoIncrement () {
		$this->fail ('Not Yet implemented');
	}
	
	function testInsertNotNull () {
		$this->fail ('Not Yet implemented');
	}
	
	function testDropTable () {
		$sql = 'DROP TABLE books';
		$query = $this->_dbModule->query ($sql);
		$this->assertEquals (4, $this->_dbModule->numRows ($query));
		$query = $this->_dbModule->query ('SELECT * FROM books');
		$this->assertEquals ("ERROR_XMLSQL_TABLE_NOT_FOUND books", $query);
	}
	
	function testAlterTable () {
		$this->fail ('Not Yet implemented');
	}
	
	function testDropRowWithWhere () {
		$this->fail ('Not Yet implemented');
	}
	
	function testCreateTable () {
		$sql = 'CREATE TABLE editors (ID int(11) auto_increment, name varchar (255) NOT NULL, isNice ENUM(\'Y\', \'N\'), number int(5), UNIQUE KEY (name), PRIMARY KEY (ID) )';
		$query = $this->_dbModule->query ($sql);
		$this->assertEquals (0, $this->_dbModule->numRows ($query), 'Wrong data CREATE returned');
		$query = $this->_dbModule->query ('SELECT * FROM editors');
		$this->assertFalse (isError ($query), 'Unexpecter error');
		$this->assertEquals (0, $this->_dbModule->numRows ($query), 'Wrong data SELECT returned');
	}
	
	function testUpdate () {
		$this->fail ('Not Yet implemented');
	}
	
	function testUpdateWithWhere () {
		$this->fail ('Not Yet implemented');
	}
}
?>
