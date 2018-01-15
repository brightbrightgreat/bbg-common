<?php
/**
 * BBG Common: Utility
 *
 * Functions that don't really fit anywhere else ¯\_(ツ)_/¯
 *
 * @package brightbrightgreat/bbg-common
 * @author	Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \Carbon_Fields\Field;
use \blobfolio\common\data as data;
use \blobfolio\common\format as v_format;
use \blobfolio\common\ref\cast as r_cast;
use \blobfolio\common\ref\format as r_format;
use \blobfolio\common\ref\sanitize as r_sanitize;


class utility {

	// Link structure.
	const LINK = array(
		'link_type'=>'',
		'link_text'=>'',
		'link_internal'=>array(),
		'link_email'=>'',
		'link_subject'=>'',
		'link_external'=>'',
		'link_download'=>'',
		'item_classes'=>array(),
	);

	/**
	 * Get Link
	 *
	 * @param array $link Link array.
	 * @return array Link.
	 */
	public static function get_link($link) {
		$link = data::parse_args($link, static::LINK);

		// Some additional sanitizing.
		r_sanitize::email($link['link_email']);
		r_sanitize::whitespace($link['link_subject']);
		r_sanitize::url($link['link_url']);
		r_sanitize::url($link['download']);
		r_cast::array($link['item_classes']);

		$link_clean = array(
			'type'=>$link['link_type'],
			'text'=>$link['link_text'],
			'classes'=>$link['item_classes'],
			'url'=>'',
			'target'=>'_self',
			'download'=>false,
		);

		switch ($link['link_type']) {
			case 'internal':
				// TODO make links better.
				if (is_array($link['link_internal'])) {
					$l = $link['link_internal'][0];

					// Carbon Fields doesn't always return a proper int,
					// but that's what we always want.
					r_cast::int($l['id'], true);

					// If this is a term.
					if (isset($l['type']) && 'term' === $l['type']) {
						switch ($l['subtype']) {

							// Category.
							case 'category':
								$link_clean['url'] = get_category_link($l['id']);
								break;

							// Post tag.
							case 'post_tag':
								$link_clean['url'] = get_tag_link($l['id']);
								break;

							// Custom taxonomies.
							default:
								$link_clean['url'] = get_term_link($l['id'], $l['subtype']);
								break;
						}
					}
					// A post, etc.
					else {
						$link_clean['url'] = get_permalink($l['id']);
					}
				}

				break;

			case 'email':
				$link_clean['url'] = 'mailto:' . $link['link_email'] . ($link['link_subject'] ? '?subject=' . urlencode($link['link_subject']) : '');
				$link_clean['classes'][] = 'js_social-share';
				break;

			case 'external':
				$link_clean['url'] = $link['link_external'];
				$link_clean['target'] = '_blank';
				break;

			case 'download':
				$link_clean['url'] = wp_get_attachment_url($link['link_download']);
				$link_clean['download'] = true;
				break;
		}

		return $link_clean;
	}


	/**
	 * Get SVG Icon
	 *
	 * Alias for svg::get_icon().
	 *
	 * @param string $icon Icon.
	 * @param array $classes Class(es).
	 * @return string $svg HTML.
	 */
	public static function get_icon(string $icon, $classes=null) {
		return svg::get_icon($icon, $classes);
	}

	/**
	 * Related Fields
	 *
	 * Returns an array of related post field definitions.
	 *
	 * @param string $type Post type.
	 * @param int $max The max amount of posts to return.
	 * @param string $prefix An optional prefix to add the to field names to avoid conflict.
	 * @param array $terms Array of terms to filter by. By default, category and tag are used.
	 * @return array $fields Array of fields.
	 */
	public static function related_fields(string $type='post', int $max=3, string $prefix='', array $terms=array()) {

		// Type prefix. If the type is not post, we need a post type prefix.
		$type_prefix = ('post' !== $type ? $type . '_' : '');

		// Get our terms properly set up. If there are no terms, add category and tag.
		if (!$terms) {
			$terms = array(
				$type_prefix . 'category',
				($type_prefix ? $type_prefix : 'post_') . 'tag',
			);
		}

		// Start getting our options array together. First, add auto.
		$options = array(
			'auto'=>'Auto (most recent)',
		);

		// Now loop through our terms.
		foreach ($terms as $t) {
			$options[$t] = 'By ' . ucwords(str_replace('_', ' ', $t));
		}

		// And finally add in the curated option.
		$options['custom'] = 'Curated (manually pick which posts will show up).';

		// Start setting up our fields. First, all our related post type options.
		$fields = array(
			// Related type.
			Field::make('radio', $prefix . 'related_type', 'Type')
			->add_options($options),
		);

		// Now let's loop through our terms again.
		foreach ($terms as $t) {
			$fields[] = Field::make('association', $prefix . 'related_' . $t, ucwords(str_replace('_', ' ', $t)))
			->set_max(1)
			->set_types(array(array(
				'type'=>'term',
				'taxonomy'=>$t,
			), ))
			->set_conditional_logic(array(array(
				'field'=>$prefix . 'related_type',
				'value'=>$t,
			), ));
		}

		// And now add in the curated option.
		$fields[] = Field::make('association', $prefix . 'related_custom', 'Curated ' . ucwords($type) . 's')
			->set_max($max)
			->set_types(array(array(
				'type'=>'post',
				'post_type'=>$type,
			), ))
			->set_conditional_logic(array(array(
				'field'=>$prefix . 'related_type',
				'value'=>'custom',
			), ));

		return $fields;
	}


	/**
	 * Related Fields
	 *
	 * Returns an array of related post field definitions.
	 *
	 * @param array $args The arguments, as received from carbon fields.
	 * @param string $type The post type to retrieve.
	 * @param int $max The max amount of posts to return.
	 * @param string $prefix The prefix on the field names, if any.
	 * @return array $fields Array of posts.
	 */
	public static function get_related_posts(array $args, string $type='post', int $max=3, string $prefix='') {

		$posts = array();
		$strip = $prefix . 'related_';

		if (!isset($args['related_type'])) {
			$args['related_type'] = 'auto';
		}

		switch ($args[$prefix . 'related_type']) {
			case 'auto':
				$posts = get_posts(array(
					'post_type'=>$type,
					'numberposts'=>$max,
					'exclude'=>$args[$prefix . 'related_excludes'],
				));
				break;

			case 'custom':
				foreach ($args['related_custom'] as $p) {
					$posts[] = get_post($p['id']);
				}
				break;

			default:
				$term = $args[$prefix . 'related_' . $args[$prefix . 'related_type']];
				$term = $term[0];

				$options = array(
					'post_type'=>$type,
					'numberposts'=>$max,
					'tax_query'=>array(array(
						'taxonomy'=>$term['subtype'],
						'field'=>'term_id',
						'terms'=>array($term['id']),
					), ),
					'exclude'=>$args[$prefix . 'related_excludes'],
				);

				$posts = get_posts($options);

				break;
		}

		return $posts;
	}

	/**
	 * Objects to Arrays
	 *
	 * WordPress objects like WP_Post cannot be serialized for JS
	 * output; this will attempt to typecast them.
	 *
	 * @param mixed $var Variable.
	 * @return int Changes.
	 */
	public static function object_to_array(&$var) {
		$changes = 0;

		// Recurse?
		if (is_array($var)) {
			foreach ($var as $k=>$v) {
				$changes += static::object_to_array($var[$k]);
			}
		}
		elseif (
			is_object($var) &&
			(
				is_a($var, 'WP_Post') ||
				is_a($var, 'WP_Term') ||
				is_a($var, 'WP_User')
			)
		) {
			$var = (array) $var;
			return 1;
		}

		return $changes;
	}


	/**
	 * Get Terms by Post Type
	 *
	 * When getting terms using `get_terms()`, the argument `hide_empty`
	 * only excludes terms that are empty across all post types
	 * that share the taxonomy.
	 *
	 * This function allows you to get a list of terms that
	 * excludes empty terms for a specific post type.
	 * You can also use it to get terms from multiple taxonomies at once.
	 *
	 * Example: Both `product` and `inspiration` share the taxonomy `color`.
	 * The term `blue` is currently being used by an inspiration, but no book uses it yet.
	 * Using `get_terms_by_post_type(array('taxonomies'=>'color', 'post_types'=>'book'))` will return an array without `blue` in it,
	 * While `get_terms(array('taxonomy'=>'color','hide_empty'=>true))` will retun an array that includes `blue`.
	 *
	 * @param array $args The arguments passed to the function.
	 * @return mixed $results Array or null.
	 */
	public static function get_terms_by_post_type( array $args ) {

		// Make sure that our taxonomies and post types exist and that the variables are arrays.
		// If not, get out.
		if (!isset($args['taxonomies']) || !isset($args['post_types']) || !is_array($args['taxonomies']) || !is_array($args['post_types'])) {
			return false;
		}

		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT t.term_id from $wpdb->terms AS t
				INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
				INNER JOIN $wpdb->term_relationships AS r ON r.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id
				WHERE p.post_type IN('%s') AND tt.taxonomy IN('%s')
				GROUP BY t.term_id",
			join( "', '", $args['post_types'] ),
			join( "', '", $args['taxonomies'] )
		);

		$results = $wpdb->get_results( $query, ARRAY_A );

		if (!is_array($results) || !count($results)) {
			return null;
		}

		$term_ids = array();

		foreach ($results as $row) {
			$term_ids[] = (int) $row['term_id'];
		}

		$terms = get_terms(array(
			'include'=>$term_ids,
		));

		return $terms;

	}
}
