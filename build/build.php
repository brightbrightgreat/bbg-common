<?php
/**
 * Rebuild Plugin
 *
 * This will update dependencies, optimize the autoloader, and
 * optionally generate a new release zip.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

// ---------------------------------------------------------------------
// Config
// ---------------------------------------------------------------------

$start = microtime(true);
clearstatcache();

// A few paths we'll need.
define('BUILD_DIR', dirname(__FILE__) . '/');
define('PLUGIN_DIR', dirname(BUILD_DIR) . '/bbg-common/');
define('RELEASE_DIR', dirname(BUILD_DIR) . '/release/');
define('RELEASE_ZIP', RELEASE_DIR . 'bbg-common.zip');
define('VENDOR_DIR', PLUGIN_DIR . 'lib/vendor/');
define('WORKING_DIR', BUILD_DIR . 'bbg-common/');

// A few libraries we'll need.
define('DEPENDENCIES', array(
	'composer'=>array(
		'remote'=>'https://getcomposer.org/composer.phar',
		'local'=>PLUGIN_DIR . 'composer.phar',
		'chmod'=>0755,
	),
	'phpab'=>array(
		'remote'=>'https://github.com/theseer/Autoload/releases/download/1.24.1/phpab-1.24.1.phar',
		'local'=>PLUGIN_DIR . 'phpab.phar',
		'chmod'=>0755,
	),
	'blob-common'=>array(
		'remote'=>'https://github.com/Blobfolio/blob-common/raw/master/bin/blob-common.phar',
		'local'=>BUILD_DIR . 'blob-common.phar',
		'chmod'=>0644,
	),
));

// Development files/directories to remove when building a release.
define('DEV_FILES', array(
	PLUGIN_DIR . 'Gruntfile.js',
	PLUGIN_DIR . 'package.json',
	PLUGIN_DIR . 'package-lock.json',
	PLUGIN_DIR . 'node_modules',
	PLUGIN_DIR . '.sass-cache',
	PLUGIN_DIR . 'src',
));

// Some commands need their CWD to be set to the plugin base; this will
// save some typing later.
define('FROM_PLUGIN_DIR', 'cd ' . escapeshellarg(PLUGIN_DIR) . ' && ');

// --------------------------------------------------------------------- end config



/**
 * An output helper.
 *
 * @param string $line Line.
 * @param string $style Special styling, if any.
 * @return void Nothing.
 */
function report(string $line, string $style='') {
	switch ($style) {
		case 'bullet':
			$line = "   \033[90m++\033[0m $line";
			break;
		case 'header':
			$divider = "\033[90m" . str_repeat('-', 50) . "\033[0m";
			$line = "\n$divider\n\033[34;1m$line\033[0m\n$divider";
			break;
		case 'error':
			$line = "\033[31;1mError:\033[0m $line";
			break;
		case 'warning':
			$line = "\033[33;1mWarning:\033[0m $line";
			break;
		case 'success':
			$line = "\033[32;1mSuccess:\033[0m $line";
			break;
	}

	echo "$line\n";

	if ('error' === $style) {
		exit(1);
	}
}

/**
 * Recursive Copy
 *
 * @param string $src Source path.
 * @param string $dst Destination Path.
 * @return void Nothing.
 */
function rcopy($src, $dst) {
	if (
		in_array($src, DEV_FILES, true) ||
		(false !== strpos('DS_Store', $src)) ||
		preg_match('/\.(gitignore|htaccess)$/', $src)
	) {
		return;
	}

	// Recurse for directories.
	if (is_dir($src)) {
		// Delete the destination if it exists.
		if (is_dir($dst)) {
			\blobfolio\common\file::rmdir($dst);
		}

		// And remake it.
		mkdir($dst, 0755);

		// Make sure it has a trailing slash.
		$src = rtrim($src, '/') . '/';
		$dst = rtrim($dst, '/') . '/';

		$files = scandir($src);
		foreach ($files as $file) {
			if ('.' !== $file && '..' !== $file) {
				rcopy("{$src}{$file}", "{$dst}{$file}");
			}
		}
	}
	// Just use the native copy for files.
	else if (is_file($src)) {
		copy($src, $dst);
		chmod($src, 0644);
	}
}



// ---------------------------------------------------------------------
// Early Errors
// ---------------------------------------------------------------------

// This has to be run in CLI mode.
if ('cli' !== php_sapi_name()) {
	report('This script must be run in CLI mode.', 'error');
}

// Make sure these exist.
if (!is_dir(PLUGIN_DIR)) {
	report('Missing plugin directory.', 'error');
}

if (!is_dir(VENDOR_DIR)) {
	report('Missing vendor directory.', 'error');
}

if (!is_dir(PLUGIN_DIR . 'node_modules/')) {
	report('Grunt not installed; run "npm i" first.', 'error');
}

// --------------------------------------------------------------------- end early errors



// ---------------------------------------------------------------------
// Build/Update
// ---------------------------------------------------------------------

// Fetch dependencies.
report('Fetching Runtime Dependencies…', 'header');
foreach (DEPENDENCIES as $k=>$v) {
	if (is_file($v['local'])) {
		report("$k: Removing old copy…", 'bullet');
		unlink($v['local']);
	}

	report("$k: Downloading…", 'bullet');
	file_put_contents($v['local'], file_get_contents($v['remote']));
	if (!is_file($v['local'])) {
		report("Could not save $k.", 'error');
	}
	chmod($v['local'], $v['chmod']);
}



// Include blob-common so we have its functions at our disposal.
use \blobfolio\common;
include(DEPENDENCIES['blob-common']['local']);



// Run Composer tasks.
report('Updating Project…', 'header');

// Delete the vendor directory if it exists.
if (is_dir(VENDOR_DIR)) {
	report('Composer: Removing old "vendor" directory…', 'bullet');
	common\file::rmdir(VENDOR_DIR);
}

report('Composer: Updating libraries…', 'bullet');
// Copy the composer configuration.
copy(BUILD_DIR . 'composer.json', PLUGIN_DIR . 'composer.json');
shell_exec(FROM_PLUGIN_DIR . escapeshellcmd(DEPENDENCIES['composer']['local']) . ' install --no-dev -q');



// Run Grunt tasks.
report('Grunt: Tidying up…', 'bullet');
shell_exec(FROM_PLUGIN_DIR . ' grunt build');



// Run PHPAB tasks.
report('PHPAB: Generating class autoloader…', 'bullet');
shell_exec(
	escapeshellcmd(DEPENDENCIES['phpab']['local']) . ' -e "' . PLUGIN_DIR . 'node_modules/*" -n --tolerant -o ' . PLUGIN_DIR . 'lib/autoload.php ' . PLUGIN_DIR
);



// Final clean up.
report('Garbage collection…', 'header');

report("composer: Removing library…", 'bullet');
unlink(DEPENDENCIES['composer']['local']);
report("phpab: Removing library…", 'bullet');
unlink(DEPENDENCIES['phpab']['local']);

// --------------------------------------------------------------------- end build



// ---------------------------------------------------------------------
// Release?
// ---------------------------------------------------------------------

// Find the current version, if any.
$version = file_get_contents(PLUGIN_DIR . 'index.php');
if (preg_match('/@version (\d+\.\d+\.\d+)/', $version, $match)) {
	$version = " ({$match[1]})";
}
else {
	$version = '';
}

report("New Release?{$version}", 'header');
report('Enter a version number to build a new release, or hit <enter> to skip.', 'bullet');
if ($handle = fopen ("php://stdin","r")) {
	$version = fgets($handle);
	$version = preg_replace('/[^\d\.]/', '', $version);
	if ($version) {
		// Remove old version if necessary.
		if (is_dir(WORKING_DIR)) {
			report('Removing old working directory…', 'bullet');
			common\file::rmdir(WORKING_DIR);
		}

		report('Patching version…', 'bullet');

		// Patch index.
		$file = file_get_contents(PLUGIN_DIR . 'index.php');
		$file = preg_replace('/@version (\d+\.\d+\.\d+)/', "@version $version", $file);
		$file = preg_replace('/\* Version: (\d+\.\d+\.\d+)/', "* Version: $version", $file);
		file_put_contents(PLUGIN_DIR . 'index.php', $file);

		// Patch base hook.
		$file = file_get_contents(PLUGIN_DIR . 'lib/bbg/wp/common/base/hook.php');
		$file = preg_replace("/const ASSET_VERSION = '([^']*)'/", "const ASSET_VERSION = '$version'", $file);
		file_put_contents(PLUGIN_DIR . 'lib/bbg/wp/common/base/hook.php', $file);

		// Patch release JSON.
		$file = trim(file_get_contents(RELEASE_DIR . 'bbg-common.json'));
		$file = json_decode($file, true);
		$file['Version'] = $version;
		$file = json_encode($file, JSON_PRETTY_PRINT);
		file_put_contents(RELEASE_DIR . 'bbg-common.json', $file);

		// Copy files to working directory.
		report('Copying source…', 'bullet');
		mkdir(WORKING_DIR, 0755);
		rcopy(PLUGIN_DIR, WORKING_DIR);

		// Strip whitespace from PHP libs.
		report('Compressing files…', 'bullet');
		$dir = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				WORKING_DIR . 'lib/',
				RecursiveDirectoryIterator::SKIP_DOTS
			)
		);
		foreach ($dir as $file) {
			if (preg_match('/\.php$/i', $file)) {
				@file_put_contents($file, php_strip_whitespace($file));
			}
		}

		// Make sure permissions make sense for files and directories.
		report('Fixing permissions…', 'bullet');
		shell_exec('find ' . escapeshellarg(WORKING_DIR) . ' -type d -print0 | xargs -0 chmod 755');
		shell_exec('find ' . escapeshellarg(WORKING_DIR) . ' -type f -print0 | xargs -0 chmod 644');

		// And zip it all up if possible.
		if (class_exists('ZipArchive')) {
			report('Building package…', 'bullet');

			if (file_exists(RELEASE_ZIP)) {
				unlink(RELEASE_ZIP);
			}

			// Initialize archive object
			$zip = new ZipArchive();
			$zip->open(RELEASE_ZIP, ZipArchive::CREATE | ZipArchive::OVERWRITE);

			// Loop it.
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(WORKING_DIR),
				RecursiveIteratorIterator::LEAVES_ONLY
			);
			foreach ($files as $name=>$file) {
				// Skip directories (they would be added automatically).
				if (!$file->isDir()) {
					// Get real and relative path for current file.
					$full = $file->getRealPath();
					$relative = 'bbg-common' . substr($full, strlen(WORKING_DIR) - 1);

					// Add current file to archive.
					$zip->addFile($full, $relative);
				}
			}

			// And close.
			$zip->close();

			report('Tidying up…', 'bullet');
			common\file::rmdir(WORKING_DIR);
		}
		else {
			report('ZipArchive is not configured; the package will need to be manually zipped.', 'warning');
		}
	}
	else {
		report('Skipping release.', 'bullet');
	}
}
else {
	report('Could not read input.', 'warning');
}


// --------------------------------------------------------------------- end release



// We're done!
$end = microtime(true);
$elapsed = round($end - $start, 3);
report('Done!', 'header');
report("Finished in $elapsed seconds.", 'success');
echo "\n";

exit(0);
