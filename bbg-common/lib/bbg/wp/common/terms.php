<?php
/**
 * BBG Common: Terms
 *
 * Functions that are helpful when dealing with terms.
 *
 * @package brightbrightgreat/bbg-common
 * @author	Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \WP_Term;

class terms {

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


	/**
	 * Get Terms by Intersection
	 *
	 * Return an array of terms in $taxonomy that have $post_type in common with $term.
	 *
	 * Example: to find all the terms in the taxonomy color that have products in common
	 * with the term Book get_terms_by_intersection($book, 'color', 'product');
	 *
	 * @param WP_Term $term Matching term.
	 * @param string $taxonomy Target taxonomy.
	 * @param string $post_type The post type to target.
	 * @return array Terms.
	 */
	public static function get_terms_by_intersection(WP_Term $term, string $taxonomy, string $post_type='post') {
		$out = array();

		// Loosely check our arguments.
		if (!is_a($term, 'WP_Term') || !$taxonomy) {
				return $out;
		}

		// Step one, get matching posts.
		$posts = get_posts(array(
			'post_type'=>$post_type,
			'numberposts'=>-1,
			'numberposts'=>-1,
			'tax_query'=>array(
				array(
					'taxonomy'=>$term->taxonomy,
					'field'=>'id',
					'terms'=>$term->term_id,
					'include_children'=>false,
				),
			),
		));

		// No posts,
		if (!is_array($posts) || !count($posts)) {
			return $out;
		}

		// Step two, convert the above to IDs.
		$post_ids = array();
		foreach ($posts as $v) {
			$post_ids[] = (int) $v->ID;
		}

		// Step three, get all matching terms.
		$out = get_terms(array(
			'taxonomy'=>$taxonomy,
			'object_ids'=>$post_ids,
		));

		return $out;
	}
}
