<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005 MorgOS
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
$DBTypeOptions = NULL;
foreach ($this->DBManager->getAllSupportedDatabases () as $key => $supported) {
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
$this->vars['VAR_ADMIN_GENERAL_FORM_ACTION'] = 'admin.php?module=generalsave';
$this->vars['VAR_ADMIN_GENERAL_FORM_NAME_SITE_NAME'] = '/general/sitename';
$this->vars['VAR_ADMIN_GENERAL_FORM_VALUE_SITE_NAME'] = $this->config->getConfigItem ('/general/sitename', TYPE_STRING);
$this->vars['VAR_ADMIN_NAVIGATION'] = $this->getAdminNavigator ();
$this->vars['VAR_ADMIN_LINK_INDEX'] = 'admin.php';
$this->vars['VAR_ADMIN_LINK_GENERAL'] = 'admin.php?module=general';

$pages = ' VAR_ADMIN_PAGES_OPEN';
$pages = $this->parse ($pages);
foreach ($this->getAllAvailableModules (false) as $module) {
	$pageName = $this->getPageNameFromModule ($module);
	if ($this->needAuthorizeFromModule ($module)) {
		$authorizedOnly .= ' ADMIN_PAGE_FORM_NEEDAUTHORIZE (' . $module .')';
	} else {
		$authorizedOnly .= ' ADMIN_PAGE_FORM_NONEEDAUTHORIZE (' . $module .')';
	}
	$authorizedOnly = $this->parse ($authorizedOnly);
	
	$language = $this->getLanguageFromModule ($module);
	$link = 'index.php?module=' . $module . '&contentlanguage=' . $language;
	$pages .= ' ADMIN_PAGE_ITEM ('.$module . ', ' . $authorizedOnly .', '. $language . ', '. $link .')';
}
$pages .= ' VAR_ADMIN_PAGES_CLOSE';
$this->vars['VAR_ADMIN_PAGES'] = $pages;

$this->vars['VAR_ADMIN_LINK_DATABASE'] = 'admin.php?module=database';
$this->vars['VAR_ADMIN_LINK_PAGES'] = 'admin.php?module=pages';
$this->vars['VAR_PAGE_CONTENT'] = $this->getModuleContent ();
$this->vars['VAR_NAVIGATION'] = $this->getNavigator ();
$this->vars['VAR_SITE_TITLE'] = $this->config->getConfigItem ('/general/sitename', TYPE_STRING);
// language vars
$this->vars['TEXT_ADMIN_INTRODUCTION'] = $this->i10nMan->translate ('This is the admin. In the admin you can setup all what you need to configure.');
$this->vars['TEXT_ADMIN_INDEX'] = $this->i10nMan->translate ('Admin Home');
$this->vars['TEXT_ADMIN_GENERAL'] = $this->i10nMan->translate ('General');
$this->vars['TEXT_ADMIN_DATABASE'] = $this->i10nMan->translate ('Database');
$this->vars['TEXT_ADMIN_PAGES'] = $this->i10nMan->translate ('Pages');
$this->vars['TEXT_ADMIN_SUBMIT'] = $this->i10nMan->translate ('Save settings');
$this->vars['TEXT_DATABASE_TYPE'] = $this->i10nMan->translate ('Database type');
$this->vars['TEXT_DATABASE_HOST'] = $this->i10nMan->translate ('Database host');
$this->vars['TEXT_DATABASE_DBNAME'] = $this->i10nMan->translate ('Database name');
$this->vars['TEXT_DATABASE_USER'] = $this->i10nMan->translate ('Database username');
$this->vars['TEXT_DATABASE_PASSWORD'] = $this->i10nMan->translate ('Database password');
$this->vars['TEXT_ADMIN_MODULE_NAME'] = $this->i10nMan->translate ('Module name');
$this->vars['TEXT_ADMIN_MODULE_AUTHORIZED_ONLY'] = $this->i10nMan->translate ('Registerd users only');
$this->vars['TEXT_ADMIN_MODULE_LANGUAGES'] = $this->i10nMan->translate ('Available languages');
$this->vars['TEXT_ADMIN_MODULE_VISIT'] = $this->i10nMan->translate ('View');
$this->vars['TEXT_ADMIN_MODULE_VIEW'] = $this->i10nMan->translate ('View module');
$this->vars['TEXT_SITE_NAME'] = $this->i10nMan->translate ('Site name');
$this->vars['TEXT_ADMIN'] = $this->i10nMan->translate ('Admin');
// the skin defined vars
$iniFile = parse_ini_file ($this->skinPath . 'skin.ini', true);
$this->vars['VAR_ADMIN_PAGES_OPEN'] = $this->parse ($iniFile['variable']['admin_pages_open']);
$this->vars['VAR_ADMIN_PAGES_CLOSE'] = $this->parse ($iniFile['variable']['admin_pages_close']);
$this->vars['VAR_SKIN_LICENSE'] = $this->parse ($iniFile['vars']['license']);
?>
