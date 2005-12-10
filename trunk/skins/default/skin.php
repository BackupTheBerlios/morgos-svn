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
$skin['general']['name'] = 'MorgOS Default';
$skin['general']['version'] = '0.1';
$skin['general']['maxversion'] = '0.1';
$skin['general']['minversion'] = '0.1';

$skin['variable']['license'] = "Copyright &copy; 2005 MorgOS";
$skin['variable']['admin_modules_open'] = "<table border='1'><tr><td>TEXT_ADMIN_MODULES_NAME</td><td>TEXT_ADMIN_MODULES_AUTHORIZED_ONLY</td><td>TEXT_ADMIN_MODULES_ADMIN_ONLY</td><td>TEXT_ADMIN_MODULES_LANGUAGES</td><td>TEXT_ADMIN_MODULES_VISIT</td><td>TEXT_ADD_PAGE</td><td>TEXT_EDIT_PAGE</td><td>TEXT_DELETE_PAGE</td><td>TEXT_DELETE_MODULE</td>";
$skin['variable']['admin_modules_close'] = "</table>";
$skin['variable']['var_skin_default_notices'] = "VAR_ERRORS VAR_WARNINGS VAR_NOTICES VAR_DEBUGGING";
$skin['variable']['var_login_form'] = 
	"<div id='loginform'> FORM (VAR_LOGIN_FORM_ACTION, VAR_LOGIN_FORM_METHOD) 
		TEXT_LOGIN:  INPUT (text, VAR_LOGIN_FORM_LOGINNAME_NAME, VAR_LOGIN_FORM_LOGINNAME_VALUE) <br />
		TEXT_PASSWORD:  INPUT (password, VAR_LOGIN_FORM_PASSWORD_NAME) <br />
		INPUT (submit, VAR_LOGIN_FORM_SUBMIT_NAME, TEXT_LOGIN) 
		CLOSEFORM ()
	</div>";

$skin['functions']['form'] = "<form action='ACTION' method='METHOD' EXTRA>";
$skin['functions']['closeform'] = "</form>";
$skin['functions']['select'] = "<select name='NAME' EXTRA>";
$skin['functions']['closeselect'] = "</select>";
$skin['functions']['option'] = "<option value='ANOPTION'>ANOPTION</option>";
$skin['functions']['input'] = "<input name='NAME' value='VALUE' type='TYPE' EXTRA />";
$skin['functions']['admin_database_type_option'] = "<option value='TYPE'>TYPE</option>";
$skin['functions']['admin_database_type_option_selected'] = "<option selected='selected' value='TYPE'>TYPE</option>";
$skin['functions']['admin_navigation_open'] = "<ul id='navigation'>";
$skin['functions']['admin_navigation_close'] = "</ul>";
$skin['functions']['admin_navigation_item'] = "<li><a href='LINK'>TEXT</a></li>";
$skin['functions']['navigation_open'] = "<ul id='navigation'>";
$skin['functions']['navigation_close'] = "</ul>";
$skin['functions']['navigation_item'] = "<li><a href='LINK'>TEXT</a></li>";
$skin['functions']['user_navigation_open'] = "<ul id='navigation'>";
$skin['functions']['user_navigation_close'] = "</ul>";
$skin['functions']['user_navigation_item'] = "<li><a href='LINK'>TEXT</a></li>";
$skin['functions']['admin_modules_item'] = "<tr><td>NAME</td><td>AUTHORIZED_ONLY</td><td>ADMIN_ONLY</td><td>LANGUAGE</td><td> INPUT (submit, NSUBMIT, TEXT_ADMIN_MODULES_VIEW_PAGE)</td><td> INPUT (submit, ADDPAGE, TEXT_ADMIN_MODULES_ADD_PAGE)</td><td> INPUT (submit, EDITPAGE, TEXT_EDIT_PAGE)</td><td> INPUT (submit, DELETEPAGE, TEXT_ADMIN_MODULES_DELETE_PAGE)</td><td> INPUT (submit, DELETEMODULE, TEXT_ADMIN_MODULES_DELETE_MODULE)</td></tr>";
$skin['functions']['admin_modules_form_needauthorize'] = "<input name='NAME'  CHECKED type='checkbox' />";
$skin['functions']['admin_modules_form_noneedauthorize'] = "<input name='NAME' type='checkbox' />";
$skin['functions']['admin_modules_form_admin_only'] = "<input name='NAME'  CHECKED type='checkbox' />";
$skin['functions']['admin_modules_form_not_admin_only'] = "<input name='NAME' type='checkbox' />";
$skin['functions']['admin_modules_form_item_available_languages'] = "<option value='LANGUAGE'>LANGUAGE</option>";
$skin['functions']['admin_modules_form_open_available_languages'] = "<select name='NAME'>";
$skin['functions']['admin_modules_form_close_available_languages'] = "</select>";
$skin['functions']['warning'] = "<div class='warning'>CONTENT</div>";
$skin['functions']['error'] = "<div class='error'>CONTENT</div>";
$skin['functions']['notice'] = "<div class='notice'>CONTENT</div>";
$skin['functions']['debug'] = "<div class='debug'>CONTENT</div>";
?>
