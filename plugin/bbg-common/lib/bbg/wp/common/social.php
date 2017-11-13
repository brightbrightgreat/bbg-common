<?php
/**
 * BBG Common: Social
 *
 * Functions that can filter through the hierarchy of information
 * to determine the appropriate post meta
 *
 * Table of Contents
 *
 * Get Title         - Get a title for a page or archive
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

	// -----------------------------------------------------------------
	// Meta Helpers
	// -----------------------------------------------------------------

	/**
	 * Get Title
	 *
	 * Returns a title for the post based on hero headline, OG title,
	 * and/or actual post title.
	 *
	 * @param bool $loop Whether we're pulling this title for a loop item.
	 * @param bool $og Whether we're pulling this title for open graph meta.
	 * @return string A string that can be used as the post title.
	 */
	public static function get_title($loop=false, $og=false) {
		global $post;

		$title = '';
		$post_type = get_post_type_object($post->post_type);

		// First, are we getting this title for a single item, either
		// on a single- page, or in a loop context.
		if ($loop || is_singular()) {
			// Try OpenGraph first.
			$title = ($og ? get_field('og_title', $post->ID) : '');

			// Try the hero headline next.
			if (!$title) {
				$title = get_field('hero_headline', $post->ID);

				// As a last resort, grab the post's title.
				if (!$title) {
					$title = $post->post_title;
				}
			}
		}

		// Otherwise we're dealing with an archive page of some sort.

		// Home.
		elseif (is_home()) {
			$p = get_page(get_option('page_for_posts', true));

			// Try OpenGraph first.
			$title = ($og ? get_field('og_title', $p->ID) : $title);

			// Or just the page title.
			if (!$title) {
				$title = $p->post_title;
			}
		}

		// Taxonomy.
		elseif (is_tax() || is_category() || is_tag()) {
			$title = $post_type->labels->name . ' in ' . single_term_title(' ', false);
		}

		// Author.
		elseif (is_author()) {
			global $wp_query;
			$author_obj = $wp_query->get_queried_object();
			$title = $post_type->labels->name . ' by ' . $author_obj->display_name;
		}

		// Next, month.
		elseif (is_month()) {
			$title = $post_type->labels->name . ' from ' . single_month_title(' ', false);
		}

		// Search.
		elseif (is_search()) {
			$title = 'Search results for &ldquo;' . get_search_query() . '&rdquo;';
		}

		// Custom post type archives.
		elseif (is_post_type_archive()) {
			$title = 'hey'; // TODO: test this!.
		}

		// Sanitize any title we've collected so far.
		r_sanitize::whitespace($title);

		// If we still do not have a title, just use the site's name.
		if (!$title) {
			$title = get_bloginfo('name');
		}

		// Let's format what we have.
		$title = apply_filters('the_title', $title);
		r_format::decode_entities($title);

		// And send it on its way.
		return $title;
	}

	/**
	 * Get Description
	 *
	 * Returns a description for the post based on hero text, OG
	 * description, and actual post text
	 *
	 * @param bool $loop Whether we're pulling this description for a loop item.
	 * @param bool $og Whether we're pulling this description for open graph meta.
	 * @return string A string that can be used as the post description.
	 */
	public static function get_description($loop=false, $og=false) {
		global $post;

		$description = '';
		$post_type = $post->post_type;

		// First, are we getting this description for a single item,
		// either on a single- page, or in a loop context.
		if ($loop || is_singular()) {
			// Try OpenGraph first.
			$description = ($og ? get_field('og_description', $post->ID) : '');

			// Try the content next.
			if (!$description) {
				$description = $post->post_content;

				// As a last resort, grab the hero description.
				if (!$description) {
					$description = get_field('hero_text', $post->ID);
				}
			}
		}

		// Otherwise we're dealing with an archive page of some sort.

		// Home.
		elseif (is_home()) {
			$p = get_post(get_option('page_for_posts', true));

			// Try OpenGraph first.
			$description = ($og ? get_field('og_description', $p->ID) : $description);

			// Or just page content.
			if (!$description) {
				$description = $p->post_content;
			}
		}

		// Taxonomy.
		elseif (is_tax() || is_category() || is_tag()) {
			$description = term_description();
		}

		// Author.
		elseif (is_author()) {
			global $wp_query;
			$author_obj = $wp_query->get_queried_object();
			$description = $post_type->labels->name . ' by ' . $author_obj->display_name;
		}

		// Next, month.
		elseif (is_month()) {
			$description = $post_type->labels->name . ' from ' . single_month_title(' ', false);
		}

		// Search.
		elseif (is_search()) {
			$description = 'Search results for &ldquo;' . get_search_query() . '&rdquo;';
		}

		// Custom post type archives.
		elseif (is_post_type_archive()) {
			$description = 'hey'; // TODO: Test this!
		}

		// Sanitize any description we've collected so far.
		r_sanitize::whitespace($description);

		// If we still do not have a description, just use the site's
		// tagline.
		if (!$description) {
			$description = get_bloginfo('description');
		}

		// Let's format what we have.
		$description = apply_filters('the_content', $description);
		r_format::decode_entities($description);

		return $description;
	}


	/**
	 * Get Image
	 *
	 * Returns an image for a particular post.
	 *
	 * @param bool $loop Whether we're pulling this image for a loop item.
	 * @param bool $og Whether we're pulling this image for open graph purposes.
	 * @return int An image ID that can be passed to various image processing functions.
	 */
	public static function get_image($loop=false, $og=false) {
		global $post;

		$featured = 0;
		if ($loop || is_singular()) {
			// If we're doing this for OG, get the OG image.
			if ($og) {
				$featured = (int) get_field('og_image', $post->ID);
			}

			// If no specific og image, look for featured image.
			if (!$featured) {
				$featured = (int) get_post_thumbnail_id($post->ID);

				// Last try the hero image.
				if (!$featured) {
					$featured = (int) get_field('hero_background_image', $post->ID);
				}
			}
		}

		// If no featured image, look for the site-wide fallback og
		// image.
		if (!$featured && $og) {
			$featured = (int) get_field('default_open_graph_image', 'options');
		}

		return $featured;
	}

	// ----------------------------------------------------------------- end meta
}
