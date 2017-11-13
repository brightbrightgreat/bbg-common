<?php
/**
 * BBG Common: Types and Taxonomies
 *
 * Some helper functions for registering custom post types
 * And custom taxonomies.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

class typetax extends \bbg\wp\common\base\hook {

	// Actions: hook=>[callbacks].
	const ACTIONS = array(
		'registered_post_type'=>array(
			'taxonomies'=>null,
		),
	);


/**
 * Register Default Custom Taxonomies
 *
 * For each custom post type, we want to register some
 * taxonomies by default. Anything beyond this would be
 * registered in the theme itself.
 *
 * @return void Nothing.
 */
	public static function taxonomies() {
		$args = array(
			'public'=>true,
			'_builtin'=>false,
		);

		$types = get_post_types($args, 'objects');

		if ($types) {
			foreach ($types as $t) {
				// Category.
				register_taxonomy(
					$t->name . '_category',
					array($t->name),
					array(
						'labels'=>static::taxonomy_labels('Category', 'Categories'),
						'rewrite'=>array('slug'=>$t->name . '_category'),
						'show_ui'=>true,
						'show_admin_column'=>true,
						'query_var'=>true,
						'hierarchical'=>true,
					)
				);

				// Tag.
				register_taxonomy(
					$t->name . '_tag',
					array($t->name),
					array(
						'labels'=>static::taxonomy_labels('Tag', 'Tags'),
						'rewrite'=>array('slug'=>$t->name . '_tag'),
						'show_ui'=>true,
						'show_admin_column'=>true,
						'query_var'=>true,
						'hierarchical'=>false,
					)
				);
			} // Endforeach types.
		} // Endif types.
	}


	/**
	 * Post Type Labels
	 *
	 * Helper to generate the huge labels array for new post types.
	 *
	 * @param string $singular Singular.
	 * @param string $plural Plural.
	 * @return array Labels.
	 */
	public static function type_labels($singular, $plural) {
		return array(
			'name'=>$plural,
			'singular_name'=>$singular,
			'menu_name'=>$plural,
			'name_admin_bar'=>$singular,
			'add_new'=>'Add New',
			'add_new_item'=>"Add New $singular",
			'new_item'=>"New $singular",
			'edit_item'=>"Edit $singular",
			'view_item'=>"View $singular",
			'all_items'=>"All $plural",
			'search_items'=>"Search $plural",
			'parent_item_colon'=>"Parent $plural",
			'not_found'=>"No $plural Found.",
			'not_found_in_trash'=>"No $plural Found In Trash.",
		);
	}

	/**
	 * Taxonomy Labels
	 *
	 * Helper to generate the huge labels array for new taxonomies.
	 *
	 * @param string $singular Singular.
	 * @param string $plural Plural.
	 * @return array Labels.
	 */
	public static function taxonomy_labels($singular, $plural) {
		return array(
			'name'=>$plural,
			'singular_name'=>$singular,
			'search_items'=>"Search $plural",
			'all_items'=>"All $plural",
			'parent_item'=>"Parent $singular",
			'parent_item_colon'=>"Parent $singular:",
			'edit_item'=>"Edit $singular",
			'update_item'=>"Update $singular",
			'add_new_item'=>"Add New $singular",
			'new_item_name'=>"New $singular Name",
			'menu_name'=>$plural,
		);
	}

}
