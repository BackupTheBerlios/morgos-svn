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
	var $pM;

	function setPageManager () {
		if (! $this->pM) {
			global $dbModule;
			$this->pM = new PageManager ($dbModule);
		}
	}

	function setUp () {
		$this->setPageManager ();
	}
	
	function testInstall () {
		$this->assertFalse ($this->pM->isInstalled ());
		$this->pM->installAllTables ();
		$this->assertTrue ($this->pM->isInstalled ());
	}	
	
	function testNewPage () {
		$page = $this->pM->newPage ();
		$this->assertEquals ('page', strtolower (get_class ($page)));
	}
	
	function testGetAdminAndSite () {
		$site = $this->pM->getSitePage ();
		$this->assertEquals ('page', strtolower (get_class ($site)));
		$this->assertTrue ($site->isRootPage ());		
		
		$admin = $this->pM->getAdminPage ();
		$this->assertEquals ('page', strtolower (get_class ($admin)));
		$this->assertTrue ($admin->isRootPage ()); 
		$this->assertTrue ($admin->isAdminPage ());
	}
	
	function testAddNewPage () {
		$newPage = $this->pM->newPage ();
		$sitePage = $this->pM->getSitePage ();
		$newPage->initFromArray (array(
				'name'=>'Home',
				'parent_page_id'=>$sitePage->getID (),
				'place_in_menu'=>MORGOS_MENU_FIRST
			));
		$this->pM->addPageToDatabase ($newPage);
		
		$pluginPage = $this->pM->newPage ();
		$pluginPage->initFromArray (array(
				'name'=>'PluginPage',
				'parent_page_id'=>$newPage->getID (),
				'plugin_id'=>'3535a30b-f026-443a-a22b-e41b9ca05cc5'
			));
		$this->pM->addPageToDatabase ($pluginPage);
		
		$actionPage = $this->pM->newPage ();
		$actionPage->initFromArray (array(
				'name'=>'ActionPage',
				'parent_page_id'=>$newPage->getID (),
				'action'=>'SOME_ACTION'
			));
		$this->pM->addPageToDatabase ($actionPage);
		
		$newPage = $this->pM->newPage ();
		$adminPage = $this->pM->getAdminPage ();
		$newPage->initFromArray (array(
				'name'=>'AdminHome',
				'parent_page_id'=>$adminPage->getID (),
				'place_in_menu'=>MORGOS_MENU_FIRST
			));
		$this->pM->addPageToDatabase ($newPage);
		
		$newPage = $this->pM->newPage ();
		$newPage->initFromArray (array(
				'name'=>'2NDPage',
				'parent_page_id'=>$sitePage->getID ()
			));
		$this->pM->addPageToDatabase ($newPage);
		
		$newPage = $this->pM->newPage ();
		$newPage->initFromArray (array(
				'name'=>'NonVisiblePage',
				'parent_page_id'=>$sitePage->getID (),
				'place_in_menu'=>MORGOS_MENU_INVISIBLE
			));
		$this->pM->addPageToDatabase ($newPage);
		
		$newPage = $this->pM->newPage ();
		$newPage->initFromArray (array(
				'name'=>'4THPage',
				'parent_page_id'=>$sitePage->getID ()
			));
		$this->pM->addPageToDatabase ($newPage);
		$this->assertEquals (2, $newPage->getPlaceInMenu ());	
		
		$newPage = $this->pM->newPage ();
		$newPage->initFromArray (array(
				'name'=>'3THPage',
				'parent_page_id'=>$sitePage->getID (),
				'place_in_menu'=>2
			));
		$this->pM->addPageToDatabase ($newPage);
		$this->assertEquals (2, $newPage->getPlaceInMenu ());
		$page4th = $this->pM->newPage ();
		$page4th->initFromName ('4THPage');
		$this->assertEquals (3, $page4th->getPlaceInMenu ());
		
		$newPage = $this->pM->newPage ();
		$newPage->initFromArray (array(
				'name'=>'LastPage',
				'parent_page_id'=>$sitePage->getID (),
				'place_in_menu'=>MORGOS_MENU_LAST
			));
		$this->pM->addPageToDatabase ($newPage);		
		
		$newPage = $this->pM->newPage ();
		$newPage->initFromArray (array(
				'name'=>'Home',
				'parent_page_id'=>$sitePage->getID ()
			));
		$r = $this->pM->addPageToDatabase ($newPage);
		$this->assertTrue ($r->is ('PAGE_EXISTS_ALREADY'));
	}
	
	function testGetMenu () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$page2nd = $this->pM->newPage ();
		$page2nd->initFromName ('2NDPage');
		$page3th = $this->pM->newPage ();
		$page3th->initFromName ('3THPage');
		$page4th = $this->pM->newPage ();
		$page4th->initFromName ('4THPage');
		$last = $this->pM->newPage ();
		$last->initFromName ('LastPage');
		
		
		$this->assertEquals (array ($home, $page2nd, $page3th, $page4th, $last), 
			$this->pM->getMenu ($this->pM->getSitePage ()));
			
		$a = $this->pM->movePageUp ($page2nd);
		$this->assertFalse (isError ($a));
		$page2nd->initFromName ('2NDPage');
		$this->assertEquals (array ($home, $page2nd, $page3th, $page4th, $last), 
			$this->pM->getMenu ($this->pM->getSitePage ()));
		
		$r = $this->pM->movePageDown ($page2nd);
		$this->assertFalse (isError ($r));
		$page2nd->initFromName ('2NDPage');
		$page3th->initFromName ('3THPage');	
		$this->assertEquals (array ($home, $page3th, $page2nd, $page4th, $last), 
			$this->pM->getMenu ($this->pM->getSitePage ()));

	
		$r = $this->pM->movePageUp ($page2nd);
		$this->assertFalse (isError ($r));
		$page2nd->initFromName ('2NDPage');
		$page3th->initFromName ('3THPage');
		$this->assertEquals (array ($home, $page2nd, $page3th, $page4th, $last), 
			$this->pM->getMenu ($this->pM->getSitePage ()));
			
		$r = $this->pM->movePageDown ($page4th);
		$this->assertEquals (array ($home, $page2nd, $page3th, $page4th, $last), 
			$this->pM->getMenu ($this->pM->getSitePage ()));
	}
	
	function testRemovePageFromDatabase () {
		$page3th = $this->pM->newPage ();
		$page3th->initFromName ('3THPage');
		
		$this->pM->removePageFromDatabase ($page3th);
		$this->assertFalse ($page3th->isInDatabase ());
		$page4th = $this->pM->newPage ();
		$page4th->initFromName ('4THPage');
		$this->assertEquals (2, $page4th->getPlaceInMenu ());
		$last = $this->pM->newPage ();
		$last->initFromName ('LastPage');
		$this->assertEquals (MORGOS_MENU_LAST, $last->getPlaceInMenu ());
		
		$r = $this->pM->removePageFromDatabase ($page3th);
		$this->assertTrue ($r->is ('PAGE_NOT_FOUND'));
	}
	
	function testIsAdminPage () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$adminHome = $this->pM->newPage ();
		$adminHome->initFromName ('AdminHome');
		
		$this->assertFalse ($home->isAdminPage ());
		$this->assertTrue ($adminHome->isAdminPage ());
	}
	
	function testGetAction () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$this->assertEquals ('', $home->getAction ());
		
		$actionPage = $this->pM->newPage ();
		$actionPage->initFromName ('ActionPage');
		$this->assertEquals ('SOME_ACTION', 
			$actionPage->getAction ());
	}	
	
	function testGetLink () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$adminHome = $this->pM->newPage ();
		$adminHome->initFromName ('AdminHome');
		$actionPage = $this->pM->newPage ();
		$actionPage->initFromName ('ActionPage');
		
		$this->assertEquals ('index.php?action=viewPage&pageID='.$home->getID (),
			$home->getLink ());
			
		$this->assertEquals ('index.php?action=admin&pageID='.$adminHome->getID (),
			$adminHome->getLink ());
			
		$this->assertEquals ('index.php?action=SOME_ACTION', $actionPage->getLink ());
	}
	
	function testGetPluginID () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$this->assertEquals ('', $home->getPluginID ());
		
		$pluginPage = $this->pM->newPage ();
		$pluginPage->initFromName ('PluginPage');
		$this->assertEquals ('3535a30b-f026-443a-a22b-e41b9ca05cc5', 
			$pluginPage->getPluginID ());
	}
	
	function testNewTranslatedPage () {
		$trPage = $this->pM->newTranslatedPage ();
		$this->assertEquals ('translatedpage', strtolower (get_class ($trPage)));
	}
	
	function testAddTranslationToPage () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$trHome = $this->pM->newTranslatedPage ();
		$trHome->initFromArray (array(
			'translated_title'=>'Welcome Home',
			'translated_nav_title'=>'Home',
			'translated_content'=>'This is the homepage',
			//'page_id'=>$home->getPageID (),
			'language_code'=>'English'
			));
		$home->addTranslation ($trHome);
		
		$trHome = $this->pM->newTranslatedPage ();
		$trHome->initFromArray (array(
			'translated_title'=>'Thuis',
			'translated_content'=>'Dit is het huis',
			//'page_id'=>$home->getPageID (),
			'language_code'=>'Nederlands'
			));
		$home->addTranslation ($trHome);
	}
	
	function testTranslationExists () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$this->assertTrue ($home->translationExists ('English'));
		$this->assertTrue ($home->translationExists ('Nederlands'));
		$this->assertFalse ($home->translationExists ('Other'));
	}
	
	function testGetTranslation () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$trNed = $home->getTranslation ('Nederlands');
		$this->assertEquals ('Thuis', $trNed->getTitle ());
		$this->assertEquals ('Thuis', $trNed->getNavTitle ());
		$this->assertEquals ('Dit is het huis', $trNed->getContent ());
		
		$trEng = $home->getTranslation ('English');
		$this->assertEquals ('Welcome Home', $trEng->getTitle ());
		$this->assertEquals ('Home', $trEng->getNavTitle ());
		$this->assertEquals ('This is the homepage', $trEng->getContent ());
		$this->assertEquals ($home, $trEng->getPage ());
		$this->assertEquals ($home->getID (), $trEng->getPageID ());
		
		$r = $home->getTranslation ('Other');
		$this->assertTrue ($r->is ('PAGE_TRANSLATION_DOESNT_EXIST'));
	}
	
	function testGetAllTranslations () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$trNed = $home->getTranslation ('Nederlands');
		$trEng = $home->getTranslation ('English');		
		
		$this->assertEquals (array ($trEng, $trNed), 
			$home->getAllTranslations ());
			
		$this->assertEquals (array ('English', 'Nederlands'), 
			$home->getAllTranslationCodes ());
	}
	
	function testRemoveTranslation () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$nedTrans = $home->getTranslation ('Nederlands');
		
		$home->removeTranslation ($nedTrans);
		$this->assertFalse ($home->translationExists ('Nederlands'));
		
		$r = $home->removeTranslation ($nedTrans);		
		$this->assertTrue ($r->is ('PAGE_TRANSLATION_DOESNT_EXIST'));
	}
	
	function testRemoveTranslationsWithPage () {
		$home = $this->pM->newPage ();
		$home->initFromName ('Home');
		$pID = $home->getID ();
		$this->pM->removePageFromDatabase ($home);
		global $dbModule;		
		$prefix = $dbModule->getPrefix ();
		$sql = "SELECT language_code FROM {$prefix}translatedPages WHERE page_id=$pID";
		$q = $dbModule->query ($sql);
		$this->assertEquals (0, $dbModule->numRows ($q));
	}
}
?>