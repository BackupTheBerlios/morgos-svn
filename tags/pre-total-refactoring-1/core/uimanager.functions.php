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
$this->functions['form'] = array ('ACTION', 'METHOD', 'EXTRA');
$this->functions['closeform'] = array ();
$this->functions['input'] = array ('TYPE', 'NAME', 'VALUE', 'EXTRA');
$this->functions['select'] = array ('NAME', 'EXTRA');
$this->functions['closeselect'] = array ();
$this->functions['option'] = array ('ANOPTION');
$this->functions['error'] = array ('CONTENT');
$this->functions['warning'] = array ('CONTENT');
$this->functions['notice'] = array ('CONTENT');
$this->functions['debug'] = array ('CONTENT');
$this->functions['file'] = array ('FILENAME');
$this->functions['box'] = array ('TITLE', 'CONTENT');

if (substr ($this->module, 0, 5) == 'admin') {
	$this->functions['admin_navigation_open'] = array ();
	$this->functions['admin_navigation_close'] = array ();
	$this->functions['admin_navigation_item'] = array ('TEXT', 'LINK');
	
	if ($this->module == 'admin/database') {
		$this->functions['admin_database_type_option'] = array ('TYPE');
		$this->functions['admin_database_type_option_selected'] = array ('TYPE');
	}
	
	if ($this->module == 'admin/pages') {
		$this->functions['admin_modules_item_innavigator'] = array ('NAME', 'AUTHORIZED_ONLY', 'ADMIN_ONLY', 'LANGUAGE', 'NSUBMIT', 'ADDPAGE', 'DELETEPAGE', 'DELETEMODULE', 'EDITPAGE', 'CHILDS', 'PARENT');
		$this->functions['admin_modules_item_notinnavigator'] = array ('NAME', 'AUTHORIZED_ONLY', 'ADMIN_ONLY', 'LANGUAGE', 'NSUBMIT', 'ADDPAGE', 'DELETEPAGE', 'DELETEMODULE', 'EDITPAGE');
		$this->functions['admin_modules_form_needauthorize'] = array ('NAME');
		$this->functions['admin_modules_form_noneedauthorize'] = array ('NAME');
	
		$this->functions['admin_modules_form_admin_only'] = array ('NAME');
		$this->functions['admin_modules_form_not_admin_only'] = array ('NAME');
	
		$this->functions['admin_modules_form_item_available_languages'] = array ('LANGUAGE');
		$this->functions['admin_modules_form_open_available_languages'] = array ('NAME');
		$this->functions['admin_modules_form_close_available_languages'] = array ();
	}

	if ($this->module == 'admin/users') {
		$this->functions['admin_user_isadmin'] = array ('USERNAME');
		$this->functions['admin_user_isnotadmin'] = array ('USERNAME');
		$this->functions['admin_user'] = array ('USERNAME', 'EMAIL', 'ISADMIN');
	}

	if ($this->module == 'admin/extensions') {
		$this->functions['admin_extension_item'] = array ('NAME', 'STATUS', 'INSTALL');
		$this->functions['admin_extension_status_ok'] = array ('NAME');
		$this->functions['admin_extension_status_incompatible'] = array ('NAME');
		$this->functions['admin_extension_status_loaded'] = array ('NAME');
		$this->functions['admin_extension_status_missing_file'] = array ('NAME');
		$this->functions['admin_extension_status_not_installed'] = array ('NAME');
		$this->functions['admin_extension_install'] = array ('INSTALLLINK');
		$this->functions['admin_extension_uninstall'] = array ('UNINSTALLLINK');
	}
} else {
	$this->functions['navigation_open'] = array ();
	$this->functions['navigation_close'] = array ();
	$this->functions['navigation_item_with_childs'] = array ('TEXT', 'LINK', 'CHILDS');
	$this->functions['navigation_item_with_childs_nolink'] = array ('TEXT', 'CHILDS');
	$this->functions['navigation_item_without_childs'] = array ('TEXT', 'LINK');

	if ($this->module == 'usersettings') {
		$this->functions['language_option'] = array ('LANGUAGE');
		$this->functions['language_option_selected'] = array ('LANGUAGE');
		$this->functions['contentlanguage_option'] = array ('CONTENTLANGUAGE');
		$this->functions['contentlanguage_option_selected'] = array ('CONTENTLANGUAGE');
		$this->functions['theme_option'] = array ('THEME');
		$this->functions['theme_option_selected'] = array ('THEME');
	}
	
	if ($this->module == 'index') {
		$this->functions['latest_news_items_item'] = array ('SUBJECT', 'MESSAGE', 'ONNEWS', 'ONITEM');
		$this->functions['to_postnew_comment'] = array ('ONITEM', 'ONNEWS');
		$this->functions['link_post_comment_loggedin'] = array ('ONITEM', 'ONNEWS');
		$this->functions['link_post_comment_notloggedin'] = array ('ONITEM', 'ONNEWS');
	}
	
	if ($this->module == 'formpostnews') {
		$this->functions['postnews_option_topic'] = array ('TOPICNAME');
	}
}
?>
