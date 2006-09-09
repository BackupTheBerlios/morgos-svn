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
		$this->assertEquals ($r, new Error ('PAGE_GENERICNAME_DOESNT_EXISTS', 'notExistingPage'));
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
		$optionName = new dbField ('optionName', 'varchar(255)');
		$oldOptions = $this->pageManager->getAllOptionsForPage ();
		$optionName2 = $optionName;
		$optionName2->canBeNull = true;
		$oldOptions['optionName'] = $optionName2;
		
		$r = $this->pageManager->addOptionToPage ($optionName);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals ($oldOptions, $this->pageManager->getAllOptionsForPage (), 'Wrong options 1');		
		$r = $this->pageManager->addOptionToPage ($optionName);
		$this->assertEquals (new Error ('PAGEMANAGER_OPTION_FORPAGE_EXISTS', 'optionName'), $r, 'Wrong error');
		$this->assertEquals ($oldOptions, $this->pageManager->getAllOptionsForPage (), 'Wrong options 2');
		
		$r = $this->pageManager->removeOptionForPage ('optionName');
		$this->assertFalse (isError ($r), 'Unexpected error 2');
		$this->assertEquals (array (), $this->pageManager->getAllOptionsForPage (), 'Wrong options 3');
		$r = $this->pageManager->removeOptionForPage ('optionName');
		$this->assertEquals (new Error ('PAGEMANAGER_OPTION_FORPAGE_DOESNT_EXISTS', 'optionName'), $r, 'Wrong error 2');
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
		$a = $appendPage->initFromArray ($array);
		$r = $this->pageManager->addPageToDatabase ($appendPage);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$siteMenu = $this->pageManager->getMenu ($root);	
		$this->assertEquals (5, $appendPage->getPlaceInMenu ());
		$this->assertEquals (array ($home, $news, $development, $packages, $appendPage), $siteMenu, 'Wronge menu order');
		
		$r = $this->pageManager->addPageToDatabase ($appendPage);
		$this->assertEquals (new Error ('PAGEMANAGER_PAGE_EXISTS', 'lastPage'), $r, "Wrong error returned");
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
		$this->assertEquals (3, $packages->getPlaceInMenu ());	
					
		$r = $this->pageManager->removePageFromDatabase ($lastPage);
		$this->assertFalse (isError ($r), 'Unexpected error 2');
		$siteMenu = $this->pageManager->getMenu ($root);
		$this->assertEquals (array ($home, $news, $packages), $siteMenu, 'Wronge menu order');
		
		$r = $this->pageManager->removePageFromDatabase ($lastPage);
		$this->assertEquals (new Error ('PAGEMANAGER_PAGE_DOESNT_EXISTS', 'lastPage'), $r, 'Wrong error');
		$siteMenu = $this->pageManager->getMenu ($root);
		$this->assertEquals (array ($home, $news, $packages), $siteMenu, 'Wronge menu order');
	}
	
	function testOptionsForTranslatedPage () {
		$translator = new dbField ('translator', 'int(11)');
		$translator2 = cloneob ($translator);
		$translator2->canBeNull = true;
		$r = $this->pageManager->addOptionToTranslatedPage ($translator);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array ('translator'=>$translator2), $this->pageManager->getAllOptionsForTranslatedPage (), 'Wrong options 1');
		$r = $this->pageManager->addOptionToTranslatedPage ($translator);
		$this->assertEquals (new Error ('PAGEMANAGER_OPTION_FORTRANSLATEDPAGE_EXISTS', 'translator'), $r, 'Wrong error');
		$this->assertEquals (array ('translator'=>$translator2), $this->pageManager->getAllOptionsForTranslatedPage (), 'Wrong options 2');
		
		$r = $this->pageManager->removeOptionForTranslatedPage ('translator');
		$this->assertFalse (isError ($r), 'Unexpected error 2');
		$this->assertEquals (array (), $this->pageManager->getAllOptionsForTranslatedPage (), 'Wrong options 3');
		$r = $this->pageManager->removeOptionForTranslatedPage ('translator');
		$this->assertEquals (new Error ('PAGEMANAGER_OPTION_FORTRANSLATEDPAGE_DOESNT_EXISTS', 'translator'), $r, 'Wrong error 2');		
	}
	
	function testGetTranslation () {
		$page = $this->pageManager->newPage ();
		$page->initFromGenericName ('TranslatedPage');
		$translatedPageNL_NL = $page->getTranslation ('NL-NL');
		$this->assertFalse (isError ($translatedPageNL_NL), 'Unexpected NL_NL error');
		$this->assertEquals ('This is the dutch (Netherlands) translation. (NL-NL)', $translatedPageNL_NL->getContent (), 'Normal translation failed');
		
		$translatedPageNL_BE = $page->getTranslation ('NL-BE');
		$this->assertFalse (isError ($translatedPageNL_BE), 'Unexpected NL_BE error');
		$this->assertEquals ('This is the dutch (generic) translation. (NL)', $translatedPageNL_BE->getContent (), 'Main language translation failed');
		
		$translatedPageFR_BE = $page->getTranslation ('FR-BE');
		$this->assertFalse (isError ($translatedPageFR_BE), 'Unexpected - hehem - FR_BE error');
		$this->assertEquals ('This is the french (french) translation. (FR-FR)', $translatedPageFR_BE->getContent (), 'Select other dialect failed (NYI)');
	}
	
	function testGetAllTranslations () {
		$page = $this->pageManager->newPage ();
		$page->initFromGenericName ('TranslatedPage');
		$this->assertEquals (array ('FR-FR', 'NL', 'NL-NL'), $page->getAllTranslations ());
	}
	
	function testAddTranslation () {
		$page = $this->pageManager->newPage ();
		$page->initFromGenericName ('TranslatedPage');
		$translationNL_BE = $this->pageManager->newTranslatedPage ();
		$a['translatedName'] = 'NL_BE';
		$a['translatedContent'] = 'NL_BE translation';
		$a['languageCode'] = 'NL-BE'; 
		$r = $translationNL_BE->initFromArray ($a);
		$this->assertFalse (isError ($r), 'Unexpected init error');
		$r = $page->addTranslation ($translationNL_BE);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array ('FR-FR', 'NL', 'NL-BE', 'NL-NL'), $page->getAllTranslations ());
		
		$r = $page->addTranslation ($translationNL_BE);
		$this->assertEquals (new Error ('PAGE_TRANSLATION_EXISTS', 'NL-BE'), $r);
	}
	
	function testRemoveTranslation () {
		$page = $this->pageManager->newPage ();
		$page->initFromGenericName ('TranslatedPage');
		$translationNL_BE = $this->pageManager->newTranslatedPage ();
		$translationNL_BE->initFromDatabasePageIDandLanguageCode ($page->getID (), 'NL-BE');
		
		$r = $page->removeTranslation ($translationNL_BE);
		$this->assertFalse (isError ($r), 'Unexpected error');
		$this->assertEquals (array ('FR-FR', 'NL', 'NL-NL'), $page->getAllTranslations (), 'Not deleted');
		
		$r = $page->removeTranslation ($translationNL_BE);
		$this->assertEquals (new Error ('PAGE_TRANSLATION_DOESNT_EXISTS', 'NL-BE'), $r, 'Wrong error returned');
	}
}
?>
