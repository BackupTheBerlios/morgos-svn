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
/** \file sqlwrapper.class.test.php
 * File that take care of the sqlwrapper tester.
 *
 * @since 0.3
 * @author Nathan Samson
*/

include_once ('core/sqlwrapper.class.php');

class BookManager extends DBTableManager {
	
	function BookManager (&$db) {
		parent::DBTableManager ($db, 'books','Book', 'authors','Author');
	}
}

class Book extends DBTableObject {
	function Book (&$db, $creator) {
		$title = new dbField ('title', DB_TYPE_STRING, 255);
		$author = new dbField ('author', DB_TYPE_INT);
		$backText = new dbField ('backtext', DB_TYPE_TEXT);
		$year = new dbField ('year', DB_TYPE_INT);
		
		$basicFields = array ($title, $author, $backText, $year);		
		$joins = array (new multipleToOneJoinField ('author', 'Authors', 'authorid', $author)); 		
		
		parent::DBTableObject ($db, $basicFields, 'books', 'bookid', $creator, array (), $joins);
	}
}

class Author extends DBTableObject {
	function Author (&$db, $creator) {
		$firstName = new dbField ('firstname', DB_TYPE_STRING, 70);
		$lastName = new dbField ('lastname', DB_TYPE_STRING, 70);
		$ID = new dbField ('authorid', DB_TYPE_INT, 11);
		$basicFields = array ($ID, $firstName, $lastName);		
		$joins = array (new OneToMultipleJoinField ('books', 'Books', 'authorid', $ID)); 		
		
		parent::DBTableObject ($db, $basicFields, 'authors', 'authorid', $creator, array (), $joins);
	}

}

class SQLWrapperTest extends TestCase {

	function setUp () {
		global $dbModule;
		$this->testManager = new BookManager ($dbModule);
	}
	
	function testIsInstalledNo () {
		$this->assertFalse ($this->testManager->isInstalled ());
	}
	
	function testInstall () {
		$e = $this->testManager->installAllTables ();
		$this->assertFalse (isError ($e), 'Unexpected error: '.$e);
		$this->assertTrue ($this->testManager->isInstalled (), 'Returned wrong value.');
	}

	function testCreateObject () {
		$book = $this->testManager->createObject ('books');
		$this->assertEquals ('Book', get_class ($book), 'Wrong object returned');
		
		$false = $this->testManager->createObject ('someNonExistingOne');
		$this->assertTrue ($false->is ('DONT_MANAGE_THIS_TABLE'));	 
	}
	
	function testGetExtraOptionsForTable () {
		$this->assertEquals (array (), $this->testManager->getExtraFieldsForTable ('books'));
	}
	
	function testGetExtraJoinsForTable () {
		$this->assertEquals (array (), $this->testManager->getExtraJoinFieldsForTable ('books'));
	}
	
	function testAddExtraOptionOrJoinForTable () {
		$newField = new dbField ('editorid', DB_TYPE_INT);
		$newJoin = new multipleToOneJoinField ('editor', 'Editors', 'editorid', $newField);	
			
		$this->testManager->addExtraJoinFieldForTable ('books', $newJoin);			
		$this->assertEquals (array ($newJoin), 
			$this->testManager->getExtraJoinFieldsForTable ('books'));
			
		$this->testManager->addExtraFieldForTable ('books', $newField);
								
		$this->assertEquals (array ($newField), 
			$this->testManager->getExtraFieldsForTable ('books'));
	}
	
	function testAddRowToTable () {
		$newAuthor = $this->testManager->createObject ('authors');
		$newAuthor->initFromArray (array ('firstname'=>'Nathan', 'lastname'=>'Samson'));
		$a = $this->testManager->addRowToTable ($newAuthor, 'authors');
		$this->assertFalse ($newAuthor->getID () == -1, 'Author Not stored');		
		
		$newBook = $this->testManager->createObject ('books');
		$newBook->initFromArray (array ('title'=>'By NS', 'authorid'=>$newAuthor->getID (), 'backtext'=>'This is the backtext.'));
		$this->testManager->addRowToTable ($newBook, 'books');
		$this->assertFalse ($newBook->getID () == -1, 'Book Not stored'); 
		
		$a = $this->testManager->addRowToTable ($newAuthor, 'NOTEXISTING');
		$this->assertTrue ($a->is ('DONT_MANAGE_THIS_TABLE'));		
	}	
	
	function testGetAllRowsFromTable () {
		$author1 = $this->testManager->createObject ('authors');
		$author1->initFromDatabaseID (1); // is this always 1?
		$authorsExp = array ($author1);
		$authors = $this->testManager->getAllRowsFromTable ('authors');
		$this->assertEquals ($authorsExp, $authors);
		
		$a = $this->testManager->getAllRowsFromTable ('NOTEXITSING');
		$this->assertTrue ($a->is ('DONT_MANAGE_THIS_TABLE'));	
	}
}