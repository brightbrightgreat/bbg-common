<?php
/**
 * BBG Common: Bootstrap
 *
 * Life is good. Enqueue what needs enqueueing to get this party
 * started.
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

// Bootstrap.
require(BBGCOMMON_PLUGIN_DIR . 'lib/autoload.php');

// Bind all of our actions and filters once all site plugins have
// loaded (gotta wait to make sure our dependencies are present).
add_action('plugins_loaded', array(BBGCOMMON_BASE_CLASS . 'hook', 'init'));
add_action('plugins_loaded', array(BBGCOMMON_BASE_CLASS . 'ajax', 'init'));
add_action('plugins_loaded', array(BBGCOMMON_BASE_CLASS . 'upgrade', 'init'));
add_action('plugins_loaded', array(BBGCOMMON_BASE_CLASS . 'base\\partial', 'init'));
add_action('plugins_loaded', array(BBGCOMMON_BASE_CLASS . 'fields', 'init'));
add_action('plugins_loaded', array(BBGCOMMON_BASE_CLASS . 'svg', 'init'));
add_action('plugins_loaded', array(BBGCOMMON_BASE_CLASS . 'typetax', 'init'));
add_action('plugins_loaded', array(BBGCOMMON_BASE_CLASS . 'meta', 'init'));
