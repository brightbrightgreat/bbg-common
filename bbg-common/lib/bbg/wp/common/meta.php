<?php
/**
 * BBG Common: Post Meta
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

use \bbg\wp\common\social;
use \blobfolio\common\ref\sanitize as r_sanitize;
use \blobfolio\common\ref\format as r_format;
use \blobfolio\common\format as v_format;

class meta extends base\hook {

	// Actions: hook=>[callbacks].
	const ACTIONS = array(
		'wp_head'=>array(
			'og_meta'=>null,
		),
	);

	// Filters: hook=>[callbacks].
	const FILTERS = array();

	// -----------------------------------------------------------------
	// Micro-meta
	// -----------------------------------------------------------------

	/**
	 * Get Title
	 *
	 * Returns a title based on the hierarchy.
	 *
	 * @param bool $loop Whether or not this is being called within loop.
	 * @param bool $og Whether or not this is being used for Open Graph data.
	 * @return string A string that can be used as the post title.
	 */
	public static function get_title($loop=false, $og=false) {

		if (is_404()) {
			return '404 Not Found';
		}

		global $post;

		$title = '';
		$ogt = carbon_get_post_meta($post->ID, 'og_title');

		if ($post) {
			$post_type = get_post_type_object($post->post_type);
		}

		// First, are we getting this title for a single item, either
		// on a single- page, or in a loop context.
		if ($loop || (is_singular() && !is_front_page())) {
			$title = $post->post_title;
		}

		// Otherwise we're dealing with an archive page of some sort.
		// Front page.
		elseif (is_front_page()) {
			$p = get_page(get_option('page_on_front', true));
			$title = get_bloginfo('site_name');
			$ogt = carbon_get_post_meta($p->ID, 'og_title');
		}

		// Home.
		elseif (is_home()) {
			$p = get_page(get_option('page_for_posts', true));
			$title = $p->post_title;
			$ogt = carbon_get_post_meta($p->ID, 'og_title');
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
			$post_type = get_post_type_object($post->post_type);
			$key = ($post_type->rewrite ? $post_type->rewrite['slug'] : $post_type->name);
			$archive = get_page_by_path($key, OBJECT);

			if ($archive) {
				$ogt = carbon_get_post_meta($archive->ID, 'og_title');
				$title = $archive->post_title;
			}

			if (!$title) {
				$title = $post_type->label;
			}
		}

		// Let's give ourselves a chance to override,
		// Before the final OG overrides.
		$title = apply_filters('bbg_common_meta_title_pre', $title);

		// If we have $ogt and we're here for OG.
		if ($ogt && $og) {
			$title = $ogt;
		}

		// Still no title but we're here for og?
		// Let's grab the site-wide og default.
		if (!$title && $og) {
			$title = carbon_get_theme_option('og_title');
		}

		// If we still do not have a title, just use the site's name.
		if (!$title && $og) {
			$title = get_bloginfo('name');
		}

		// Let's give ourselves the option to override this title.
		$title = apply_filters('bbg_common_meta_title', $title);

		// Let's format what we have.
		r_sanitize::whitespace($title);
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

		if (is_404()) {
			return 'Sorry, we couldn\'t find the post you were looking for.';
		}

		global $post;

		$description = '';
		$post_type = $post->post_type;
		$ogd = carbon_get_post_meta($post->ID, 'og_description');

		// First, are we getting this description for a single item,
		// either on a single- page, or in a loop context.
		if ($loop || (is_singular() && !is_front_page())) {
			// Try the content first.
			$description = $post->post_content;
		}

		// Otherwise we're dealing with an archive page of some sort.
		// Front page.
		elseif (is_front_page()) {
			$p = get_post(get_option('page_on_front', true));
			$description = $p->post_content;
			$ogd = carbon_get_post_meta($p->ID, 'og_description');
		}

		// Home.
		elseif (is_home()) {
			$p = get_post(get_option('page_for_posts', true));
			$ogd = carbon_get_post_meta($p->ID, 'og_description');
			// Try the page description.
			$description = $p->post_content;
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
			$post_type = get_post_type_object($post->post_type);
			$key = ($post_type->rewrite ? $post_type->rewrite['slug'] : $post_type->name);
			$archive = get_page_by_path($key, OBJECT);
			$ogd = carbon_get_post_meta($archive->ID, 'og_description');

			if ($archive) {
				// Try the post content.
				$description = $archive->post_content;
			}

			// Still no description?
			if (!$description) {
				$description = $post_type->description;
			}
		}

		// Give ourselves an opportunity to override the description,
		// Without overriding the OG overrides.
		$description = apply_filters('bbg_common_meta_description_pre', $description);

		// If we have OG overrides, and we're here for OG,
		// Apply them now.
		if ($ogd && $og) {
			$description = $ogd;
		}

		// Still no title but we're here for og?
		// Let's grab the site-wide og default.
		if (!$description && $og) {
			$description = carbon_get_theme_option('og_description');
		}

		// If we still do not have a description, just use the site's
		// tagline.
		if (!$description) {
			$description = get_bloginfo('description');
		}

		// Let's give ourselves the option to override this title.
		$description = apply_filters('bbg_common_meta_description', $description);

		// Let's format what we have.
		r_sanitize::whitespace($description);
		$description = strip_shortcodes($description);
		r_format::decode_entities($description);

		// And send it on its way.
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
		$ogi = carbon_get_post_meta($post->ID, 'og_image');

		if ($loop || is_singular()) {
			$featured = (int) get_post_thumbnail_id($post->ID);
		}

		// Give ourselves the opportunity to override,
		// Before the final OG override.
		$featured = (int) apply_filters('bbg_common_meta_image_pre', $featured);

		// If we have an OG image, use that.
		if ($ogi && $og) {
			$featured = $ogi;
		}

		// If no featured image, look for the site-wide fallback og
		// image.
		if (!$featured && $og) {
			$featured = (int) carbon_get_theme_option('og_image');
		}

		// Give ourselves an option to override the value.
		$featured = (int) apply_filters('bbg_common_meta_image', $featured);

		return $featured;
	}

	// ----------------------------------------------------------------- end meta


	/**
	 * OG Meta
	 *
	 * This adds the appropriate Open Graph Meta to the page.
	 */
	public static function og_meta() {

		// --------------------
		// SETUP
		// --------------------

		// Get some global variables to make this easier.
		global $post;
		global $wp_query;

		// Figure out the post type we're working with.
		$post_type = null;
		if (is_singular()) {
			$post_type = get_post_type_object($post->post_type);
		}

		// Some site-wide defaults.
		$name = v_format::decode_entities(get_bloginfo('name'));
		$url = site_url($_SERVER['REQUEST_URI']);

		// Twitter.
		$twitter_user = null;
		if (false !== ($twitter = social::get_social_url('twitter'))) {
			$twitter_user = basename(untrailingslashit($twitter));
		}

		// Facebook.
		$facebook = social::get_social_url('facebook');
		$facebook_prof = carbon_get_theme_option('facebook_profile_id');
		$facebook_app = carbon_get_theme_option('facebook_app_id');

		// Title.
		$title = meta::get_title(false, true) . (!is_front_page() ? ' &mdash; ' . $name : '');

		// Description.
		$description = strip_tags(meta::get_description(false, true));

		// Image.
		$featured = meta::get_image(false, true);

		// Taxonomy?
		$categories = null;
		$tags = null;
		if (is_singular()) {
			$categories = get_the_category($post->ID);
			if (!is_array($categories) || !count($categories)) {
				$categories = null;
			}
			$tags = get_the_tags($post->ID);
			if (!is_array($tags) || !count($tags)) {
				$tags = null;
			}
		}

		// --------------------
		// BUILD
		// --------------------
		// An array will help us get a handle on whitespace
		// and prevent a lot of PHP breakouts.
		$out = array();

		// Twitter Card Style.
		$out['twitter:card'] = array(
			'name'=>'twitter:card',
			'content'=>'summary_large_image',
		);

		// Site Name.
		$out['site:name'] = array(
			'property'=>'og:site_name',
			'content'=>$name,
		);

		// Type of site.
		$out['og:type'] = array(
			'property'=>'og:type',
			'content'=>'website',
		);

		// Site Locale.
		$out['og:locale'] = array(
			'property'=>'og:locale',
			'content'=>'en_us',
		);

		// Twitter User.
		if ($twitter_user) {
			$out['twitter:site'] = array(
				'name'=>'twitter:site',
				'content'=>'@' . $twitter_user,
			);

			$out['twitter:creator'] = array(
				'name'=>'twitter:creator',
				'content'=>$twitter_user,
			);
		}

		// Page URL.
		$out['og:url'] = array(
			'property'=>'og:url',
			'content'=>$url,
		);

		// Page title.
		$out['og:title'] = array(
			'property'=>'og:title',
			'name'=>'twitter:title',
			'content'=>$title,
		);

		// Page description.
		$out['og:description'] = array(
			'property'=>'og:description',
			'name'=>'twitter:description',
			'content'=>$description,
		);

		$out['description'] = array(
			'name'=>'description',
			'content'=>$description,
		);

		// Page image.
		if ($featured) {
			$featured_img = wp_get_attachment_image_src($featured, 'og-img');

			// If the image worked...
			if (isset($featured_img[1])) {
				// Include redundant SSL version.
				if (is_ssl()) {
					$out['og:image:url'] = array(
						'property'=>'og:image',
						'name'=>'twitter:image',
						'content'=>$featured_img[0],
					);

					$out['og:image:secure_url'] = array(
						'property'=>'og:image:secure_url',
						'content'=>$featured_img[0],
					);
				}
				// Just the one.
				else {
					$out['og:image:url'] = array(
						'property'=>'og:image:url',
						'name'=>'twitter:image',
						'content'=>$featured_img[0],
					);
				}

				$out['og:image:width'] = array(
						'property'=>'og:image:width',
						'content'=>$featured_img[1],
				);

				$out['og:image:height'] = array(
					'property'=>'og:image:height',
					'content'=>$featured_img[2],
				);
			}
		}

		// Default type/author.
		$out['author'] = array(
			'name'=>'author',
			'content'=>$name,
		);

		// Posts only...
		if (is_singular('post')) {

			$out['og:type']['content'] = 'article';

			// Connect facebook author if we have one.
			if ($facebook) {
				$out['article:publisher'] = array(
					'property'=>'article:publisher',
					'content'=>$facebook,
				);
			}

			// Date.
			$out['article:published_time'] = array(
				'property'=>'article:published_time',
				'content'=>$post->post_date,
			);

			// Categories.
			if ($categories) {
				$out['article:section'] = array(
					'property'=>'article:section',
					'content'=>$categories[0]->name,
				);
			}

			// Tags.
			if ($tags) {
				foreach ($tags as $tag) {
					$out['article:tags:' . $tag->name] = array(
						'property'=>'article:tag',
						'content'=>$tag->name,
					);
				}
			}
		}

		// Facebook extras.
		if ($facebook_prof) {
			$out['fb:profile_id'] = array(
				'property'=>'fb:profile_id',
				'content'=>$facebook_prof,
			);
		}

		if ($facebook_app) {
			$out['fb:app_id'] = array(
				'property'=>'fb:app_id',
				'content'=>$facebook_app,
			);
		}

		// Allow ourselves to override anything we need.
		$out = apply_filters('bbg_common_og_meta', $out);

		// One pass for propertied tags.
		foreach ($out as $item) {
			if (isset($item['property'], $item['content'])) {
				echo sprintf(
					'<meta property="%s" content="%s" />' . "\n",
					\esc_attr($item['property']),
					\esc_attr($item['content'])
				);
			}
		}

		// One pass for named tags.
		foreach ($out as $item) {
			if (isset($item['name'], $item['content'])) {
				echo sprintf(
					'<meta name="%s" content="%s" />' . "\n",
					\esc_attr($item['name']),
					\esc_attr($item['content'])
				);
			}
		}
	}
}
