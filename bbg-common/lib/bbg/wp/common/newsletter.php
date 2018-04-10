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
	protected static $api_key;
	protected static $list_id;
	protected static $mc;



	// -----------------------------------------------------------------
	// Init
	// -----------------------------------------------------------------

	/**
	 * API Connection
	 *
	 * Pull MailChimp settings and start an API connection.
	 *
	 * @param string $list_id List ID.
	 * @return bool True/false.
	 */
	protected static function get_mc(string $list_id='') {
		// Allow themes to handle this themselves.
		if (defined('NO_MAILCHIMP') && NO_MAILCHIMP) {
			static::$api_key = false;
			static::$list_id = false;
			static::$mc = false;
			return false;
		}

		// Set up the data if it hasn't been done yet.
		if (is_null(static::$api_key)) {
			static::$api_key = carbon_get_theme_option('mailchimp_api_key');
			static::$list_id = carbon_get_theme_option('mailchimp_list_id');

			if (!static::$api_key) {
				static::$api_key = false;
			}

			if (!static::$list_id) {
				static::$list_id = false;
			}

			try {
				if (static::$api_key) {
					static::$mc = new MailChimp(static::$api_key);
				}
			} catch (Throwable $e) {
				static::$api_key = false;
				static::$list_id = false;
				static::$mc = false;
			}
		}

		// Return the connection if we can, or false.
		return static::$api_key && ($list_id || static::$list_id);
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
			'list_id'=>'',
		);
		$data = data::parse_args($args, $defaults);

		// Gotta have an email.
		if (!$email) {
			return static::error('A valid email address is required.');
		}

		// Make sure the API details exist.
		if (!static::get_mc($data['list_id'])) {
			return static::error('MailChimp has not been configured.');
		}
		// Maybe use the default list.
		elseif (!$data['list_id']) {
			$data['list_id'] = static::$list_id;
		}

		try {
			$hash = static::$mc->subscriberHash($email);

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
			static::$mc->put("lists/{$data['list_id']}/members/$hash", $options);

			// Get the status one more time.
			$status2 = static::get_status($email, $args);

			// Trigger an action so themes can do something with this
			// info if desired.
			if ($status2 !== $status) {
				do_action('bbg_common_mc_status_update', $email, $status2);
			}

			return $status2;
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
		$data = data::parse_args($args, array('list_id'=>''));

		// Gotta have an email.
		if (!$email) {
			return static::error('A valid email address is required.');
		}

		// Make sure the API details exist.
		if (!static::get_mc($data['list_id'])) {
			return static::error('MailChimp has not been configured.');
		}
		// Maybe use the default list.
		elseif (!$data['list_id']) {
			$data['list_id'] = static::$list_id;
		}

		try {
			$hash = static::$mc->subscriberHash($email);

			$options = array(
				'email_address'=>$data['email'],
				'status'=>'unsubscribed',
			);

			// Submit it.
			static::$mc->put("lists/{$data['list_id']}/members/$hash", $options);

			// Get the status one more time.
			$status = static::get_status($email, $args);

			// Trigger an action so themes can do something with this
			// info if desired.
			do_action('bbg_common_mc_status_update', $email, $status);

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
		$data = data::parse_args($args, array('list_id'=>''));

		// Gotta have an email.
		if (!$email) {
			return static::error('A valid email address is required.');
		}

		// Make sure the API details exist.
		if (!static::get_mc($data['list_id'])) {
			return static::error('MailChimp has not been configured.');
		}
		// Maybe use the default list.
		elseif (!$data['list_id']) {
			$data['list_id'] = static::$list_id;
		}

		try {
			$hash = static::$mc->subscriberHash($email);

			$status = static::$mc->get("lists/{$data['list_id']}/members/$hash");
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
				return 'unsubscribed';
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
