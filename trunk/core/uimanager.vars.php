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
$vars['ADMIN_DATABASE_FORM_DATABASE_TYPE_OPTIONS'] = $DBTypeOptions;
$vars['ADMIN_DATABASE_FORM_ACTION'] = './admin.php?module=databasesave';
$vars['ADMIN_DATABASE_FORM_NAME_HOST'] = '/database/host';
$vars['ADMIN_DATABASE_FORM_NAME_DATABASE_TYPE'] = '/database/type';
$vars['ADMIN_DATABASE_FORM_NAME_DBNAME'] = '/database/name';
$vars['ADMIN_DATABASE_FORM_NAME_USER'] = '/database/user';
$vars['ADMIN_DATABASE_FORM_NAME_PASSWORD'] = '/database/password';
$vars['ADMIN_DATABASE_FORM_VALUE_HOST'] = $this->config->getConfigItem ('/database/host', TYPE_STRING);
$vars['ADMIN_DATABASE_FORM_VALUE_DBNAME'] = $this->config->getConfigItem ('/database/name', TYPE_STRING);
$vars['ADMIN_DATABASE_FORM_VALUE_USER'] = $this->config->getConfigItem ('/database/user', TYPE_STRING);
$vars['ADMIN_DATABASE_FORM_VALUE_PASSWORD'] = $this->config->getConfigItem ('/database/password', TYPE_STRING);
$vars['ADMIN_GENERAL_FORM_ACTION'] = 'admin.php?module=generalsave';
$vars['ADMIN_GENERAL_FORM_NAME_SITE_NAME'] = '/general/sitename';
$vars['ADMIN_GENERAL_FORM_VALUE_SITE_NAME'] = $this->config->getConfigItem ('/general/sitename', TYPE_STRING);
$vars['ADMIN_NAVIGATION'] = $this->getAdminNavigator ();
$vars['ADMIN_LINK_INDEX'] = 'admin.php';
$vars['ADMIN_LINK_GENERAL'] = 'admin.php?module=general';
$vars['ADMIN_LINK_DATABASE'] = 'admin.php?module=database';
// language vars
$vars['TEXT_ADMIN_INTRODUCTION'] = $this->i10nMan->translate ('This is the admin. In the admin you can setup all what you need to configure.');
$vars['TEXT_ADMIN_INDEX'] = $this->i10nMan->translate ('Admin Home');
$vars['TEXT_ADMIN_GENERAL'] = $this->i10nMan->translate ('General');
$vars['TEXT_ADMIN_DATABASE'] = $this->i10nMan->translate ('Database');
$vars['TEXT_ADMIN_SUBMIT'] = $this->i10nMan->translate ('Save settings');
$vars['TEXT_DATABASE_TYPE'] = $this->i10nMan->translate ('Database type');
$vars['TEXT_DATABASE_HOST'] = $this->i10nMan->translate ('Database host');
$vars['TEXT_DATABASE_DBNAME'] = $this->i10nMan->translate ('Database name');
$vars['TEXT_DATABASE_USER'] = $this->i10nMan->translate ('Database username');
$vars['TEXT_DATABASE_PASSWORD'] = $this->i10nMan->translate ('Database password');
$vars['TEXT_SITE_NAME'] = $this->i10nMan->translate ('Site name');
$vars['TEXT_ADMIN'] = $this->i10nMan->translate ('Admin');
$vars['SITE_NAME'] = $this->config->getConfigItem ('/general/sitename', TYPE_STRING);
// the skin defined vars

?>
