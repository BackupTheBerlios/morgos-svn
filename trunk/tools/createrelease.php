#! /usr/bin/php
<?php
   function recursiveRemoveDirectory($path)
   {   
       $dir = new RecursiveDirectoryIterator($path);

       //Remove all files
       foreach(new RecursiveIteratorIterator($dir) as $file)
       {
           unlink($file);
       }

       //Remove all subdirectories
       foreach($dir as $subDir)
       {
           //If a subdirectory can't be removed, it's because it has subdirectories, so recursiveRemoveDirectory is called again passing the subdirectory as path
           if(!@rmdir($subDir)) //@ suppress the warning message
           {
               recursiveRemoveDirectory($subDir);
           }
       }

       //Remove main directory
       rmdir($path);
   }
?>
<?php
	$tempDir = 'tempdir';
	$defaultSkins = array ('default');
	$defaultPlugins = array ();
	$extraSkins = array ();
	$extraPlugins = array ();
	$_ARGS = $_SERVER['argv'];
	unset ($_ARGS[0]);
	$version = $_ARGS[1];
	
	foreach ($_ARGS as $arg) {
		if (ereg ('^--skins=', $arg)) {
			$extraSkins = explode (',', substr ($arg, strlen ('--skins=')));
		} elseif (ereg ('^--plugins=', $arg)) {
			$extraPlugins = explode (',', substr ($arg, strlen ('--plugins=')));
		}
	}		
	
	$r = `svn export ../ $tempDir`;
	
	$includedSkins = array_merge ($defaultSkins, $extraSkins);
	$includedPlugins = array_merge ($defaultPlugins, $extraPlugins);
	
	$allSkins = scandir ($tempDir.'/skins');
	
	foreach ($allSkins as $skin) {
		if ($skin[0] == '.') continue;
		if (! in_array ($skin, $includedSkins)) {
			echo "Deleting $skin \n";
			recursiveRemoveDirectory ($tempDir.'/skins/'.$skin);
		} else {
			echo "Including $skin \n";
		}
	}
	
	$allPlugins = scandir ($tempDir.'/plugins');
	foreach ($allPlugins as $plugin) {
		if ($plugin[0] == '.') continue;
		if (! in_array ($plugin, $includedPlugins)) {
			echo "Deleting $plugin \n";
			recursiveRemoveDirectory ($tempDir.'/plugins/'.$plugin);
		} else {
			echo "Including $plugin \n";
		}
	}
	
	unlink ($tempDir.'/reinstalldb.php');
	unlink ($tempDir.'/tools/createrelease.php');
	$curDir = getcwd ();
	chdir ($tempDir);
	$r=`tar -czf $curDir/morgos-$version.tar.gz *`;	
	chdir ($curDir);
	recursiveRemoveDirectory ($tempDir);
	echo "Writing file to morgos-$version.tar.gz\n";
?>