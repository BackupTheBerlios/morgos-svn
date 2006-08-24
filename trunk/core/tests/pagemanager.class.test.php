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

include_once ('core/page/pagemanager.class.php');
class pageManagerTest extends TestCase {
	function setUp () {
		global $dbModule;
		$this->pageManager = new pageManager ($dbModule);
	}
	
	function testPageExists () {
		$this->assertTrue ($this->pageManager->pageExists ('Home'), 'Expected true');
		$this->assertFalse ($this->pageManager->pageExists ('NotExistingPage'), 'Expected false');
	}
	
	function testPageInitFromGenericName () {
		$page = $this->pageManager->newPage ();
		$r = $page->initFromGenericName ('notExistingPage');
		$this->assertEquals ($r, "ERROR_PAGE_GENERICNAME_DOESNT_EXISTS notExistingPage");
	}	
	
	function testGetMenu () {
		$root = $this->pageManager->newPage ();
		$root->initFromGenericName ('site');
		
		$home = $this->pageManager->newPage ();
		$home->initFromGenericName ('Home');
		
		$news = $this->pageManager->newPage ();
		$news->initFromGenericName ('News');
		
		$packages = $this->pageManager->newPage ();
		$packages->initFromGenericName ('Packages');
		
		$siteMenu = $this->pageManager->getMenu ($root);
		$this->assertEquals (array ($home, $news, $packages), $siteMenu);
	}
	
	function testOptionsForPage () {
		$r = $this->pageManager->addOptionToPage ('optionName', 'varchar (20)');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array ('optionName'=>null), $this->pageManager->getAllOptionsForPage (), 'Wrong options 1');
		$r = $this->pageManager->addOptionToPage ('optionName', 'varchar(20)');
		$this->assertEquals ("ERROR_PAGEMANAGER_OPTION_FORPAGE_EXISTS optionName", $r, 'Wrong error');
		$this->assertEquals (array ('optionName'=>null), $this->pageManager->getAllOptionsForPage (), 'Wrong options 2');
		
		$r = $this->pageManager->removeOptionForPage ('optionName');
		$this->assertFalse (isError ($r), 'Unexpected error 2');
		$this->assertEquals (array (), $this->pageManager->getAllOptionsForPage (), 'Wrong options 3');
		$r = $this->pageManager->removeOptionForPage ('optionName');
		$this->assertEquals ("ERROR_PAGEMANAGER_OPTION_FORPAGE_DOESNT_EXISTS optionName", $r, 'Wrong error 2');
	}
	
	function testAddPageToDatabase () {
		$root = $this->pageManager->newPage ();
		$root->initFromGenericName ('site');	
	
		$development = $this->pageManager->newPage ();
		$array = array ();
		$array['genericName'] = 'Development';
		$array['genericContent'] = 'This is the development page.';
		$array['parentPageID'] = $root->getID ();
		$array['placeInMenu'] = 3; //before packages, after news
		$development->initFromArray ($array);
		$r = $this->pageManager->addPageToDatabase ($development);
		$this->assertFalse (isError ($r), 'Unexpected error');		
		
		$home = $this->pageManager->newPage ();
		$home->initFromGenericName ('Home');
		$news = $this->pageManager->newPage ();
		$news->initFromGenericName ('News');
		$packages = $this->pageManager->newPage ();
		$packages->initFromGenericName ('Packages');	
		
		$siteMenu = $this->pageManager->getMenu ($root);	
		$this->assertEquals (array ($home, $news, $development, $packages), $siteMenu, 'Wronge menu order');
		
		$appendPage = $this->pageManager->newPage ();
		$array = array ();
		$array['genericName'] = 'lastPage';
		$array['genericContent'] = 'This is the last page in the menu.';
		$array['parentPageID'] = $root->getID ();
		$array['placeInMenu'] = 0;
		$a = $appendPage->initFromArray ($array);
		$r = $this->pageManager->addPageToDatabase ($appendPage);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$siteMenu = $this->pageManager->getMenu ($root);	
		$this->assertEquals ('5', $appendPage->getPlaceInMenu ());
		$this->assertEquals (array ($home, $news, $development, $packages, $appendPage), $siteMenu, 'Wronge menu order');
		
		$r = $this->pageManager->addPageToDatabase ($appendPage);
		$this->assertEquals ("ERROR_PAGEMANAGER_PAGE_EXISTS lastPage", $r, "Wrong error returned");
	}
	
	function testRemovePageFromDatabase () {
		$root = $this->pageManager->newPage ();
		$root->initFromGenericName ('site');	

		$development = $this->pageManager->newPage ();
		$development->initFromGenericName ('Development');
		
		$r = $this->pageManager->removePageFromDatabase ($development);
		$this->assertFalse (isError ($r), 'Unexpected error 1');
		
		
		$siteMenu = $this->pageManager->getMenu ($root);
		$home = $this->pageManager->newPage ();
		$home->initFromGenericName ('Home');		
		$news = $this->pageManager->newPage ();
		$news->initFromGenericName ('News');
		$packages = $this->pageManager->newPage ();
		$packages->initFromGenericName ('Packages');
		$lastPage = $this->pageManager->newPage ();	
		$lastPage->initFromGenericName ('lastPage');		
		
		$this->assertEquals (array ($home, $news, $packages, $lastPage), $siteMenu, 'Wronge menu order');
		$packages = $this->pageManager->newPage ();
		$packages->initFromGenericName ('Packages');
		$this->assertEquals ('3', $packages->getPlaceInMenu ());	
					
		$r = $this->pageManager->removePageFromDatabase ($lastPage);
		$this->assertFalse (isError ($r), 'Unexpected error 2');
		$siteMenu = $this->pageManager->getMenu ($root);
		$this->assertEquals (array ($home, $news, $packages), $siteMenu, 'Wronge menu order');
		
		$r = $this->pageManager->removePageFromDatabase ($lastPage);
		$this->assertEquals ("ERROR_PAGEMANAGER_PAGE_DOESNT_EXISTS lastPage", $r, 'Wrong error');
		$siteMenu = $this->pageManager->getMenu ($root);
		$this->assertEquals (array ($home, $news, $packages), $siteMenu, 'Wronge menu order');
	}
	
	function testOptionsForTranslatedPage () {
		$r = $this->pageManager->addOptionToTranslatedPage ('translator', 'int (11)');
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array ('translator'=>null), $this->pageManager->getAllOptionsForTranslatedPage (), 'Wrong options 1');
		$r = $this->pageManager->addOptionToTranslatedPage ('translator', 'varchar(20)');
		$this->assertEquals ("ERROR_PAGEMANAGER_OPTION_FORTRANSLATEDPAGE_EXISTS translator", $r, 'Wrong error');
		$this->assertEquals (array ('translator'=>null), $this->pageManager->getAllOptionsForTranslatedPage (), 'Wrong options 2');
		
		$r = $this->pageManager->removeOptionForTranslatedPage ('translator');
		$this->assertFalse (isError ($r), 'Unexpected error 2');
		$this->assertEquals (array (), $this->pageManager->getAllOptionsForTranslatedPage (), 'Wrong options 3');
		$r = $this->pageManager->removeOptionForTranslatedPage ('translator');
		$this->assertEquals ("ERROR_PAGEMANAGER_OPTION_FORTRANSLATEDPAGE_DOESNT_EXISTS translator", $r, 'Wrong error 2');		
	}
	
}


?>
