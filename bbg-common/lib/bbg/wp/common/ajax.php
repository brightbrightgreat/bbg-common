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

namespace bbg\wp\common;

use \blobfolio\common\data;
use \blobfolio\common\ref\sanitize as r_sanitize;
use \blobfolio\common\ref\cast as r_cast;

class ajax extends base\ajax {
	// These are the actions offered by the plugin. The callback name
	// should correspond to the action, minus the prefix. The value
	// indicates whether the method is public or only available to
	// WP users.
	const ACTIONS = array(
		'heartbeat'=>true,
	);

	// -----------------------------------------------------------------
	// Misc
	// -----------------------------------------------------------------

	/**
	 * Heartbeat
	 *
	 * To make the site friendlier to static cache, dynamic nonces are
	 * delivered as needed via AJAX. This happens automatically when
	 * when sending the response, so we just need a lightweight
	 * callback to deliver the goods.
	 *
	 * @return void Nothing.
	 */
	public static function heartbeat() {
		$out = null;
		static::send($out);
	}

	// ----------------------------------------------------------------- end misc
}
