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
$skin['general']['name'] = 'MorgOS Default';
$skin['general']['version'] = '0.1';
$skin['general']['maxversion'] = '0.1';
$skin['general']['minversion'] = '0.1';

$skin['variable']['license'] = "Copyright &copy; 2005 MorgOS";
$skin['variable']['admin_modules_open'] = "<table border='1'><!--<tr><td>TEXT_ADMIN_MODULES_NAME</td><td>TEXT_ADMIN_MODULES_AUTHORIZED_ONLY</td><td>TEXT_ADMIN_MODULES_ADMIN_ONLY</td><td>TEXT_ADMIN_MODULES_LANGUAGES</td><td>TEXT_ADMIN_MODULES_VISIT</td><td>TEXT_ADD_PAGE</td><td>TEXT_EDIT_PAGE</td><td>TEXT_DELETE_PAGE</td><td>TEXT_DELETE_MODULE</td><td>TEXT_PARENT</td>-->";
$skin['variable']['admin_modules_close'] = "</table>";
$skin['variable']['var_skin_default_notices'] = "&VAR_ERRORS; &VAR_WARNINGS; &VAR_NOTICES; &VAR_DEBUGGING;";
$skin['variable']['var_login_form'] = 
	" BOX ( &TEXT_USER; ,<div id='loginform'> FORM (&VAR_LOGIN_FORM_ACTION;, &VAR_LOGIN_FORM_METHOD;) 
		&TEXT_LOGIN;:  INPUT (text, &VAR_LOGIN_FORM_LOGINNAME_NAME;, &VAR_LOGIN_FORM_LOGINNAME_VALUE;) <br />
		&TEXT_PASSWORD;:  INPUT (password, &VAR_LOGIN_FORM_PASSWORD_NAME;) <br />
		<a href='&VAR_TO_REGISTER_USER;'>&TEXT_REGISTER;</a> <br />
		&VAR_FORGOT_PASSWORD_LINK; <br />
		INPUT (submit, &VAR_LOGIN_FORM_SUBMIT_NAME;, &TEXT_LOGIN;) 
		CLOSEFORM ()
	</div>)";
$skin['variable']['var_open_language_option'] = "<select name='&VAR_LANGUAGE_OPTION_NAME;'>";
$skin['variable']['var_close_language_option'] = "</select>";
$skin['variable']['var_open_contentlanguage_option'] = "<select name='&VAR_CONTENTLANGUAGE_OPTION_NAME;'>";
$skin['variable']['var_close_contentlanguage_option'] = "</select>";
$skin['variable']['var_open_theme_option'] = "<select name='&VAR_THEME_OPTION_NAME;'>";
$skin['variable']['var_close_theme_option'] = "</select>";
$skin['variable']['var_user_admin_open'] = "<table border='1'><tr><td>&TEXT_USERNAME;</td><td>&TEXT_EMAIL;</td><td>&TEXT_ISADMIN;</td></tr>";
$skin['variable']['var_user_admin_close'] = "</table>";
$skin['variable']['open_extensions_admin'] = "FORM (&VAR_EXTENSIONS_ADMIN_ACTION;,&VAR_EXTENSIONS_ADMIN_METHOD;)<table>";
$skin['variable']['close_extensions_admin'] = "<tr><td colspan=\"3\"> INPUT (submit, &VAR_SAVE_EXTENSIONS_SUBMIT;, &TEXT_SAVE_EXTENSIONS_SUBMIT;)</td></tr></table> CLOSEFORM ()";
$skin['variable']['sidebar'] = 'BOX (&TEXT_NAVIGATION;,&VAR_NAVIGATION;) &VAR_USER_PLACE;';
$skin['variable']['subbar'] = '&MORGOS_COPYRIGHT; <br />&TIME_RUNNED;';
$skin['variable']['LATEST_NEWS_ITEMS_OPEN'] = '<div class="news">';
$skin['variable']['LATEST_NEWS_ITEMS_CLOSE'] = '&LINK_POST_NEW_NEWSITEM;</div>';
$skin['variable']['LINK_POST_NEW_NEWSITEM_LOGGEDIN'] = '<a href="&TO_POSTNEW_NEWSITEMFORM;">&TEXT_POST_NEW_NEWSITEM;</a>';
$skin['variable']['LINK_POST_NEW_NEWSITEM_NOTLOGGEDIN'] = '';



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
$skin['functions']['navigation_open'] = "<ul class='makeMenu'>";
$skin['functions']['navigation_close'] = "</ul>";
$skin['functions']['navigation_item_with_childs'] = "<li><a href='LINK'>TEXT</a><ul> CHILDS</ul></li> \n";
$skin['functions']['navigation_item_with_childs_nolink'] = "<li><span>TEXT</span><ul> CHILDS</ul></li> \n";
$skin['functions']['navigation_item_without_childs'] = "<li><a href='LINK'>TEXT</a></li> \n";
$skin['functions']['admin_modules_item_innavigator'] = "<tr><td>NAME</td><td>AUTHORIZED_ONLY</td><td>ADMIN_ONLY</td><td>LANGUAGE</td><td> INPUT (submit, NSUBMIT, TEXT_ADMIN_MODULES_VIEW_PAGE)</td><td> INPUT (submit, ADDPAGE, TEXT_ADMIN_MODULES_ADD_PAGE)</td><td> INPUT (submit, EDITPAGE, TEXT_EDIT_PAGE)</td><td> INPUT (submit, DELETEPAGE, TEXT_ADMIN_MODULES_DELETE_PAGE)</td><td> INPUT (submit, DELETEMODULE, TEXT_ADMIN_MODULES_DELETE_MODULE)</td><td>PARENT</td><tr><td colspan='9'>TEXT__CHI_LDS</td></tr><tr><td colspan='9'><div style='margin-left: 5em;'>CHILDS</div></td></tr></tr>";
$skin['functions']['admin_modules_item_notinnavigator'] = "<tr><td>NAME</td><td>AUTHORIZED_ONLY</td><td>ADMIN_ONLY</td><td>LANGUAGE</td><td> INPUT (submit, NSUBMIT, TEXT_ADMIN_MODULES_VIEW_PAGE)</td><td> INPUT (submit, ADDPAGE, TEXT_ADMIN_MODULES_ADD_PAGE)</td><td> INPUT (submit, EDITPAGE, TEXT_EDIT_PAGE)</td><td> INPUT (submit, DELETEPAGE, TEXT_ADMIN_MODULES_DELETE_PAGE)</td><td> INPUT (submit, DELETEMODULE, TEXT_ADMIN_MODULES_DELETE_MODULE)</td></tr>";
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
$skin['functions']['language_option'] = " <option value='LANGUAGE'>LANGUAGE</option>";
$skin['functions']['language_option_selected'] = " <option SELECTED value='LANGUAGE'>LANGUAGE</option>";
$skin['functions']['contentlanguage_option'] = " <option value='CONTENTLANGUAGE'>CONTENTLANGUAGE</option>";
$skin['functions']['contentlanguage_option_selected'] = " <option SELECTED value='CONTENTLANGUAGE'>CONTENTLANGUAGE</option>";
$skin['functions']['theme_option'] = " <option value='THEME'>THEME</option>";
$skin['functions']['theme_option_selected'] = " <option SELECTED value='THEME'>THEME</option>";
$skin['functions']['box'] = "<div class='box'><h3>TITLE</h3>CONTENT</div>";
$skin['functions']['admin_user_isadmin'] = "<input name='USERNAME' type='checkbox' CHECKED />";
$skin['functions']['admin_user_isnotadmin'] = "<input name='USERNAME' type='checkbox' />";
$skin['functions']['admin_user'] = "<tr><td>USERNAME</td><td>EMAIL</td><td>ISADMIN</td></tr>";
$skin['functions']['admin_extension_item'] = "<tr><td>NAME</td><td>STATUS</td><td>INSTALL</td></tr>";
$skin['functions']['admin_extension_status_ok'] = "&TEXT_EXTENSION_LOAD; <input type=\"checkbox\" name=\"NAME\" />";
$skin['functions']['admin_extension_status_loaded'] = "&TEXT_EXTENSION_LOAD; <input CHECKED type=\"checkbox\" name=\"NAME\" />";
$skin['functions']['admin_extension_status_missing_file'] = "&TEXT_EXTENSION_MISSING_FILE;";
$skin['functions']['admin_extension_status_incompatible'] = "&TEXT_EXTENSION_INCOMPATIBLE;";
$skin['functions']['admin_extension_status_not_installed'] = "&TEXT_EXTENSION_LOAD; <input type=\"checkbox\" name=\"NAME\" /> &TEXT_WARNING_INSTALL_EXTENSION_ON_LOAD;";
$skin['functions']['admin_extension_install'] = "<a href='INSTALLLINK'>TEXT_INSTALL_EXTENSION</a>";
$skin['functions']['admin_extension_uninstall'] = "<a href='UNINSTALLLINK'>TEXT_UNINSTALL_EXTENSION</a>";
$skin['functions']['latest_news_items_item'] = '<div class="newsitem"><h3>SUBJECT</h3>  &LINK_POST_COMMENT; (ONITEM,ONNEWS)<p>MESSAGE</p></div>';
$skin['functions']['postnews_option_topic'] = 'OPTION (TOPICNAME)';
$skin['functions']['link_post_comment_loggedin'] = '<p><a href=" TO_POSTNEW_COMMENT (ONITEM,ONNEWS) ">&TEXT_POST_COMMENT;</a></p>';
$skin['functions']['LINK_POST_COMMENT_NOTLOGGEDIN'] = '';
?>
