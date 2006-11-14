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
	
	function testPageInitFromName () {
		$page = $this->pageManager->newPage ();
		$r = $page->initFromName ('notExistingPage');
		$this->assertEquals ($r, new Error ('PAGE_NAME_DOESNT_EXISTS', 'notExistingPage'));
	}	
	
	function testGetMenu () {
		$root = $this->pageManager->newPage ();
		$root->initFromName ('site');
		
		$home = $this->pageManager->newPage ();
		$home->initFromName ('Home');
		
		$news = $this->pageManager->newPage ();
		$news->initFromName ('News');
		
		$packages = $this->pageManager->newPage ();
		$packages->initFromName ('Packages');
		
		$siteMenu = $this->pageManager->getMenu ($root);
		$this->assertEquals (array ($home, $news, $packages), $siteMenu);
	}
	
	function testAddPageToDatabase () {
		$root = $this->pageManager->newPage ();
		$root->initFromName ('site');	
	
		$development = $this->pageManager->newPage ();
		$array = array ();
		$array['name'] = 'Development';
		$array['parentPageID'] = $root->getID ();
		$array['placeInMenu'] = 3; //before packages, after news
		$a = $development->initFromArray ($array);
		$r = $this->pageManager->addPageToDatabase ($development);
		
		$this->assertFalse (isError ($r), 'Unexpected error');		
		
		$home = $this->pageManager->newPage ();
		$home->initFromName ('Home');
		$news = $this->pageManager->newPage ();
		$news->initFromName ('News');
		$packages = $this->pageManager->newPage ();
		$packages->initFromName ('Packages');	
		
		$siteMenu = $this->pageManager->getMenu ($root);
		/*var_dump ($siteMenu == array ($home, $news, $development, $packages));
		
		foreach (array ($home, $news, $development, $packages) as $k=>$i) {
			if ($i != $siteMenu[$k]) {
				var_dump ($i);
				
				var_dump ($siteMenu[$k]);
			} else {
				echo 'OK';
			}
		}	*/		
			
		$this->assertEquals (array ($home, $news, $development, $packages), $siteMenu, 
			'Wronge menu order');
		$appendPage = $this->pageManager->newPage ();
		$array = array ();
		$array['name'] = 'lastPage';
		$array['parentPageID'] = $root->getID ();
		$a = $appendPage->initFromArray ($array);
		$r = $this->pageManager->addPageToDatabase ($appendPage);
		//$this->assertFalse (isError ($r), 'Unexpected error');
		$siteMenu = $this->pageManager->getMenu ($root);	
		$this->assertEquals (5, $appendPage->getPlaceInMenu ());
		$this->assertEquals (array ($home, $news, $development, $packages, $appendPage), 
			$siteMenu, 'Wronge menu order');
		
		$r = $this->pageManager->addPageToDatabase ($appendPage);
		$this->assertEquals (new Error ('PAGEMANAGER_PAGE_EXISTS', 'lastPage'), $r, 
			"Wrong error returned");
	}
	
	function testRemovePageFromDatabase () {
		$root = $this->pageManager->newPage ();
		$root->initFromName ('site');	

		$development = $this->pageManager->newPage ();
		$development->initFromName ('Development');
		
		$r = $this->pageManager->removePageFromDatabase ($development);
		$this->assertFalse (isError ($r), 'Unexpected error 1');
		
		
		$siteMenu = $this->pageManager->getMenu ($root);
		$home = $this->pageManager->newPage ();
		$home->initFromName ('Home');		
		$news = $this->pageManager->newPage ();
		$news->initFromName ('News');
		$packages = $this->pageManager->newPage ();
		$packages->initFromName ('Packages');
		$lastPage = $this->pageManager->newPage ();	
		$lastPage->initFromName ('lastPage');		
		
		$this->assertEquals (array ($home, $news, $packages, $lastPage), $siteMenu, 
			'Wronge menu order');
		$packages = $this->pageManager->newPage ();
		$packages->initFromName ('Packages');
		$this->assertEquals (3, $packages->getPlaceInMenu ());	
					
		$r = $this->pageManager->removePageFromDatabase ($lastPage);
		$this->assertFalse (isError ($r), 'Unexpected error 2');
		$siteMenu = $this->pageManager->getMenu ($root);
		$this->assertEquals (array ($home, $news, $packages), $siteMenu, 'Wronge menu order');
		
		$r = $this->pageManager->removePageFromDatabase ($lastPage);
		$this->assertEquals (new Error ('PAGEMANAGER_PAGE_DOESNT_EXISTS', 'lastPage'), $r, 
			'Wrong error');
		$siteMenu = $this->pageManager->getMenu ($root);
		$this->assertEquals (array ($home, $news, $packages), $siteMenu, 'Wronge menu order');
	}
	
	function testGetTranslation () {
		$page = $this->pageManager->newPage ();
		$page->initFromName ('TranslatedPage');
		$translatedPageNL_NL = $page->getTranslation ('NL-NL');
		$this->assertFalse (isError ($translatedPageNL_NL), 'Unexpected NL_NL error');
		$this->assertEquals ('This is the dutch (Netherlands) translation. (NL-NL)', 
			$translatedPageNL_NL->getContent (), 'Normal translation failed');
		
		$translatedPageNL_BE = $page->getTranslation ('NL-BE');
		$this->assertFalse (isError ($translatedPageNL_BE), 
			'Unexpected NL_BE error (should return NL language)');
		
		$this->assertEquals ('This is the dutch (generic) translation. (NL)', 
			$translatedPageNL_BE->getContent (), 'Main language translation failed');
	}
	
	function testGetAllTranslationCodes () {
		$page = $this->pageManager->newPage ();
		$page->initFromName ('TranslatedPage');
		$this->assertEquals (array ('FR-FR', 'NL', 'NL-NL'), $page->getAllTranslationCodes ());
	}
	
	function testAddTranslation () {
		$page = $this->pageManager->newPage ();
		$page->initFromName ('TranslatedPage');
		$translationNL_BE = $this->pageManager->newTranslatedPage ();
		$a['translatedTitle'] = 'NL_BE';
		$a['translatedContent'] = 'NL_BE translation';
		$a['languageCode'] = 'NL-BE'; 
		$r = $translationNL_BE->initFromArray ($a);
		$this->assertFalse (isError ($r), 'Unexpected init error');
		$r = $page->addTranslation ($translationNL_BE);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array ('FR-FR', 'NL', 'NL-BE', 'NL-NL'), 
			$page->getAllTranslationCodes ());
		
		$r = $page->addTranslation ($translationNL_BE);
		$this->assertEquals (new Error ('PAGE_TRANSLATION_EXISTS', 'NL-BE'), $r);
	}
	
	function testRemoveTranslation () {
		$page = $this->pageManager->newPage ();
		$page->initFromName ('TranslatedPage');
		$translationNL_BE = $this->pageManager->newTranslatedPage ();
		$translationNL_BE->initFromDatabasePageIDandLanguageCode ($page->getID (), 'NL-BE');
		
		$r = $page->removeTranslation ($translationNL_BE);
		$this->assertFalse (isError ($r), 'Unexpected error');
		
		$this->assertEquals (array ('FR-FR', 'NL', 'NL-NL'), $page->getAllTranslationCodes (), 
			'Not deleted');
		
		$r = $page->removeTranslation ($translationNL_BE);
		$this->assertEquals (new Error ('PAGE_TRANSLATION_DOESNT_EXISTS', 'NL-BE'), $r, 
			'Wrong error returned');
	}
	
	function testGetParentPage () {
		$page = $this->pageManager->newPage ();
		$page->initFromName ('Home');
		
		$ppage = $this->pageManager->newPage ();
		$ppage->initFromName ('Site');
		
		$this->assertEquals ($ppage, $page->getParentPage ());
		$this->assertEquals (null, $ppage->getParentPage ());
	}
	
	function testIsPageAdmin () {
		$page = $this->pageManager->newPage ();
		$page->initFromName ('Home');
		$this->assertFalse ($page->isAdminPage ());
		
		$page = $this->pageManager->newPage ();
		$page->initFromName ('Admin');
		$this->assertTrue ($page->isAdminPage ());
		
		$page = $this->pageManager->newPage ();
		$page->initFromName ('Adminpage');
		$this->assertTrue ($page->isAdminPage ());
	}
	
	function testGetAction () {
		$page = $this->pageManager->newPage ();
		$page->initFromName ('News');
		$this->assertEquals ('newsViewLatestItems', $page->getAction ());
	}
	
	function testMovePageUp () {
		$root = $this->pageManager->newPage ();
		$root->initFromName ('site');	
		$homepage = $this->pageManager->newPage ();
		$homepage->initFromName ('Home');
		$newspage = $this->pageManager->newPage ();
		$newspage->initFromName ('News');
		$packpage = $this->pageManager->newPage ();
		$packpage->initFromName ('Packages');
		
		$this->pageManager->movePageUp ($packpage->getID ());
		$newspage->setField ('placeInMenu', 3);
		$packpage->setField ('placeInMenu', 2);
		$this->assertEquals (array ($homepage, $packpage, $newspage), 
			$this->pageManager->getMenu ($root));
			
		$this->pageManager->movePageUp ($homepage->getID ());
		$this->assertEquals (array ($homepage, $packpage, $newspage), 
			$this->pageManager->getMenu ($root));
	}
	
	function testMovePageDown () {
		$root = $this->pageManager->newPage ();
		$root->initFromName ('site');	
		$homepage = $this->pageManager->newPage ();
		$homepage->initFromName ('Home');
		$newspage = $this->pageManager->newPage ();
		$newspage->initFromName ('News');
		$packpage = $this->pageManager->newPage ();
		$packpage->initFromName ('Packages');
		
		$this->pageManager->movePageDown ($newspage->getID ());
		$this->assertEquals (array ($homepage, $packpage, $newspage), 
			$this->pageManager->getMenu ($root));
		
		$this->pageManager->movePageDown ($homepage->getID ());
		$homepage->setField ('placeInMenu', 2);
		$packpage->setField ('placeInMenu', 1);
		$this->assertEquals (array ($packpage, $homepage, $newspage), 
			$this->pageManager->getMenu ($root));
	}	
	
}
?>
