<?php
/**
 * BBG Common: Fallback
 *
 * This plugin is *not* compatible with the current environment. This
 * will print an admin notice and exit.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

/**
 * Do not execute this file directly.
 */
if (!defined('ABSPATH')) {
	exit;
}



// ---------------------------------------------------------------------
// Compatibility Checking
// ---------------------------------------------------------------------

// There will be errors. What are they?
$bbgcommon_errors = array();

if (version_compare(PHP_VERSION, '7.0.0') < 0) {
	$bbgcommon_errors['version'] = __('PHP 7.0.0 or newer is required.', 'bbg-common');
}

if (function_exists('is_multisite') && is_multisite()) {
	$bbgcommon_errors['multisite'] = __('This plugin cannot be used on Multi-Site.', 'bbg-common');
}

// Miscellaneous extensions.
foreach (array('date', 'filter', 'json', 'pcre') as $v) {
	if (!extension_loaded($v)) {
		$bbgcommon_errors[$v] = sprintf(
			__('This plugin requires the PHP extension %s.', 'bbg-common'),
			$v
		);
	}
}

if (!function_exists('hash_algos') || !in_array('sha512', hash_algos(), true)) {
	$bbgcommon_errors['hash'] = __('PHP must support basic hashing algorithms like SHA512.', 'bbg-common');
}

// --------------------------------------------------------------------- end compatibility



// ---------------------------------------------------------------------
// Functions
// ---------------------------------------------------------------------

/**
 * Admin Notice
 *
 * @return bool True/false.
 */
function bbgcommon_admin_notice() {
	global $bbgcommon_errors;

	if (!is_array($bbgcommon_errors) || !count($bbgcommon_errors)) {
		return false;
	}
	?>
	<div class="notice notice-error">
		<p><?php
		echo sprintf(
			esc_html__('Your server does not meet the requirements for running %s. You or your system administrator should take a look at the following:', 'bbg-common'),
			'<strong>BBG Common</strong>'
		);
		?></p>

		<?php
		foreach ($bbgcommon_errors as $error) {
			echo '<p>&nbsp;&nbsp;&mdash; ' . esc_html($error) . '</p>';
		}
		?>
	</div>
	<?php
	return true;
}
add_action('admin_notices', 'bbgcommon_admin_notice');

/**
 * Self-Deactivate
 *
 * If the environment can't support the plugin and the environment never
 * supported the plugin, simply remove it.
 *
 * @return bool True/false.
 */
function bbgcommon_deactivate() {
	// Can't deactivate an MU plugin.
	if (BBGCOMMON_MUST_USE) {
		return false;
	}

	require_once(trailingslashit(ABSPATH) . 'wp-admin/includes/plugin.php');
	deactivate_plugins(BBGCOMMON_INDEX);

	global $bbgcommon_errors;
	$bbgcommon_errors['disabled'] = __('The plugin has been automatically disabled.', 'bbg-common');

	if (isset($_GET['activate'])) {
		unset($_GET['activate']);
	}

	return true;
}
add_action('admin_init', 'bbgcommon_deactivate');

/**
 * Localize
 *
 * @return void Nothing.
 */
function bbgcommon_localize() {
	if (BBGCOMMON_MUST_USE) {
		load_muplugin_textdomain('bbg-common', basename(BBGCOMMON_PLUGIN_DIR) . '/languages');
	}
	else {
		load_plugin_textdomain('bbg-common', false, basename(BBGCOMMON_PLUGIN_DIR) . '/languages');
	}
}
add_action('plugins_loaded', 'bbgcommon_localize');

// --------------------------------------------------------------------- end functions
