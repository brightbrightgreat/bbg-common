<?php
/**
 * Compile Plugin
 *
 * This will update dependencies, optimize the autoloader, and
 * optionally generate a new release zip.
 *
 * @package blob-wp
 * @author  Blobfolio, LLC <hello@blobfolio.com>
 */

namespace bbg\dev;

use \blobfolio\bob\io;

class plugin extends \blobfolio\bob\base\mike_wp {
	// Project Name.
	const NAME = 'bbg-common';
	const DESCRIPTION = 'bbg-common is a WordPress plugin containing various tools for a happy and effective WordPress theme.';
	const CONFIRMATION = '';
	const SLUG = 'bbg-common';

	const RELEASE_COMPRESS = array('lib/');



	/**
	 * Overload: Patch Version
	 *
	 * @return void Nothing.
	 */
	protected static function patch_version() {
		// Patch the base hook cache-break version.
		$file = static::get_plugin_dir() . 'lib/bbg/wp/common/base/hook.php';
		$content = file_get_contents($file);
		$content = preg_replace(
			"/const ASSET_VERSION = '([^']*)'/",
			"const ASSET_VERSION = '" . static::$_version . "'",
			$content
		);
		file_put_contents($file, $content);
	}

	/**
	 * Get Shitlist
	 *
	 * @return array Shitlist.
	 */
	protected static function get_shitlist() {
		$shitlist = io::SHITLIST;

		// Exclude the source directory.
		$shitlist[] = '#^' . preg_quote(static::get_plugin_dir() . 'src', '$#') . '#';

		// Exclude the working copy's source too, if defined.
		if (isset(static::$_working_dir)) {
			$shitlist[] = '#^' . preg_quote(static::$_working_dir . 'src', '$#') . '#';
		}

		return $shitlist;
	}
}
