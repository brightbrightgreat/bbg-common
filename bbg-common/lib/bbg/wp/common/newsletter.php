<?php
/**
 * BBG Common: Newsletter
 *
 * Simple Mailchimp wrappers to manage subscription statuses.
 *
 * @package brightbrightgreat/bbg-common
 * @author	Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \blobfolio\common\data;
use \blobfolio\common\ref\format as r_format;
use \blobfolio\common\ref\sanitize as r_sanitize;
use \DrewM\MailChimp\MailChimp;
use \Throwable;
use \WP_Error;

class newsletter {

	// Our connection info.
	protected static $env = array(
		'api_key'=>'',
		'list_id'=>'',
	);



	// -----------------------------------------------------------------
	// Init
	// -----------------------------------------------------------------

	/**
	 * Parse/Set Environment
	 *
	 * We'll hold onto the API key and list ID between calls to minimize
	 * the number of arguments subsequent calls have to provide.
	 *
	 * @param array $args Arguments.
	 * @return void Nothing.
	 */
	protected static function set_env($args=null) {
		$data = data::parse_args($args, static::$env);

		// Allow themes to set this data via filter.
		$data = apply_filters('bbg_common_newsletter_env', $data);

		// Save it.
		static::$env = data::parse_args($data, static::$env);
	}

	/**
	 * Has Env Credentials?
	 *
	 * Make sure we have enough information to contact MailChimp.
	 *
	 * @return bool True/false.
	 */
	protected static function has_env() {
		return static::$env['api_key'] && static::$env['list_id'];
	}

	// ----------------------------------------------------------------- end init



	// -----------------------------------------------------------------
	// List Management
	// -----------------------------------------------------------------

	/**
	 * Subscribe
	 *
	 * @param string $email Email.
	 * @param array $args Arguments.
	 * @return string|WP_Error Status or error.
	 */
	public static function subscribe(string $email, $args=null) {
		r_sanitize::email($email);

		// Possible arguments.
		$defaults = array(
			'merge'=>null,
			'interests'=>null,
		);
		$data = data::parse_args($args, $defaults);

		// Set up environment.
		static::set_env($args);
		if (!static::has_env()) {
			return static::error('The API Key and/or List ID is missing.');
		}

		// Gotta have an email.
		if (!$email) {
			return static::error('A valid email address is required.');
		}

		try {
			$mc = new MailChimp(static::$env['api_key']);
			$hash = $mc->subscriberHash($email);

			// We want to force double opt-in for anybody that isn't
			// already subscribed.
			$status = static::get_status($email, $args);

			// List options.
			$options = array(
				'email_address'=>$email,
				'status'=>('subscribed' === $status) ? 'subscribed' : 'pending',
			);

			// Add merge.
			if (is_array($data['merge']) && count($data['merge'])) {
				$options['merge_fields'] = $data['merge'];
			}

			// Add interests.
			if (is_array($data['interests']) && count($data['interests'])) {
				$options['interests'] = array();
				foreach ($data['interests'] as $v) {
					$options['interests'][$v] = true;
				}
			}

			// Submit it.
			$response = $mc->put('lists/' . static::$env['list_id'] . "/members/$hash", $options);

			// Get the status one more time.
			$status = static::get_status($email, $args);

			// Trigger an action so themes can do something with this
			// info if desired.
			do_action('bbg_common_newsletter_status', $email, $status);

			return $status;
		} catch (Throwable $e) {
			return static::error($e);
		}
	}

	/**
	 * Unsubscribe
	 *
	 * @param string $email Email.
	 * @param array $args Arguments.
	 * @return string|WP_Error Status or error.
	 */
	public static function unsubscribe(string $email, $args=null) {
		r_sanitize::email($email);

		// Set up environment.
		static::set_env($args);
		if (!static::has_env()) {
			return static::error('The API Key and/or List ID is missing.');
		}

		// Gotta have an email.
		if (!$email) {
			return static::error('A valid email address is required.');
		}

		try {
			$mc = new MailChimp(static::$env['api_key']);
			$hash = $mc->subscriberHash($email);

			$options = array(
				'email_address'=>$data['email'],
				'status'=>'unsubscribed',
			);

			// Submit it.
			$response = $mc->put('lists/' . static::$env['list_id'] . "/members/$hash", $options);

			// Get the status one more time.
			$status = static::get_status($email, $args);

			// Trigger an action so themes can do something with this
			// info if desired.
			do_action('bbg_common_newsletter_status', $email, $status);

			return $status;
		} catch (Throwable $e) {
			return static::error($e);
		}
	}

	/**
	 * Get Status
	 *
	 * @param string $email Email.
	 * @param array $args Arguments.
	 * @return string|WP_Error Status or error.
	 */
	public static function get_status(string $email, $args=null) {
		r_sanitize::email($email);

		// Set up environment.
		static::set_env($args);
		if (!static::has_env()) {
			return static::error('The API Key and/or List ID is missing.');
		}

		// Gotta have an email.
		if (!$email) {
			return static::error('A valid email address is required.');
		}

		try {
			$mc = new MailChimp(static::$env['api_key']);
			$hash = $mc->subscriberHash($email);

			$status = $mc->get('lists/' . static::$env['list_id'] . "/members/$hash");
			if (isset($status['email_address'], $status['status'])) {
				// Subscribed.
				if ('subscribed' === $status['status']) {
					return 'subscribed';
				}
				// Unsubscribed.
				elseif (
					('unsubscribed' === $status['status']) ||
					('cleaned' === $status['status'])
				) {
					return 'unsubscribed';
				}

				return 'pending';
			}
			else {
				return static::error('The status could not be determined.');
			}
		} catch (Throwable $e) {
			return static::error($e);
		}
	}

	// ----------------------------------------------------------------- end lists



	// -----------------------------------------------------------------
	// Error Handling
	// -----------------------------------------------------------------

	/**
	 * Error
	 *
	 * Convert an Exception or Throwable to a WP_Error.
	 *
	 * @param Throwable $error Error.
	 * @return WP_Error Error.
	 */
	protected static function error(Throwable $error) {
		if (
			is_object($error) &&
			method_exists($error, 'getCode') &&
			method_exists($error, 'getMessage')
		) {
			return new WP_Error($error->getCode(), $error->getMessage());
		}
		elseif (is_string($error) && $error) {
			return new WP_Error('other', $error);
		}

		return new WP_Error('other', 'An unidentified error occurred.');
	}

	// ----------------------------------------------------------------- end errors
}
