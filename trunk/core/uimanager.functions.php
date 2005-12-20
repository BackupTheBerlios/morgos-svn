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
// MorgOS defined functions
$this->functions['form'] = array ('name' => 'FORM', 'params' => array ('ACTION', 'METHOD', 'EXTRA'));
$this->functions['closeform'] = array ('name' => 'CLOSEFORM', 'params' => array ());
$this->functions['input'] = array ('name' => 'INPUT', 'params' => array ('TYPE', 'NAME', 'VALUE', 'EXTRA'));
$this->functions['select'] = array ('name' => 'SELECT', 'params' => array ('NAME', 'EXTRA'));
$this->functions['closeselect'] = array ('name' => 'CLOSESELECT', 'params' => array ());
$this->functions['option'] = array ('name' => 'OPTION', 'params' => array ('ANOPTION'));
$this->functions['admin_database_type_option'] = array ('name' => 'ADMIN_DATABASE_TYPE_OPTION', 'params' => array ('TYPE'));
$this->functions['admin_database_type_option_selected'] = array ('name' => 'ADMIN_DATABASE_TYPE_OPTION_SELECTED', 'params' => array ('TYPE'));
$this->functions['admin_navigation_open'] = array ('name' => 'ADMIN_NAVIGATION_OPEN', 'params' => array ());
$this->functions['admin_navigation_close'] = array ('name' => 'ADMIN_NAVIGATION_CLOSE', 'params' => array ());
$this->functions['admin_navigation_item'] = array ('name' => 'ADMIN_NAVIGATION_ITEM', 'params' => array ('TEXT', 'LINK'));

$this->functions['user_navigation_open'] = array ('name' => 'USER_NAVIGATION_OPEN', 'params' => array ());
$this->functions['user_navigation_close'] = array ('name' => 'USER_NAVIGATION_CLOSE', 'params' => array ());
$this->functions['user_navigation_item_with_childs'] = array ('name' => 'USER_NAVIGATION_ITEM_WITH_CHILDS', 'params' => array ('TEXT', 'LINK', 'CHILDS'));
$this->functions['user_navigation_item_without_childs'] = array ('name' => 'USER_NAVIGATION_ITEM_WITHOUT_CHILDS', 'params' => array ('TEXT', 'LINK'));

$this->functions['navigation_open'] = array ('name' => 'NAVIGATION_OPEN', 'params' => array ());
$this->functions['navigation_close'] = array ('name' => 'NAVIGATION_CLOSE', 'params' => array ());
$this->functions['navigation_item_with_childs'] = array ('name' => 'NAVIGATION_ITEM_WITH_CHILDS', 'params' => array ('TEXT', 'LINK', 'CHILDS'));
$this->functions['navigation_item_without_childs'] = array ('name' => 'NAVIGATION_ITEM_WITHOUT_CHILDS', 'params' => array ('TEXT', 'LINK'));
$this->functions['admin_modules_item_innavigator'] = array ('name' => 'ADMIN_MODULES_ITEM_INNAVIGATOR', 'params' => array ('NAME', 'AUTHORIZED_ONLY', 'ADMIN_ONLY', 'LANGUAGE', 'NSUBMIT', 'ADDPAGE', 'DELETEPAGE', 'DELETEMODULE', 'EDITPAGE', 'CHILDS', 'PARENT'));
$this->functions['admin_modules_item_notinnavigator'] = array ('name' => 'ADMIN_MODULES_ITEM_NOTINNAVIGATOR', 'params' => array ('NAME', 'AUTHORIZED_ONLY', 'ADMIN_ONLY', 'LANGUAGE', 'NSUBMIT', 'ADDPAGE', 'DELETEPAGE', 'DELETEMODULE', 'EDITPAGE'));
$this->functions['admin_modules_form_needauthorize'] = array ('name' => 'ADMIN_MODULES_FORM_NEEDAUTHORIZE', 'params' => array ('NAME'));
$this->functions['admin_modules_form_noneedauthorize'] = array ('name' => 'ADMIN_MODULES_FORM_NONEEDAUTHORIZE', 'params' => array ('NAME'));

$this->functions['admin_modules_form_admin_only'] = array ('name' => 'ADMIN_MODULES_FORM_ADMIN_ONLY', 'params' => array ('NAME'));
$this->functions['admin_modules_form_not_admin_only'] = array ('name' => 'ADMIN_MODULES_FORM_NOT_ADMIN_ONLY', 'params' => array ('NAME'));

$this->functions['admin_modules_form_item_available_languages'] = array ('name' => 'ADMIN_MODULES_FORM_ITEM_AVAILABLE_LANGUAGES', 'params' => array ('LANGUAGE'));
$this->functions['admin_modules_form_open_available_languages'] = array ('name' => 'ADMIN_MODULES_FORM_OPEN_AVAILABLE_LANGUAGES', 'params' => array ('NAME'));
$this->functions['admin_modules_form_close_available_languages'] = array ('name' => 'ADMIN_MODULES_FORM_CLOSE_AVAILABLE_LANGUAGES', 'params' => array ());
$this->functions['error'] = array ('name' => 'ERROR', 'params' => array ('CONTENT'));
$this->functions['warning'] = array ('name' => 'WARNING', 'params' => array ('CONTENT'));
$this->functions['notice'] = array ('name' => 'NOTICE', 'params' => array ('CONTENT'));
$this->functions['debug'] = array ('name' => 'DEBUG', 'params' => array ('CONTENT'));
$this->functions['file'] = array ('name' => 'FILE', 'params' => array ('FILENAME'));
$this->functions['language_option'] = array ('name' => 'LANGUAGE_OPTION', 'params' => array ('LANGUAGE'));
$this->functions['language_option_selected'] = array ('name' => 'LANGUAGE_OPTION_SELECTED', 'params' => array ('LANGUAGE'));
$this->functions['contentlanguage_option'] = array ('name' => 'CONTENTLANGUAGE_OPTION', 'params' => array ('CONTENTLANGUAGE'));
$this->functions['contentlanguage_option_selected'] = array ('name' => 'CONTENTLANGUAGE_OPTION_SELECTED', 'params' => array ('CONTENTLANGUAGE'));
$this->functions['theme_option'] = array ('name' => 'THEME_OPTION', 'params' => array ('THEME'));
$this->functions['theme_option_selected'] = array ('name' => 'THEME_OPTION_SELECTED', 'params' => array ('THEME'));
$this->functions['box'] = array ('name' => 'BOX', 'params' => array ('TITLE', 'CONTENT'));
$this->functions['admin_user_isadmin'] = array ('name' => 'ADMIN_USER_ISADMIN', 'params' => array ('USERNAME'));
$this->functions['admin_user_isnotadmin'] = array ('name' => 'ADMIN_USER_ISNOTADMIN', 'params' => array ('USERNAME'));
$this->functions['admin_user'] = array ('name' => 'ADMIN_USER', 'params' => array ('USERNAME', 'EMAIL', 'ISADMIN'));
?>
