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

use \bbg\dev\plugin;

require(__DIR__ . '/lib/vendor/autoload.php');

// Set up some quick constants, namely for path awareness.
define('BBGCOMMON_BUILD_DIR', __DIR__ . '/');
define('BBGCOMMON_SOURCE_DIR', dirname(BBGCOMMON_BUILD_DIR) . '/bbg-common/');
define('BBGCOMMON_RELEASE_DIR', dirname(BBGCOMMON_BUILD_DIR) . '/release/');
define('BBGCOMMON_COMPOSER_CONFIG', BBGCOMMON_BUILD_DIR . 'skel/composer.json');
define('BBGCOMMON_PHPAB_AUTOLOADER', BBGCOMMON_SOURCE_DIR . 'lib/autoload.php');
define('BBGCOMMON_SHITLIST', array(
	'#^' . preg_quote(BBGCOMMON_SOURCE_DIR . 'src/', '#') . '#',
));

// Compilation is as easy as calling this method!
plugin::compile();

// We're done!
exit(0);
