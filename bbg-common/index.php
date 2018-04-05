<?php
/**
 * Various tools for a happy and effective WordPress theme.
 *
 * @package BBG Common
 * @version 0.8.2
 *
 * @wordpress-plugin
 * Plugin Name: BBG Common
 * Version: 0.8.2
 * Plugin URI: https://github.com/brightbrightgreat/bbg-common
 * Description: Various tools for a happy and effective WordPress theme.
 * Text Domain: bbg-common
 * Domain Path: /languages/
 * Info URI: https://brightbrightgreat.com/repo/bbg-common.json
 * Author: Bright Bright Great
 * Author URI: https://brightbrightgreat.com/
 * License: WTFPL
 * License URI: http://www.wtfpl.net
 */

/**
 * Do not execute this file directly.
 */
if (!defined('ABSPATH')) {
	exit;
}

// ---------------------------------------------------------------------
// Setup
// ---------------------------------------------------------------------

// Constants.
define('BBGCOMMON_PLUGIN_DIR', dirname(__FILE__) . '/');
define('BBGCOMMON_INDEX', __FILE__);
define('BBGCOMMON_BASE_CLASS', 'bbg\\wp\\common\\');
define('BBG_TESTMODE', false !== strpos(site_url(), '.brightbrightgreat.com'));

// Is this installed as a Must-Use plugin?
$bbgcommon_must_use = (
	defined('WPMU_PLUGIN_DIR') &&
	@is_dir(WPMU_PLUGIN_DIR) &&
	(0 === strpos(BBGCOMMON_PLUGIN_DIR, WPMU_PLUGIN_DIR))
);
define('BBGCOMMON_MUST_USE', $bbgcommon_must_use);

// Now the URL root.
if (!BBGCOMMON_MUST_USE) {
	define('BBGCOMMON_PLUGIN_URL', preg_replace('/^https?:/i', '', trailingslashit(plugins_url('/', BBGCOMMON_INDEX))));
}
else {
	define('BBGCOMMON_PLUGIN_URL', preg_replace('/^https?:/i', '', trailingslashit(str_replace(WPMU_PLUGIN_DIR, WPMU_PLUGIN_URL, BBGCOMMON_PLUGIN_DIR))));
}

// --------------------------------------------------------------------- end setup



// ---------------------------------------------------------------------
// Requirements
// ---------------------------------------------------------------------

// If the server doesn't meet the requirements, load the fallback
// instead.
if (
	version_compare(PHP_VERSION, '7.0.0') < 0 ||
	(function_exists('is_multisite') && is_multisite()) ||
	(!function_exists('hash_algos') || !in_array('sha512', hash_algos(), true)) ||
	!extension_loaded('date') ||
	!extension_loaded('filter') ||
	!extension_loaded('json') ||
	!extension_loaded('pcre')
) {
	require(BBGCOMMON_PLUGIN_DIR . 'bootstrap-fallback.php');
	return;
}

// --------------------------------------------------------------------- end requirements



// Otherwise we can continue as normal!
require(BBGCOMMON_PLUGIN_DIR . 'bootstrap.php');
