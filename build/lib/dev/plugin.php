<?php
/**
 * Compile Plugin
 *
 * This will update dependencies, optimize the autoloader, and
 * optionally generate a new release zip.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\dev;

class plugin extends \blobfolio\bob\base\build_wp {
	const NAME = 'bbg-common';

	// Various file paths.
	const SOURCE_DIR = BBGCOMMON_SOURCE_DIR;
	const COMPOSER_CONFIG = BBGCOMMON_COMPOSER_CONFIG;
	const GRUNT_TASK = 'build';
	const PHPAB_AUTOLOADER = BBGCOMMON_PHPAB_AUTOLOADER;
	const SHITLIST = BBGCOMMON_SHITLIST;

	// Release info.
	const RELEASE_OUT = BBGCOMMON_RELEASE_DIR . '/bbg-common.zip';
	const RELEASE_COMPRESS = array(
		'%TMP%lib/',
	);
	const RELEASE_ZIP_SUBDIR = 'bbg-common/';

	// There are no file dependencies.
	const SKIP_FILE_DEPENDENCIES = true;



	/**
	 * Patch Version
	 *
	 * @param string $version Version.
	 * @return void Nothing.
	 */
	protected static function patch_version(string $version) {
		// Patch the base hook cache-break version.
		$file = static::SOURCE_DIR . 'lib/bbg/wp/common/base/hook.php';
		$content = file_get_contents($file);
		$content = preg_replace("/const ASSET_VERSION = '([^']*)'/", "const ASSET_VERSION = '$version'", $content);
		file_put_contents($file, $content);
	}
}
