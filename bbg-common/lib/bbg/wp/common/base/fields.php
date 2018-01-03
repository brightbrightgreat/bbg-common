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

abstract class fields extends hook {

	// Actions: hook=>[callbacks].
	const ACTIONS = array(
	);

	// Filters: hook=>[callbacks].
	const FILTERS = array(
	);

	// The base for linkables always includes
	// Posts and pages,
	// Category and post tag.
	protected static $linkables = array(
		array(
			'type'=>'post',
			'post_type'=>'post',
		),
		array(
			'type'=>'post',
			'post_type'=>'page',
		),
		array(
			'type'=>'term',
			'taxonomy'=>'category',
		),
		array(
			'type'=>'term',
			'taxonomy'=>'post_tag'
		)
	);


	/**
	 * Get Linkables
	 *
	 * Generates an array of valid post types and taxonomies
	 * That can be used in an association field.
	 *
	 * @return array $linkables.
	 */
	public static function get_linkables() {

		// Let's grab all our custom public post types.
		$post_types = get_post_types(array(
			'public'=>true,
			'_builtin'=>false,
		));

		foreach ($post_types as $type) {
			static::$linkables[] = array(
				'type'=>'post',
				'post_type'=>$type,
			);
		}

		// Let's grab all our custom public taxonomies
		$taxonomies = get_taxonomies(array(
			'public'=>true,
			'_builtin'=>false,
		));

		foreach ($taxonomies as $tax) {
			static::$linkables[] = array(
				'type'=>'term',
				'taxonomy'=>$tax,
			);
		}

		return apply_filters('bbg_common_linkables', static::$linkables);
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
