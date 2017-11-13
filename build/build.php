<?php
/**
 * Rebuild Dependencies
 *
 * Composer's autoloader is fine for development use, but can cause
 * performance and collision problems in production. This build script
 * should be run any time new classes are added or old ones are updated.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

// A few paths we'll need.
define('BUILD_DIR', dirname(__FILE__) . '/');
define('PLUGIN_BASE', dirname(BUILD_DIR) . '/bbg-common/');
define('VENDOR_BASE', PLUGIN_BASE . 'lib/vendor/');

// A few files we'll need.
define('COMPOSER', PLUGIN_BASE . 'composer.phar');
define('PHPAB', PLUGIN_BASE . 'phpab.phar');
define('BLOBCOMMON', BUILD_DIR . 'blob-common.phar');

// Some commands need their CWD to be set to the plugin base; this will
// save some typing later.
$from_plugindir = 'cd ' . escapeshellarg(PLUGIN_BASE) . ' && ';



/**
 * Build Cleanup
 *
 * Delete the files we've downloaded.
 *
 * @return void Nothing.
 */
function build_cleanup() {
	$files = array(COMPOSER, PHPAB, BLOBCOMMON);
	foreach ($files as $f) {
		if (file_exists($f)) {
			unlink($f);
		}
	}
}



// Grunt might need to be configured first.
if (!file_exists(PLUGIN_BASE . 'node_modules')) {
	echo "\n";
	echo "+ Configuring Grunt.\n";

	shell_exec(
		$from_plugindir . ' npm i'
	);
}



// Download the packages we'll need, just in case they aren't already
// on the system or accessible via PHP's user.
echo "\n";
echo "+ Grabbing Composer, phpab, and blob-common.\n";



// Download the three files.
file_put_contents(COMPOSER, file_get_contents('https://getcomposer.org/composer.phar'));
file_put_contents(PHPAB, file_get_contents('https://github.com/theseer/Autoload/releases/download/1.24.1/phpab-1.24.1.phar'));
file_put_contents(BLOBCOMMON, file_get_contents('https://github.com/Blobfolio/blob-common/raw/master/bin/blob-common.phar'));

// Adjust their permissions.
chmod(COMPOSER, 0755);
chmod(PHPAB, 0755);
chmod(BLOBCOMMON, 0644);



// Include blob-common so we have its functions at our disposal.
use \blobfolio\common;
include(BLOBCOMMON);



// Fetch composer dependencies and recompile the class autoloader.
echo "\n";
echo "+ Rebuilding dependencies.\n";



// Delete the vendor directory so we can start clean.
common\file::rmdir(VENDOR_BASE);

// Copy composer to the plugin base.
copy(BUILD_DIR . 'composer.json', PLUGIN_BASE . 'composer.json');

// Run the installer.
shell_exec(
	$from_plugindir . escapeshellcmd(COMPOSER) . ' install --no-dev -q'
);

// Run grunt clean.
shell_exec(
	$from_plugindir . 'grunt clean'
);

// Run phpab to give us a better autoload file.
shell_exec(
	escapeshellcmd(PHPAB) . ' -e "' . PLUGIN_BASE . 'node_modules/*" -n --tolerant -o ' . PLUGIN_BASE . 'lib/autoload.php ' . PLUGIN_BASE
);



echo "\n";
echo "+ Clean up.\n";

// Remove the binaries we downloaded.
build_cleanup();



echo "\n----\nDone\n";
exit(0);
