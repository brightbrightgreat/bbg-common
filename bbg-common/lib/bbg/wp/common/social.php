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
 * Get Share URL     - Returns an appropriate share URL based on the network.
 *
 * @package brightbrightgreat/bbg-common
 * @author	Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \blobfolio\common\format as v_format;
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

	// Sharing Networks.
	const SHARING_NETWORKS = array(
		'twitter'=>'https://twitter.com/share?url=%1$s&text=%2$s&via=%5$s',
		'facebook'=>'http://www.facebook.com/sharer.php?u=%1$s',
		'pinterest'=>'https://www.pinterest.com/pin/create/button/?url=%1$s&media=%3$s',
		'linkedin'=>'https://www.linkedin.com/cws/share?url=%1$s&text=%2$s',
		'google+'=>'https://plus.google.com/share?url=%1$s&text=%2$s',
		'email'=>'mailto:?body=%4$s%1$s&subject=%2$s',
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
			$networks = (count($order) ? $order : static::SOCIAL_NETWORKS);

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


	/**
	 * Sharing URLs.
	 *
	 * Generates appropriate sharing links for a given post.
	 *
	 * @param string $network Network.
	 * @param int|object $post The post for which to generate URLs. Either a post ID or a post object.
	 * @return string|bool URL or false.
	 */
	public static function get_share_url(string $network=null, $post=null) {

		// First, is the network valid?
		// If not, get out.
		if (!isset(static::SHARING_NETWORKS[$network])) {
			return false;
		}

		// If our post is not an object,
		// We need to fetch the post in question.
		if (!is_a($post, 'WP_Post')) {

			// Typcast as integer.
			r_cast::int($post);

			// Get post.
			$post = get_post((int) $post);
		}

		// Okay, if we have a post object now,
		// We can compose the URL.
		if (is_a($post, 'WP_Post')) {

			// Permalink.
			$permalink = get_permalink($post->ID);

			// Title.
			$title = apply_filters('bbg_common_share_title', $post->post_title);

			// Excerpt.
			$excerpt = ($post->post_excerpt ? $post->post_excerpt : $post->post_content);
			$excerpt = apply_filters('bbg_common_share_excerpt', v_format::excerpt(strip_tags($excerpt), '25', '...', 'words'));

			// Image.
			$image = apply_filters('bbg_common_share_image', get_post_thumbnail_id($post->ID));
			$image = wp_get_attachment_image_src($image, 'original');
			$image = ($image ? $image[0] : '');

			// Via.
			$via = social::get_social_url('twitter');
			$via = basename(untrailingslashit($via));

			$url = sprintf(
				static::SHARING_NETWORKS[$network],
				urlencode($permalink),
				$title,
				urlencode($image),
				$excerpt,
				urlencode($via)
			);

			return $url;
		}

		// We still didn't have a post,
		// So return false.
		return false;

	}
}
