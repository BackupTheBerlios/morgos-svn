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
/**
 * This is the installerBase class.
 *
 * @since 0.2
 * @author Nathan Samson
*/
class installerBasePlugin extends plugin {
	
	function installerBasePlugin ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Installer plugin';
		$this->_ID = '{e29500ef-6cf9-45b9-9319-2c6937a1c1f8}';
		$this->_minMorgOSVersion = '0.2';
		$this->_maxMorgOSVersion = '0.2';
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);	

		/*$this->_pluginAPI->getActionManager ()->addAction (
			new action ('installerAskLanguage', 'GET',  
				array (&$this, 'askLanguage'), array (), array ()));*/
		
		
		$aM = &$this->_pluginAPI->getActionManager ();		
		$aM->addAction (
			new action ('installerShowLicense', 'GET',  
				array (&$this, 'showLicense'), array (), array (new LocaleInput ('language'))));
				
		$aM->addAction (
			new action ('installerAgreeLicense', 'GET',  
				array (&$this, 'agreeLicense'), array (new BoolInput ('agreed')), array ()));
				
		$aM->addAction (
			new action ('installerShowRequirements', 'GET',  
				array (&$this, 'showRequirements'), array (new BoolInput ('agreed')), array ()));
			
		$aM->addAction (
			new action ('askConfig', 'GET',  
				array (&$this, 'askConfig'), array (new BoolInput ('canRun')), array ()));
				
		$aM->addAction (
			new action ('installerInstall', 'POST',  
				array (&$this, 'installConfigAndDatabase'), array (
						new StringInput ('siteName'), 
						new StringInput ('databaseModule'), 
						new StringInput ('databaseHost'), new StringInput ('databaseUser'), 
						new StringInput ('databasePassword'), new StringInput ('databaseName'), 
						new StringInput ('databasePrefix'), 
						new StringInput ('adminLogin'), new PasswordNewInput ('adminPassword'), 
						new EmailInput ('adminMail')), array ()));	
		//echo $this->_pluginAPI->_actionManager;
	}
	
	function askLanguage () {
	}
	
	function showLicense ($language) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->display ('installer/license.tpl');
	}	
	
	function agreeLicense ($agreed) {
		$aM = &$this->_pluginAPI->getActionManager ();		
		if ($agreed == 'Y') {
			$a = $aM->executeAction ('installerShowRequirements', array ('agreed'=>'Y'));
		} else {
			$a = $aM->executeAction ('installerShowLicense', array ('language'=>'en'));
		}
	}
	
	function showRequirements ($agreed) {
		$t = &$this->_pluginAPI->getI18NManager ();	
		if ($agreed == 'Y') {
			$sm = &$this->_pluginAPI->getSmarty ();
			$sm->assign ('canRun', true);
			if (version_compare (PHP_VERSION, '4.3', '>=')) {
				$sm->assign ('phpError', false);
				$sm->assign ('phpMessage', $t->translate ('You are running PHP version %1 which is new enough to run MorgOS.', array (PHP_VERSION)));
			} else {
				$sm->assign ('phpMessage', $t->translate ('You are running PHP version %1 which is too old to run MorgOS, please upgrade to at least %2 .', array (PHP_VERSION, '4.3')));
				$sm->assign ('phpError', true);
				$sm->assign ('canRun', false);
			}
			
			$aMods = databaseGetAllModules (true);
			if (count ($aMods) > 1) {
				$sm->assign ('dbMError', false);
				$sm->assign ('dbMMessage', $t->translate ('You have at least installed 1 database module.'));
			} else {
				$sm->assign ('canRun', false);
				$sm->assign ('dbMError', true);
				$s = '';
				foreach (databaseGetAllModules (false) as $a=> $mod) {
					if (! empty ($s)) {
						$s .= ', ';
					}
					$s .= $a;
				}
				$sm->assign ('dbMMessage', $t->translate ('You need to install one supported database module. Supported databases by MorgOS are: %1.', array ($s)));	
			}
			
			if (file_exists ('skins_c')) {
				if (is_writable ('skins_c')) {
					$sm->assign ('dirsError', false);
					$sm->assign ('dirsMessage', $t->translate ('All required dirs are ok.'));
				} else {
					$sm->assign ('canRun', false);
					$sm->assign ('dirsError', true);
					$sm->assign ('dirsMessage', $t->translate ('You need to make the dir "skins_c" wirtable for PHP.'));
				}
			} else {
				$a = @mkdir ('skins_c');
				if ($a == false) {
					$sm->assign ('canRun', false);
					$sm->assign ('dirsError', true);
					$sm->assign ('dirsMessage', $t->translate ('You need to have a dir skins_c that is writable by PHP.'));
				} else {
					if (is_writable ('skins_c')) {
						$sm->assign ('dirsError', false);
						$sm->assign ('dirsMessage', $t->translate ('All required dirs are ok.'));
					} else {
						$sm->assign ('canRun', false);
						$sm->assign ('dirsError', true);
						$sm->assign ('dirsMessage', $t->translate ('You need to make the dir "skins_c" wirtable for PHP.'));
					}
				}
			}		
			
			$sm->display ('installer/testreqs.tpl');
		} else {
			$aM = &$this->_pluginAPI->getActionManager ();
			$aM->executeAction ('installerShowLicense', array ('language'=>'en'));
		}
	}
	
	function askConfig ($canRun) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->assign ('dbModules', databaseGetAllModules (true));
		$sm->display ('installer/configure.tpl');
	}
	
	function testDatabaseConfig () {
	}
	
	function installConfigAndDatabase ($siteName, 
			$databaseModule, $databaseHost, $databaseUser, $databasePassword, $databaseName, $databasePrefix, 
			$adminLogin, $adminPassword, $adminMail) {
		$dbModule = databaseLoadModule ($databaseModule);
		if (! isError ($dbModule)) {
			$dbModule->connect ($databaseHost, $databaseUser, $databasePassword);
			$dbModule->selectDatabase ($databaseName);
			$dbModule->setPrefix ($databasePrefix);
			$dbModule->queryFile ('interface/installer/base-plugin/sqlCode.sql');
			
			//var_dump ($dbModule);
			//die ('End');
			
			$userM = new userManager ($dbModule);
			$admin = $userM->newUser ();
			$a = $admin->initFromArray (array ('login'=>$adminLogin, 'password'=>md5($adminPassword), 'email'=>$adminMail));
			$userM->addUserToDatabase ($admin);
			
			$group = $userM->newGroup ();
			$group->initFromArray (array ('genericName'=>'administrator', 'genericDescription'=>'The admin users'));
			$userM->addGroupToDatabase ($group);
			$group->assignPermission ('edit_admin', true);
			$admin->addToGroup ($group);
			
			$group = $userM->newGroup ();
			$group->initFromArray (array ('genericName'=>'normaluser', 'genericDescription'=>'All users'));
			$userM->addGroupToDatabase ($group);
			$group->assignPermission ('edit_admin', false);
			$admin->addToGroup ($group);
			
			$group = $userM->newGroup ();
			$group->initFromArray (array ('genericName'=>'anonymous', 'genericDescription'=>'Not logged in'));
			$userM->addGroupToDatabase ($group);
			$group->assignPermission ('edit_admin', false);
			
			$pageM = new pageManager ($dbModule);
			$site = $pageM->newPage ();
			$admin = $pageM->newPage ();
			$home = $pageM->newPage ();
			$ahome = $pageM->newPage ();
			$pman = $pageM->newPage ();
			$pluman = $pageM->newPage ();
			$regform = $pageM->newPage ();
			$adminLogout = $pageM->newPage ();
			$adminUser = $pageM->newPage ();
			
			$site->initFromArray (array ('name'=>'site', 'parentPageID'=>0, 'placeInMenu'=>0));
			$admin->initFromArray (array ('name'=>'admin', 'parentPageID'=>0, 'placeInMenu'=>0));
			
			$pageM->addPageToDatabase ($site);
			$pageM->addPageToDatabase ($admin);
			
			$home->initFromArray (array ('name'=>'MorgOS_Home', 'parentPageID'=>$site->getID ()));
			$ahome->initFromArray (array ('name'=>'MorgOS_Admin_Home', 'parentPageID'=>$admin->getID ()));
			$pman->initFromArray (array ('name'=>'MorgOS_Admin_PageManager', 'parentPageID'=>$admin->getID (), 'action'=>'adminPageManager'));
			$pluman->initFromArray (array ('name'=>'MorgOS_Admin_PluginManager', 'parentPageID'=>$admin->getID (), 'action'=>'adminPluginManager'));
			$regform->initFromArray (array ('name'=>'MorgOS_RegisterForm',  'parentPageID'=>$site->getID (), 'action'=>'userRegisterForm', 'placeInMenu'=>0));
			$adminLogout->initFromArray (array ('name'=>'MorgOS_Admin_Logout',  'parentPageID'=>$admin->getID (), 'action'=>'adminLogout'));
			$adminUser->initFromArray (array ('name'=>'MorgOS_Admin_UserManager',  'parentPageID'=>$admin->getID (), 'action'=>'adminUserManager'));

			$pageM->addPageToDatabase ($home);
			$pageM->addPageToDatabase ($ahome);
			$pageM->addPageToDatabase ($pman);
			$pageM->addPageToDatabase ($adminUser);
			$pageM->addPageToDatabase ($pluman);
			$pageM->addPageToDatabase ($regform);
			$pageM->addPageToDatabase ($adminLogout);
			
			$tHome = $pageM->newTranslatedPage ();
			$tAHome = $pageM->newTranslatedPage ();
			$tPMan = $pageM->newTranslatedPage ();
			$tRegForm = $pageM->newTranslatedPage ();
			$tPlugMan = $pageM->newTranslatedPage ();
			$tALogout = $pageM->newTranslatedPage ();
			$tAdminUser = $pageM->newTranslatedPage ();

			$tHome->initFromArray (array ('languageCode'=>'en_UK', 'translatedTitle'=>'Home', 'translatedContent'=>'This is the homepage.'));
			$tAHome->initFromArray (array ('languageCode'=>'en_UK', 'translatedTitle'=>'Admin', 'translatedContent'=>'This is the admin. Here you can configure the site, add/remove and edit pages, or ban users.'));
			$tPMan->initFromArray (array ('languageCode'=>'en_UK', 'translatedTitle'=>'Page Manager', 'translatedContent'=>'Edit pages here.'));
			$tRegForm->initFromArray (array ('languageCode'=>'en_UK', 'translatedTitle'=>'Registration', 'translatedContent'=>'Give up all your user details in order to registrate to this site.'));
			$tPlugMan->initFromArray (array ('languageCode'=>'en_UK', 'translatedTitle'=>'Plugin Manager', 'translatedContent'=>'Enable/disable plugins.'));
			$tALogout->initFromArray (array ('languageCode'=>'en_UK', 'translatedTitle'=>'Logout', 'translatedContent'=>'Logout'));
			$tAdminUser->initFromArray (array ('languageCode'=>'en_UK', 'translatedTitle'=>'User manager', 'translatedContent'=>'Manage users here, remove/add them from administrators list.'));
			
			$home->addTranslation ($tHome);
			$ahome->addTranslation ($tAHome);
			$pman->addTranslation ($tPMan);
			$regform->addTranslation ($tRegForm);
			$pluman->addTranslation ($tPlugMan);
			$adminLogout->addTranslation ($tALogout);
			$adminUser->addTranslation ($tAdminUser);
					
			
			$configContents = '<?php'.PHP_NL.PHP_NL;
			
			$configContents .= '$configItems[\'/databases/host\']=\''.$databaseHost.'\';'.PHP_NL;
			$configContents .= '$configItems[\'/databases/password\']=\''.$databasePassword.'\';'.PHP_NL;
			$configContents .= '$configItems[\'/databases/user\']=\''.$databaseUser.'\';'.PHP_NL;
			$configContents .= '$configItems[\'/databases/database\']=\''.$databaseName.'\';'.PHP_NL;
			$configContents .= '$configItems[\'/databases/table_prefix\']=\''.$databasePrefix.'\';'.PHP_NL.PHP_NL;
			
			$configContents .= '$configItems[\'/site/title\']=\''.$siteName.'\';'.PHP_NL;
			$configContents .= '?>';
			$c = fopen ('config.php', 'w');
			if ($c !== false) {
				fwrite ($c, $configContents);
				fclose ($c);
				header ('Location: index.php');
			} else {
				echo $configContents;
			}
		} else {
			var_dump ($dbModule);
		}	
	}
}
?>