<?php
/* Site statistics is an extension for MorgOS to have site statistics.
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

$extension['general']['name'] = 'Site statistics';
$extension['general']['version'] = '0.1';
$extension['general']['maxversion'] = '0.1';
$extension['general']['minversion'] = '0.1';
$extension['general']['ID'] = '{9786-5432-1098-7654}';
$extension['required_file'][] = 'index.php';
$extension['need_install'] = true;
$extension['is_installed_function'] = 'statisticsIsInstalled';
$extension['install_function'] = 'statisticsInstall';
$extension['uninstall_function'] = 'statisticsUnInstall';
$extension['file_to_load'] = 'index.php';
$extension['file_to_install'] = 'install.php';

if (! function_exists ('statisticsIsInstalled')) {
	function statisticsIsInstalled ($genDB) {
		$result = $genDB->query ('SHOW COLUMNS FROM ' . TBL_PAGES);
		while ($column = $genDB->fetch_array ($result)) {
			if ($column['Field'] == 'pageViews') {
				return true;
			}
		}
		return false;
	}
	
	function statisticsInstall ($genDB, $pages) {
		$genDB->query ('ALTER TABLE ' . TBL_PAGES . ' ADD pageViews int(10)');
		$pages->addModule ('view_statistics', false, false, 2, 0, true, 'index', true, '{9786-5432-1098-7654}');
		$pages->addPage ('view_statistics', 'english', 'View statistics', 'View all statitics for this website.');
	}
	
	function statisticsUnInstall ($genDB, $pages) {
		$result = $genDB->query ('ALTER TABLE ' . TBL_PAGES . ' DROP COLUMN pageViews');
	}
}
?>
