<?php
/**
 * BBG Common: Carbon Fields
 *
 * Load CarbonFields, and also add a few fields.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \Carbon_Fields\Container;
use \Carbon_Fields\Field;

use \blobfolio\common\data;
use \blobfolio\common\ref\sanitize as r_sanitize;

class fields extends base\fields {

	// Actions: hook=>[callbacks].
	const ACTIONS = array(
		'after_setup_theme'=>array(
			'fields_init'=>null,
		),
		'carbon_fields_register_fields'=>array(
			'tracking'=>null,
			'mailchimp'=>null,
			'og_meta'=>null,
		),
	);


	/**
	 * Init fields
	 *
	 * @return void Nothing.
	 */
	public static function fields_init() {
		\Carbon_Fields\Carbon_Fields::boot();
	}


	/**
	 * OG Meta (individual content)
	 *
	 * @return void Nothing.
	 */
	public static function og_meta() {

		// Let's grab all custom public post types.
		$cpt = get_post_types(array(
			'_builtin'=>false,
			'public'=>true,
		));

		// And merge those in with regular post types for our default post_types.
		$post_types = array_merge(array('page', 'post'), $cpt);

		Container::make('post_meta', 'og', 'Social Sharing - Open Graph Settings')

		// Display location.
		->where('post_type', 'IN', apply_filters('bbg_common_og_meta_post_types', $post_types))
		->where('post_id', 'NOT IN', apply_filters('bbg_common_og_meta_exclude', array(0)))

		// Set up fields.
		->add_fields(array(

			Field::make('html', 'og_instructions')
			->set_html('
				<p>Open Graph settings control what shows up on social networks like Facebook, LinkedIn, and Twitter when someon shares this url.</p>
				<p>WordPress automatically fills in certain values based on the content but these fields give you the opportunity to override them. You may leave them blank if the default is acceptable.</p>
				<p>You can check what the page returns by using the Facebook Open Graph Debugger: <a href="https://developers.facebook.com/tools/debug">https://developers.facebook.com/tools/debug</a></p>
			'),

			Field::make('text', 'og_title', 'Title'),

			Field::make('textarea', 'og_description', 'Description'),

			Field::make('image', 'og_image', 'Image'),
		));

		Container::make('theme_options', 'Sharing')
		->set_page_file('site-sharing')
		->set_icon('dashicons-share')
		->add_fields(array(

			// Instructions.
			Field::make('html', 'sharing_instructions')
			->set_html('
				<h3>Sharing - Open Graph</h3>
				<p>Open Graph settings control what shows up on social networks like Facebook, LinkedIn, and Twitter when someon shares this url.</p>
				<p>You <strong>must</strong> set defaults for the site. Defaults will be used on the homepage as well as any pages that are missing information to automatically generate the information.</p>
				<p>You can check what the page returns by using the Facebook Open Graph Debugger: <a href="https://developers.facebook.com/tools/debug">https://developers.facebook.com/tools/debug</a></p>
			'),

			// Open Graph Title.
			Field::make('text', 'og_title', 'Title')
			->set_required(true),

			// Open Graph Description.
			Field::make('textarea', 'og_description', 'Description')
			->set_required(true),

			// Open Graph Image.
			Field::make('image', 'og_image', 'Image')
			->set_required(true)
			->set_help_text('<p>This image will be used as the thumbnail when a post or page is shared and doesn\'t have a featured Image assigned.</p><p>Image should be at least 1200x630.</p>'),

			// Facebook Profile ID.
			Field::make('text', 'facebook_profile_id', 'Facebook Profile ID')
			->set_required(true),

			// Facebook App ID.
			Field::make('text', 'facebook_app_id', 'Facebook App ID')
			->set_required(true),

		));
	}


	/**
	 * Tracking Code
	 *
	 * @return void Nothing.
	 */
	public static function tracking() {
		// Allow themes to handle this themselves.
		if (defined('NO_GTM') && NO_GTM) {
			return;
		}

		Container::make('theme_options', 'Analytics & Tracking')
		->set_page_file('site-tracking')
		->set_icon('dashicons-chart-bar')
		->add_tab(
			'Google Tag Manager',
			array(
				Field::make('text', 'gtm', 'ID')
				->set_attribute('placeholder', 'GTM-#######')
				->set_help_text("To keep stats honest, GTM tracking code is not added to pages viewed by WP users or under staging environments. To override this, append <code>?gtm</code> to the URL, or add <code>define('BBG_COMMON_GTM', true)</code> to the code."),
			)
		);
	}


	/**
	 * MailChimp
	 *
	 * @return void Nothing.
	 */
	public static function mailchimp() {
		// Allow themes to handle this themselves.
		if (defined('NO_MAILCHIMP') && NO_MAILCHIMP) {
			return;
		}

		Container::make('theme_options', 'MailChimp')
		->set_page_file('site-mailchimp')
		->set_icon('dashicons-email-alt')
		->add_tab(
			'MailChimp',
			array(
				Field::make('text', 'mailchimp_api_key', 'API Key'),

				Field::make('text', 'mailchimp_list_id', 'List ID')
				->set_help_text('This is the default mailing list.'),

				Field::make('rich_text', 'mailchimp_subscribed', 'Subscribe Message')
				->set_help_text('This text will be shown when a user joins the list.'),

				Field::make('rich_text', 'mailchimp_pending', 'Pending Message')
				->set_help_text('This text will be shown when a user joins the list but has not yet completed the double opt-in.'),

				Field::make('rich_text', 'mailchimp_unsubscribed', 'Unsubscribe Message')
				->set_help_text('This text will be shown when a user leaves the list.'),
			)
		);
	}
}
