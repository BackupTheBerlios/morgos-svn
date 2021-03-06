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
// MorgOS defined vars
include ($this->skinPath . 'skin.php');

$contentLanguage = $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING);
$this->vars['VAR_PAGE_CONTENT'] = $this->pages->getPageContent ($this->module, $contentLanguage);
$page = $this->pages->getPageInfo ($this->module, $contentLanguage);
$this->vars['VAR_PAGE_TITLE'] = $page['name'];
$this->vars['VAR_NAVIGATION'] = $this->getNavigator ();
$this->vars['VAR_SITE_TITLE'] = $this->config->getConfigItem ('/general/sitename', TYPE_STRING);
$this->vars['TINYMCE'] = 'skins/default/tinymce/jscripts/tiny_mce/tiny_mce.js';

$this->vars['VAR_TO_REGISTER_USER'] = './index.php?module=register';
$this->vars['SIDEBAR'] = $this->getSidebarHTML ();
$this->vars['SUBBAR'] = $this->getSubbarHTML ();
$this->vars['MORGOS_COPYRIGHT'] = $this->i10nMan->translate ('Powered By MorgOS &copy; 2006');

$this->vars['TO_POSTNEW_NEWSITEMFORM'] = 'index.php?module=formpostnews';

if (substr ($this->module, 0, 5) == 'admin') {
	$this->vars['VAR_ADMIN_NAVIGATION'] = $this->getAdminNavigator ();
	$this->vars['VAR_ADMIN_LINK_INDEX'] = 'admin.php';
	$this->vars['VAR_ADMIN_LINK_GENERAL'] = 'admin.php?module=general';
	if ($this->module == 'admin/databases') {
		$DBTypeOptions = NULL;
		$DBManager = new genericDatabase;
		foreach ($DBManager->getAllSupportedDatabases () as $key => $supported) {
			if ($this->config->getConfigItem ('/database/type', TYPE_STRING) == $key) {
				$DBTypeOptions .= ' ADMIN_DATABASE_TYPE_OPTION_SELECTED ('. $key . ')';
			} else {
				$DBTypeOptions .= ' ADMIN_DATABASE_TYPE_OPTION ('. $key . ')';
			}
		}
		$this->vars['VAR_ADMIN_DATABASE_FORM_DATABASE_TYPE_OPTIONS'] = $DBTypeOptions;
		$this->vars['VAR_ADMIN_DATABASE_FORM_ACTION'] = './admin.php?module=databasesave';
		$this->vars['VAR_ADMIN_DATABASE_FORM_NAME_HOST'] = '/database/host';
		$this->vars['VAR_ADMIN_DATABASE_FORM_NAME_DATABASE_TYPE'] = '/database/type';
		$this->vars['VAR_ADMIN_DATABASE_FORM_NAME_DBNAME'] = '/database/name';
		$this->vars['VAR_ADMIN_DATABASE_FORM_NAME_USER'] = '/database/user';
		$this->vars['VAR_ADMIN_DATABASE_FORM_NAME_PASSWORD'] = '/database/password';
		$this->vars['VAR_ADMIN_DATABASE_FORM_VALUE_HOST'] = $this->config->getConfigItem ('/database/host', TYPE_STRING);
		$this->vars['VAR_ADMIN_DATABASE_FORM_VALUE_DBNAME'] = $this->config->getConfigItem ('/database/name', TYPE_STRING);
		$this->vars['VAR_ADMIN_DATABASE_FORM_VALUE_USER'] = $this->config->getConfigItem ('/database/user', TYPE_STRING);
		$this->vars['VAR_ADMIN_DATABASE_FORM_VALUE_PASSWORD'] = $this->config->getConfigItem ('/database/password', TYPE_STRING);
	}
	
	if ($this->module == 'admin/general') {
		$this->vars['VAR_ADMIN_GENERAL_FORM_ACTION'] = 'admin.php?module=generalsave';
		$this->vars['VAR_ADMIN_GENERAL_FORM_NAME_SITE_NAME'] = '/general/sitename';
		$this->vars['VAR_ADMIN_GENERAL_FORM_VALUE_SITE_NAME'] = $this->config->getConfigItem ('/general/sitename', TYPE_STRING);
	}
	
	if ($this->module == 'admin/pages') {
		$this->vars['VAR_ADMIN_FORM_MODULES_ACTION'] = 'admin.php?module=pagessave';
		$this->vars['VAR_ADMIN_FORM_MODULES_SUBMIT'] = 'submit';
		$this->vars['VAR_ADMIN_FORM_MODULES_NEW_MODULE_NAME'] = 'NEW_MODULE_NAME';
		$this->vars['VAR_ADMIN_FORM_MODULES_NEW_MODULE_NEEDAUTHORIZE'] = 'NEW_MODULE_NEEDAUTHORIZE';
		$this->vars['VAR_ADMIN_MODULES_OPEN'] = $this->parse ($skin['variable']['admin_modules_open']);
		$this->vars['VAR_ADMIN_MODULES_CLOSE'] = $this->parse ($skin['variable']['admin_modules_close']);
		$this->vars['VAR_ADMIN_MODULES'] = $this->getModuleAdminHTML ();
		$this->vars['VAR_ADMIN_LINK_DATABASE'] = 'admin.php?module=database';
		$this->vars['VAR_ADMIN_LINK_PAGES'] = 'admin.php?module=pages';
	
		$this->vars['VAR_SKIN_LICENSE'] = $this->parse ($skin['variable']['license']);
		$this->vars['VAR_ADMIN_DATABASE_FORM_SUBMIT'] = 'submit';
		$this->vars['VAR_ADMIN_FORM_ADDPAGE_ACTION'] = 'admin.php?module=addpagesave';
		$this->vars['VAR_ADMIN_FORM_ADDPAGE_SUBMIT'] = 'submit';
		$this->vars['VAR_ADMIN_FORM_ADDPAGE_LANGUAGE'] = 'language';
		$this->vars['VAR_ADMIN_FORM_ADDPAGE_CONTENT'] = 'content';
		$this->vars['VAR_ADMIN_FORM_ADDPAGE_MODULE'] = 'module';
		$this->vars['VAR_ADMIN_FORM_ADDPAGE_NAME'] = 'name';
	}
	
	global $editPageModule, $editPageLanguage;
	if (isset ($editPageModule)) {
		$this->vars['VAR_ADMIN_FORM_EDIT_PAGE_CURLANGUAGE'] = $editPageLanguage;
		$this->vars['VAR_ADMIN_FORM_EDITPAGE_SUBMIT'] = 'EDIT_PAGE_SAVE' . $editPageModule;
		$page = $this->pages->getPageInfo ($editPageModule, $editPageLanguage);
		$this->vars['VAR_ADMIN_FORM_EDIT_PAGE_CURPAGENAME'] = $page['name'];
		$this->vars['VAR_ADMIN_FORM_EDIT_PAGE_CURCONTENT'] = $page['content']; 
		$this->vars['VAR_ADMIN_FORM_EDIT_PAGE_LANGUAGE'] = 'language';
		$this->vars['VAR_ADMIN_FORM_EDIT_PAGE_CONTENT'] = 'newcontent';
		$this->vars['VAR_ADMIN_FORM_EDIT_PAGE_LANGUAGE'] = 'language';
		$this->vars['VAR_ADMIN_FORM_EDITPAGE_ACTION'] = './admin.php?module=pagessave';
		$this->vars['VAR_ADMIN_FORM_EDIT_PAGE_PAGENAME'] = 'newname';
	}
	
	
	global  $addToModule;
	if (! isset ($addToModule)) {
		$this->vars['VAR_ADMIN_FORM_ADDPAGE_VALUE_MODULE'] = $addToModule;
	}
	
	global $saveOutput;
	if (! isset ($saveOutput)) {
		$this->vars['VAR_SAVE_CONFIG_MANUALLY'] = $saveOutput;
	}
	
	$this->vars['VAR_ADMIN_SAVECONFIG_FORM_ACTION'] = './admin';
	$this->vars['VAR_ADMIN_SAVECONFIG_FORM_SUBMIT'] = 'submit';
	if ($this->module == 'admin/users') {
		$this->vars['VAR_ADMIN_USERS_ADMIN'] = $this->getUserAdminHTML ();
	}
	
	if ($this->module == 'admin/extensions') {
		$this->vars['VAR_ADMIN_EXTENSIONS_ADMIN'] = $this->getExtensionAdminHTML ();
		$this->vars['OPEN_EXTENSIONS_ADMIN'] = $this->parse ($skin['variable']['open_extensions_admin']);
		$this->vars['CLOSE_EXTENSIONS_ADMIN'] = $this->parse ($skin['variable']['close_extensions_admin']);
		$this->vars['VAR_EXTENSIONS_ADMIN_METHOD'] = 'post';
		$this->vars['VAR_SAVE_EXTENSIONS_SUBMIT'] = 'submit';
		$this->vars['VAR_EXTENSIONS_ADMIN_ACTION'] = './admin.php?module=saveextensions';
	}
} else {
	if ($this->user->isLoggedIn ()) {
		$this->vars['VAR_USER_PLACE'] = '';
		$this->vars['LINK_POST_COMMENT'] = ' LINK_POST_COMMENT_LOGGEDIN';
	} else {
		$userPlace = '&VAR_LOGIN_FORM;';
		$this->vars['VAR_LOGIN_FORM_ACTION'] = './index.php?module=login';
		$this->vars['VAR_LOGIN_FORM_METHOD'] = 'post';
		$this->vars['VAR_LOGIN_FORM_LOGINNAME_NAME'] = 'loginname';
		$this->vars['VAR_LOGIN_FORM_LOGINNAME_VALUE'] = $this->i10nMan->translate ('Loginname');
		$this->vars['VAR_LOGIN_FORM_PASSWORD_NAME'] = 'password';
		$this->vars['VAR_LOGIN_FORM_SUBMIT_NAME'] = 'submit';
		$this->vars['VAR_USER_PLACE'] = $this->parse ($userPlace);
		$this->vars['LINK_POST_COMMENT'] = ' LINK_POST_COMMENT_NOTLOGGEDIN';
	}
	
	if ($this->module == 'register') {
		$this->vars['VAR_REGISTER_ACTION'] = './index.php?module=registeruser';
		$this->vars['VAR_REGISTER_METHOD'] = 'post';
		$this->vars['VAR_REGISTER_NAME_NAME'] = 'account-name';
		$this->vars['VAR_REGISTER_EMAIL_NAME'] = 'account-email';
		$this->vars['VAR_REGISTER_PASSWORD1_NAME'] = 'account-password';
		$this->vars['VAR_REGISTER_PASSWORD2_NAME'] = 'account-password2';
		$this->vars['VAR_REGISTER_SUBMIT_NAME'] = 'submit';
	}
	
	if ($this->module == 'usersettings') {
		$this->vars['VAR_USERSETTINGSFORM_ACTION'] = './index.php?module=saveusersettings';
		$this->vars['VAR_USERSETTINGSFORM_METHOD'] = 'post';
		$this->vars['VAR_USERSETTINGSFORM_EMAIL_NAME'] = 'account-email';
		$curUser = $this->user->getUser ();
		$this->vars['VAR_USERSETTINGSFORM_EMAIL_VALUE'] = $curUser['email'];
		$this->vars['VAR_USERSETTINGSFORM_PASSWORD1_NAME'] = 'account-password1';
		$this->vars['VAR_USERSETTINGSFORM_PASSWORD2_NAME'] = 'account-password2';
		$this->vars['VAR_USERSETTINGSFORM_SUBMIT'] = 'submit';
		$this->vars['VAR_THEME_OPTION_NAME'] = 'skin';
		$this->vars['VAR_LANGUAGE_OPTION_NAME'] = 'language';
		$this->vars['VAR_CONTENTLANGUAGE_OPTION_NAME'] = 'contentlanguage';
		$this->vars['VAR_OPEN_LANGUAGE_OPTION'] = $this->parse ($skin['variable']['var_open_language_option']);
		$this->vars['VAR_CLOSE_LANGUAGE_OPTION'] = $this->parse ($skin['variable']['var_close_language_option']);
		$this->vars['VAR_OPEN_CONTENTLANGUAGE_OPTION'] = $this->parse ($skin['variable']['var_open_contentlanguage_option']);
		$this->vars['VAR_CLOSE_CONTENTLANGUAGE_OPTION'] = $this->parse ($skin['variable']['var_close_contentlanguage_option']);
		$this->vars['VAR_OPEN_THEME_OPTION'] = $this->parse ($skin['variable']['var_open_theme_option']);
		$this->vars['VAR_CLOSE_THEME_OPTION'] = $this->parse ($skin['variable']['var_close_theme_option']);
	
		$languageOption = ' &VAR_OPEN_LANGUAGE_OPTION;';
		foreach ($this->i10nMan->getAllSupportedLanguages () as $language) {
			if ($language == $this->config->getConfigItem ('/userinterface/language', TYPE_STRING)) {
				$languageOption .= ' LANGUAGE_OPTION_SELECTED (' . $language . ')';
			} else {
				$languageOption .= ' LANGUAGE_OPTION (' . $language . ')';
			}
		}
		$languageOption .= ' &VAR_CLOSE_LANGUAGE_OPTION;';
		$this->vars['VAR_LANGUAGE_OPTION'] = $this->parse ($languageOption);
	
		$contentLanguageOption = ' &VAR_OPEN_CONTENTLANGUAGE_OPTION;';
		foreach ($this->i10nMan->getAllSupportedLanguages () as $language) {
			if ($language == $this->config->getConfigItem ('/userinterface/contentlanguage', TYPE_STRING)) {
					$contentLanguageOption .= ' CONTENTLANGUAGE_OPTION_SELECTED (' . $language . ')';
			} else {
				$contentLanguageOption .= ' CONTENTLANGUAGE_OPTION (' . $language . ')';
			}
		}
		$contentLanguageOption .= ' &VAR_CLOSE_CONTENTLANGUAGE_OPTION;';
		$this->vars['VAR_CONTENTLANGUAGE_OPTION'] = $this->parse ($contentLanguageOption);
	
		$themeOption = ' &VAR_OPEN_THEME_OPTION;';
		foreach ($this->getAllSupportedSkins () as $askin) {
			if ($askin == $this->config->getConfigItem ('/userinterface/skin', TYPE_STRING)) {
				$themeOption .= ' THEME_OPTION_SELECTED (' . $askin . ')';
			} else {
				$themeOption .= ' THEME_OPTION (' . $askin . ')';
			}
		}
		$themeOption .= ' &VAR_CLOSE_THEME_OPTION;';
		$this->vars['VAR_THEME_OPTION'] = $this->parse ($themeOption);
	}
	
	if ($this->module == 'forgotpass') {
		$this->vars['VAR_LOSTPASSFORM_ACTION'] = './index.php?module=sendpass';
		$this->vars['VAR_LOSTPASSFORM_METHOD'] = 'post';
		$this->vars['VAR_LOSTPASS_NAMENAME'] = 'username';
		$this->vars['VAR_LOSTPASS_EMAILNAME'] = 'useremail';
		$this->vars['VAR_LOSTPASS_SUBMITNAME'] = 'submit';
	}
	
	if ($this->module == 'formpostnews') {
		$this->vars['POSTNEWS_ACTION'] = 'index.php?module=postnews';
		$this->vars['POSTNEWS_METHOD'] = 'post';
		$this->vars['POSTNEWS_SUBJECT_NAME'] = 'subject';
		$this->vars['POSTNEWS_MESSAGE_NAME'] = 'message';
		$this->vars['POSTNEWS_TOPIC_NAME'] = 'topic';
		$alltopics = NULL;
		foreach ($this->news->getAllTopics ($language) as $topic) {
			$alltopics .= '  POSTNEWS_OPTION_TOPIC (' . $topic['name'] . ')';
		}
		$this->vars['POSTNEWS_GETALLTOPICS'] = $this->parse ($alltopics);
	}

	if ($this->module == 'formpostcomment') {
		global $onItemID, $onNews;
			
		$this->vars['POSTCOMMENT_ACTION'] = 'index.php?module=postcomment';
		$this->vars['POSTCOMMENT_METHOD'] = 'post';
		$this->vars['POSTCOMMENT_SUBJECT_NAME'] = 'subject';
		$this->vars['POSTCOMMENT_MESSAGE_NAME'] = 'message';
		$this->vars['POSTCOMMENT_TOPIC_NAME'] = 'topic';
		$this->vars['POSTCOMMENT_ONITEM_NAME'] = 'onitem_number';
		$this->vars['POSTCOMMENT_ONNEWS_NAME'] = 'onnews';

		$this->vars['POSTCOMMENT_ONITEM_VALUE'] = $onItemID;
		$this->vars['POSTCOMMENT_ONNEWS_VALUE'] = $onNews;
		
	}
	
	if ($this->module == 'index') {
		$this->vars['LATEST_NEWS_ITEMS'] = $this->getHTMLLatestNewsItems ();
		if ($this->user->isLoggedIn ()) {
			$this->vars['LINK_POST_NEW_NEWSITEM'] = $skin['variable']['LINK_POST_NEW_NEWSITEM_LOGGEDIN'];
		} else {
			$this->vars['LINK_POST_NEW_NEWSITEM'] = $skin['variable']['LINK_POST_NEW_NEWSITEM_NOTLOGGEDIN'];
		}
		$this->vars['LINK_POST_NEW_NEWSITEM'] = $this->parse ($this->vars['LINK_POST_NEW_NEWSITEM']);
	}
}

if ($this->module == 'login' && $this->user->isLoggedIn () == false) {
	$this->vars['VAR_FORGOT_PASSWORD_LINK'] = '<a href="index.php?module=forgotpass">&TEXT_FORGOT_PASSWORD;</a>';
} else {
	$this->vars['VAR_FORGOT_PASSWORD_LINK'] = '';
}

// language vars
$this->vars['TEXT_ADMIN'] = $this->i10nMan->translate ('Admin');
$this->vars['TEXT_DATABASE_TYPE'] = $this->i10nMan->translate ('Database type');
$this->vars['TEXT_DATABASE_HOST'] = $this->i10nMan->translate ('Database host');
$this->vars['TEXT_DATABASE_DBNAME'] = $this->i10nMan->translate ('Database name');
$this->vars['TEXT_DATABASE_USER'] = $this->i10nMan->translate ('Database username');
$this->vars['TEXT_DATABASE_PASSWORD'] = $this->i10nMan->translate ('Database password');
$this->vars['TEXT_ADMIN_MODULES_NAME'] = $this->i10nMan->translate ('Module name');
$this->vars['TEXT_ADMIN_MODULES_AUTHORIZED_ONLY'] = $this->i10nMan->translate ('Registerd users only');
$this->vars['TEXT_ADMIN_MODULES_LANGUAGES'] = $this->i10nMan->translate ('Available languages');
$this->vars['TEXT_ADMIN_MODULES_VISIT'] = $this->i10nMan->translate ('View');
$this->vars['TEXT_ADMIN_MODULES_VIEW_PAGE'] = $this->i10nMan->translate ('View page');
$this->vars['TEXT_ADD_MODULE'] = $this->i10nMan->translate ('Add module');
$this->vars['TEXT_ADMIN_MODULES_ADD_PAGE'] = $this->i10nMan->translate ('Add translation to module');
$this->vars['TEXT_MANAGE_MODULES'] = $this->i10nMan->translate ('Manage modules');
$this->vars['TEXT_WARNING_CHANGES_LOST'] = $this->i10nMan->translate ('If you add a module, changes in other modules are lost!');
$this->vars['TEXT_ADD_PAGE'] = $this->i10nMan->translate ('Add page');
$this->vars['TEXT_EDIT_PAGE_INTRODUCTION'] = $this->i10nMan->translate ('Here you can edit a page.');
if (isset ($addToModule)) {
	$this->vars['TEXT_YOU_HAVE_CHOSEN_TO_ADD_PAGE_TO_MODULE'] = $this->i10nMan->translate ('You have chosen to add a page to module %1', $addToModule);
}
$this->vars['TEXT_ADMIN_MODULES_DELETE_PAGE'] = $this->i10nMan->translate ('Delete page');
$this->vars['TEXT_ADMIN_MODULES_DELETE_MODULE'] = $this->i10nMan->translate ('Delete module');
$this->vars['TEXT_EDIT_PAGE'] = $this->i10nMan->translate ('Edit page');
$this->vars['TEXT_DELETE_PAGE'] = $this->i10nMan->translate ('Delete page');
$this->vars['TEXT_DELETE_MODULE'] = $this->i10nMan->translate ('Delete module');
$this->vars['TEXT_LANGUAGE'] = $this->i10nMan->translate ('Language');
$this->vars['TEXT_PAGENAME'] = $this->i10nMan->translate ('Name');
$this->vars['TEXT_SAVE_PAGE'] = $this->i10nMan->translate ('Save this page');
$this->vars['TEXT_SITE_NAME'] = $this->i10nMan->translate ('Site name');
$this->vars['TEXT_ADMIN_MODULES_ADMIN_ONLY'] = $this->i10nMan->translate ('Admin only');
$this->vars['TEXT_SAVE_MANUALLY_END'] = $this->i10nMan->translate ('End of the content of site.config.php');
$this->vars['TEXT_SAVE_MANUALLY'] = $this->i10nMan->translate ('Save the folowing text in the file "site.config.php" in the directory where MorgOS is installed, then continue.');
$this->vars['TEXT_LOGIN'] = $this->i10nMan->translate ('Login');
$this->vars['TEXT_PASSWORD1'] = $this->i10nMan->translate ('Password');
$this->vars['TEXT_PASSWORD2'] = $this->i10nMan->translate ('Password (repeat)');
$this->vars['TEXT_NAME_OR_EMAIL'] = $this->i10nMan->translate ('Fill in your username OR your email.');
$this->vars['TEXT_NAME'] = $this->i10nMan->translate ('Username');
$this->vars['TEXT_EMAIL'] = $this->i10nMan->translate ('E-Mail address');
$this->vars['TEXT_PASSWORD'] = $this->i10nMan->translate ('Password');
$this->vars['TEXT_SUBMIT_REGISTER'] = $this->i10nMan->translate ('Register now');
$this->vars['TEXT_REGISTER'] = $this->i10nMan->translate ('Don\'t have an account? register now.');
$this->vars['TEXT_SAVE_SETTINGS'] = $this->i10nMan->translate ('Save your settings');
$this->vars['TEXT_CHANGE_PASSWORD'] = $this->i10nMan->translate ('Change your password');
$this->vars['TEXT_GENERAL_SETTINGS'] = $this->i10nMan->translate ('General options');
$this->vars['TEXT_LANGUAGE'] = $this->i10nMan->translate ('Language');
$this->vars['TEXT_CONTENTLANGUAGE'] = $this->i10nMan->translate ('Content Language');
$this->vars['TEXT_THEME'] = $this->i10nMan->translate ('Theme');
$this->vars['TEXT_CONTENT'] = $this->i10nMan->translate ('Content');
$this->vars['TEXT_LOGIN'] = $this->i10nMan->translate ('Login');
$this->vars['TEXT_USER'] = $this->i10nMan->translate ('User');
$this->vars['TEXT_NAVIGATION'] = $this->i10nMan->translate ('Navigate');
$this->vars['TEXT_FORGOT_PASSWORD'] = $this->i10nMan->translate ('Forgotten your password?');
$this->vars['TEXT_LOSTPASS_SUBMIT'] = $this->i10nMan->translate ('Send new password');
$this->vars['TEXT_EXTENSION_MISSING_FILE'] = $this->i10nMan->translate ('A required file is missing to make this extension work correctly.');
$this->vars['TEXT_EXTENSION_INCOMPATIBLE'] = $this->i10nMan->translate ('This extension is incompatible with this version of MorgOS.');
$this->vars['TEXT_EXTENSION_LOAD'] = $this->i10nMan->translate ('Load extenion');
$this->vars['TEXT_SAVE_EXTENSIONS_SUBMIT'] = $this->i10nMan->translate ('Save extensions');
// the skin defined vars
foreach ($skin['variable'] as $key => $skinVar) {
	if (! array_key_exists (strtoupper ($key), $this->vars)) {
		$this->vars[strtoupper ($key)] = $this->parse ($skinVar);
	}
}
?>
