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

abstract class fields extends base\hook {

	// Actions: hook=>[callbacks].
	const ACTIONS = array(
		'after_setup_theme'=>array(
			'fields_init'=>null,
		),
		'carbon_fields_register_fields'=>array(
			'og_meta'=>null,
		),
	);

	// Filters: hook=>[callbacks].
	const FILTERS = array(
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
		Container::make('post_meta', 'og', 'Social Sharing - Open Graph Settings')

		// Display location.
		->where('post_type', 'IN', apply_filters('cf_og_meta_post_types', array('page', 'post')))
		->where('post_id', 'NOT IN', apply_filters('cf_og_meta_exclude', array(0)))

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
	}


	/**
	 * Clone fields.
	 *
	 * This sets up common field types that we can later clone into field groups.
	 *
	 * @param string $name The name of the field group to clone.
	 * @param string $prefix Optional. Prefix for the field names, used to avoid conflicts.
	 * @return array $fields Array of fields.
	 */
	public static function clone_fields(string $name, string $prefix='') {
		$fields = array();
		return $fields;
	}
}
