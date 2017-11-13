<?php
/**
 * Package!
 *
 * We want to get rid of source files and whatnot, and since they're
 * kinda all over the place, it is better to let a robot handle it.
 *
 * Dirty, dirty work.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

define('BUILD_DIR', dirname(__FILE__) . '/');
define('PLUGIN_BASE', dirname(BUILD_DIR) . '/bbg-common/');
define('RELEASE_BASE', BUILD_DIR . 'bbb-common/');
define('RELEASE_DIR', dirname(BUILD_DIR) . '/release/');



echo "\n";
echo "+ Copying the source.\n";

// Delete the release base if it already exists.
if (file_exists(RELEASE_BASE)) {
	shell_exec('rm -rf ' . escapeshellarg(RELEASE_BASE));
}

// Copy the trunk.
shell_exec('cp -aR ' . escapeshellarg(PLUGIN_BASE) . ' ' . escapeshellarg(RELEASE_BASE));



// Remove unneeded files.
echo "+ Cleaning the source.\n";
$files = array(
	RELEASE_BASE . 'Gruntfile.js',
	RELEASE_BASE . 'package.json',
	RELEASE_BASE . 'package-lock.json',
	RELEASE_BASE . 'node_modules/',
	RELEASE_BASE . '.sass-cache/',
	RELEASE_BASE . 'src/',
);
foreach ($files as $file) {
	if (file_exists($file)) {
		if (is_file($file)) {
			unlink($file);
		}
		elseif (is_dir($file)) {
			shell_exec('rm -rf ' . escapeshellarg($file));
		}
	}
}
shell_exec('find ' . escapeshellarg(RELEASE_BASE) . ' -name ".gitignore" -type f -delete');



// We can compress our libraries.
echo "+ Compressing files.\n";
$dir = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator(
		RELEASE_BASE . 'lib/',
		RecursiveDirectoryIterator::SKIP_DOTS
	)
);
foreach ($dir as $file) {
	if (preg_match('/\.php$/i', $file)) {
		@file_put_contents($file, php_strip_whitespace($file));
	}
}



// Permissions might be weird, particularly for Composer packages.
echo "+ Fixing permissions.\n";
shell_exec('find ' . escapeshellarg(RELEASE_BASE) . ' -type d -print0 | xargs -0 chmod 755');
shell_exec('find ' . escapeshellarg(RELEASE_BASE) . ' -type f -print0 | xargs -0 chmod 644');



if (class_exists('ZipArchive')) {
	// Last bit, let's make a zip!
	echo "+ Zipping package.\n";

	if (file_exists(RELEASE_DIR . 'bbg-common.zip')) {
		unlink(RELEASE_DIR . 'bbg-common.zip');
	}

	// Initialize archive object
	$zip = new ZipArchive();
	$zip->open(RELEASE_DIR . 'bbg-common.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

	// Loop it.
	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator(RELEASE_BASE),
		RecursiveIteratorIterator::LEAVES_ONLY
	);
	foreach ($files as $name=>$file) {
		// Skip directories (they would be added automatically).
		if (!$file->isDir()) {
			// Get real and relative path for current file.
			$full = $file->getRealPath();
			$relative = 'bbg-common/' . substr($full, strlen(RELEASE_BASE) - 1);

			// Add current file to archive.
			$zip->addFile($full, $relative);
		}
	}

	// And close.
	$zip->close();
	shell_exec('rm -rf ' . escapeshellarg(RELEASE_BASE));
}
else {
	echo "+ ZipArchive not configured; package will need to be manually zipped.\n";
}



echo "\nDone!.\n";