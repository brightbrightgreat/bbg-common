<?php
/**
 * BBG Common: Carbon Fields
 *
 * Load CarbonFields, and also add a few fields.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common\base;

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
	);

	// Filters: hook=>[callbacks].
	const FILTERS = array(
	);

	protected static $linkeables = array();

	/**
	 * Init fields
	 *
	 * @return void Nothing.
	 */
	public static function fields_init() {
		\Carbon_Fields\Carbon_Fields::boot();
	}


	/**
	 * Get Linkables
	 *
	 * Generates an array of valid post types and taxonomies
	 * That can be used in an association field.
	 *
	 * @return array $linkeables.
	 */
	public static function get_linkables() {
		$post_types = get_post_types(array(
			'public'=>true,
		));

		foreach($post_types as $type) {
			static::$linkables[] = array(
				'type'=>'post',
				'post_type'=>$type
			);
		}

		print_r(static::$linkables);
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
