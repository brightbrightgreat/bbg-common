<?php
/**
 * BBG Common: Upgrade
 *
 * This file helps WordPress detect and upgrade to new releases of this
 * plugin.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

class upgrade {

	// The full WP slug for the plugin.
	const PLUGIN_PATH = 'bbg-common/index.php';

	// Cached plugin data.
	protected static $_plugin;

	// Keep track of initialization (run just once).
	protected static $_init = false;



	// -----------------------------------------------------------------
	// Setup
	// -----------------------------------------------------------------

	/**
	 * Init
	 *
	 * Bind our AJAX callbacks.
	 *
	 * @return bool True.
	 */
	public static function init() {
		// Just run this once.
		if (static::$_init) {
			return true;
		}
		static::$_init = true;

		// Bind our actions.
		$class = get_called_class();

		// Enqueue update handlers first as this might exit before
		// reaching the end.
		if (BBGCOMMON_MUST_USE) {
			// Must-Use doesn't have normal version management, but we
			// can add filters for Musty in case someone's using that.
			add_filter('musty_download_version_' . static::PLUGIN_PATH, array($class, 'musty_download_version'));
			add_filter('musty_download_uri_' . static::PLUGIN_PATH, array($class, 'musty_download_uri'));
		}
		else {
			// Normal plugins are... more normal.
			add_filter('transient_update_plugins', array($class, 'update_plugins'));
			add_filter('site_transient_update_plugins', array($class, 'update_plugins'));
		}

		return true;
	}

	// ----------------------------------------------------------------- end setup



	// -----------------------------------------------------------------
	// Update Checking
	// -----------------------------------------------------------------

	/**
	 * Release Info
	 *
	 * Pull what we can from the local plugin, grab the rest from its
	 * source.
	 *
	 * @param string $key Key.
	 * @return mixed Details, detail, false.
	 */
	public static function release_info($key=null) {
		if (is_null(static::$_plugin)) {
			require_once(trailingslashit(ABSPATH) . 'wp-admin/includes/plugin.php');

			// Start by pulling details from the header.
			static::$_plugin = get_plugin_data(BBGCOMMON_INDEX, false, false);

			// Unfortunately that function lacks a filter, so we need
			// one more call to get the remote URI.
			$extra = get_file_data(
				BBGCOMMON_INDEX,
				array('InfoURI'=>'Info URI'),
				'plugin'
			);
			static::$_plugin['InfoURI'] = $extra['InfoURI'];
			static::$_plugin['DownloadVersion'] = '';
			static::$_plugin['DownloadURI'] = '';

			// Now grab the remote info, if applicable. Cache it a bit
			// to save round trips.
			$transient_key = 'bbgcommon_' . md5(BBGCOMMON_INDEX . static::$_plugin['InfoURI']);
			if (false === ($response = get_transient($transient_key))) {
				$response = wp_remote_get(static::$_plugin['InfoURI']);
				if (200 === wp_remote_retrieve_response_code($response)) {
					$response = wp_remote_retrieve_body($response);
					$response = json_decode($response, true);
					if (is_array($response)) {
						set_transient($transient_key, $response, 7200);
					}
				}
			}

			if (
				is_array($response) &&
				isset($response['Version']) &&
				isset($response['DownloadURI'])
			) {
				static::$_plugin['DownloadVersion'] = $response['Version'];
				static::$_plugin['DownloadURI'] = $response['DownloadURI'];
			}
		}

		if (!is_null($key)) {
			return array_key_exists($key, static::$_plugin) ? static::$_plugin[$key] : false;
		}

		return static::$_plugin;
	}

	/**
	 * Musty Callback: Download Version
	 *
	 * MU plugins aren't part of the usual version management. We'll
	 * pass the info to the WP-CLI plugin Musty in case someone is using
	 * that.
	 *
	 * @param string $version Version.
	 * @return string $version Version.
	 */
	public static function musty_download_version($version='') {
		$version = static::release_info('DownloadVersion');
		if (!$version) {
			$version = '';
		}

		return $version;
	}

	/**
	 * Musty Callback: Download URI
	 *
	 * MU plugins aren't part of the usual version management. We'll
	 * pass the info to the WP-CLI plugin Musty in case someone is using
	 * that.
	 *
	 * @param string $uri URI.
	 * @return string $uri URI.
	 */
	public static function musty_download_uri($uri='') {
		$uri = static::release_info('DownloadURI');
		if (!$uri) {
			$uri = '';
		}

		return $uri;
	}

	/**
	 * Update Check
	 *
	 * Inject bbg-common into the updateable plugin object if there's an
	 * update available so WP can do its thing.
	 *
	 * @param object $updates Updates.
	 * @return object $updates Updates.
	 */
	public static function update_plugins($updates) {
		// Needs to make sense.
		if (!is_object($updates) || !isset($updates->response)) {
			return $updates;
		}

		$me = static::release_info();

		if (
			!$me['Version'] ||
			!$me['DownloadVersion'] ||
			!$me['DownloadURI'] ||
			version_compare($me['Version'], $me['DownloadVersion']) >= 0
		) {
			return $updates;
		}

		$updates->response[static::PLUGIN_PATH] = new \stdClass();
		$updates->response[static::PLUGIN_PATH]->id = 0;
		$updates->response[static::PLUGIN_PATH]->new_version = $me['DownloadVersion'];
		$updates->response[static::PLUGIN_PATH]->package = $me['DownloadURI'];
		$updates->response[static::PLUGIN_PATH]->plugin = static::PLUGIN_PATH;
		$updates->response[static::PLUGIN_PATH]->slug = $me['TextDomain'];
		$updates->response[static::PLUGIN_PATH]->url = $me['PluginURI'];

		return $updates;
	}

	// ----------------------------------------------------------------- end updates
}
