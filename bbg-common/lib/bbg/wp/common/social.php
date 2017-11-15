<?php
/**
 * BBG Common: Social
 *
 * Functions that can filter through the hierarchy of information
 * to determine the appropriate post meta
 *
 * Table of Contents
 *
 * Get Social URL    - Function for returning social network URLs
 * Get Description   - Get a description for a page or archive
 * Get Image         - Get an image id for a page or archive
 *
 * @package brightbrightgreat/bbg-common
 * @author	Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \blobfolio\common\ref\format as r_format;
use \blobfolio\common\ref\cast as r_cast;
use \blobfolio\common\ref\sanitize as r_sanitize;

class social {

	// Social Networks.
	const SOCIAL_NETWORKS = array(
		'facebook',
		'googleplus',
		'instagram',
		'linkedin',
		'pinterest',
		'tumblr',
		'twitter',
		'vimeo',
		'youtube',
	);

	protected static $social;


	/**
	 * Social URLs.
	 *
	 * Pull social network URLs from ACF. This is
	 * cached for performance.
	 *
	 * @param string $network Network.
	 * @param array $order An array of networks in the order you want them returned. If empty, the default order will be used.
	 * @return string|bool URL or false.
	 */
	public static function get_social_url(string $network=null, array $order=null) {

		// Pull all networks.
		if (!is_array(static::$social)) {
			static::$social = array();

			// Set our order appropriately.
			$networks = (count($order) ?  $order : static::SOCIAL_NETWORKS);

			// Loop through networks in the order specified.
			foreach ($networks as $v) {
				static::$social[$v] = carbon_get_theme_option('social_' . $v);
				r_sanitize::url(static::$social[$v]);
				if (!static::$social[$v]) {
					unset(static::$social[$v]);
				}
			}
		}

		if (!is_null($network)) {
			return array_key_exists($network, static::$social) ? static::$social[$network] : false;
		}

		return static::$social;
	}

}
