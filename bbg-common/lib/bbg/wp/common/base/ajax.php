<?php
/**
 * BBG Common: AJAX
 *
 * This file includes AJAX handlers and Nonce validation/generation.
 *
 * Program error: 500
 * Bad request: 400
 * Invalid/missing authorization: 401
 * Authorized but not allowed: 403
 * Rate limiting: 429
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common\base;

use \blobfolio\common\data;
use \blobfolio\common\ref\sanitize as r_sanitize;
use \blobfolio\common\ref\cast as r_cast;

abstract class ajax {
	// This is the prefix applied to AJAX actions.
	const PREFIX = 'bbg_common_ajax_';

	// These are the actions offered by the plugin. The callback name
	// should correspond to the action, minus the prefix. The value
	// indicates whether the method is public or only available to
	// WP users.
	const ACTIONS = array();

	// Nonce setup.
	const NONCE_SALT = 'bbg-common';
	const NONCE_COOKIE = 'bbg_common_n';

	// Response template.
	const RESPONSE = array(
		'data'=>array(),
		'errors'=>array(),
		'message'=>'',
		'session'=>array(
			'n'=>'',
		),
		'status'=>200,
	);

	// Keep track of initialization (run just once).
	protected static $_init = array();



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
		// Don't need to calculate this a thousand times.
		$class = get_called_class();

		// Don't need to do this twice.
		if (isset(static::$_init[$class])) {
			return true;
		}
		static::$_init[$class] = true;

		foreach (static::ACTIONS as $action=>$public) {
			// WP user binding.
			add_action(
				'wp_ajax_' . static::PREFIX . $action,
				array($class, $action)
			);

			// Public binding (if applicable).
			if ($public) {
				add_action(
					'wp_ajax_nopriv_' . static::PREFIX . $action,
					array($class, $action)
				);
			}
		}

		return true;
	}

	/**
	 * Parse Request
	 *
	 * A generic action to sanitize $_POST and do some global error
	 * checking.
	 *
	 * @param array $data Data.
	 * @param bool $nonce Check Nonce.
	 * @return array Response.
	 */
	protected static function parse(&$data, $nonce=true) {
		r_cast::array($data);
		$data = stripslashes_deep($data);
		r_sanitize::printable($data);
		$out = static::RESPONSE;

		// Check Nonce?
		if ($nonce) {
			if (!isset($data['n']) || !wp_verify_nonce($data['n'], static::NONCE_SALT)) {
				$out['errors']['other'] = 'The form had expired. Please try again.';
				$out['status'] = 400;
			}
		}

		return $out;
	}

	/**
	 * Parse Admin Request
	 *
	 * Same as above, but for admin-only forms.
	 *
	 * @param array $data Data.
	 * @param string $privilege Privilege.
	 * @return array Response.
	 */
	protected static function parse_admin(&$data, $privilege='manage_options') {
		$out = static::parse($data);

		// Check WP user privilege.
		if (!current_user_can($privilege)) {
			$out['errors']['other'] = 'You are not authorized to perform this operation.';
			$out['status'] = 403;
		}

		return $out;
	}

	/**
	 * Send Response
	 *
	 * AJAX responses are JSON formatted. Status codes indicate yay/nay.
	 *
	 * @param array $data Data.
	 * @return void Nothing.
	 */
	protected static function send(&$data) {
		$out = data::parse_args($data, static::RESPONSE);

		// Errors should indicate an errory response, while the lack of
		// errors should mean success.
		if (count($out['errors']) && data::in_range($out['status'], 200, 399)) {
			$out['status'] = 400;
		}
		elseif (!count($out['errors']) && !data::in_range($out['status'], 200, 399)) {
			$out['status'] = 200;
		}

		// Refresh the nonce. It is generated/validated server-side, but
		// everything else takes place in Javascriptland.
		$out['session']['n'] = static::get_nonce();

		// Pass it on!
		r_sanitize::utf8($out);
		wp_send_json($out, $out['status']);
	}

	/**
	 * Get Nonce
	 *
	 * All forms use a global Nonce. The value will vary from user to
	 * to user, but nothing specific is required for validation.
	 *
	 * @param bool $save Save it.
	 * @return string Nonce.
	 */
	public static function get_nonce($save=true) {
		$nonce = wp_create_nonce(static::NONCE_SALT);

		// Store it in a cookie if we're saving.
		if ($save) {
			@setcookie(
				static::NONCE_COOKIE,
				$nonce,
				0,
				COOKIEPATH,
				COOKIE_DOMAIN,
				is_ssl(),
				false
			);
			$_COOKIE[static::NONCE_COOKIE] = $nonce;
		}

		return $nonce;
	}

	/**
	 * Get Nonce From Cookie
	 *
	 * This method is mostly intended for soft Nonce checks, where it is
	 * undesirable to actually generate one.
	 *
	 * @return string Value.
	 */
	public static function get_soft_nonce() {
		// Logged in users can always get an up-to-date nonce.
		if (is_user_logged_in()) {
			return static::get_nonce();
		}

		// For others, we'll just trust the cookie, if present.
		return isset($_COOKIE[static::NONCE_COOKIE]) ? $_COOKIE[static::NONCE_COOKIE] : '';
	}

	/**
	 * Get URL
	 *
	 * This just helps to ensure that the AJAX endpoint URL is generated
	 * consistently on the front-end.
	 *
	 * @return string Nonce.
	 */
	public static function get_url() {
		return admin_url('admin-ajax.php');
	}

	// ----------------------------------------------------------------- end setup
}
