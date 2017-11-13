<?php
/**
 * BBG Common: Partial
 *
 * This abstract class contains all the basics needed for a partial;
 * actual (specific) partials should extend this.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common\base;

use \blobfolio\common\data;
use \blobfolio\common\ref\cast as r_cast;
use \blobfolio\common\ref\format as r_format;

abstract class partial extends hook {

	// The basic $args data structure.
	const TEMPLATE = array();

	// The key in $args containing wrapper classes.
	const WRAPPER_CLASSES_KEY = 'section_classes';

	// Default wrapper classes.
	const WRAPPER_CLASSES = array();

	// Actions: hook=>[callbacks].
	const ACTIONS = array(
	);

	// Filters: hook=>[callbacks].
	const FILTERS = array(
		'bbg_common_js_env'=>array(
			'js_env'=>null,
		),
	);

	protected static $data = array(); // Collected Vue data (if any).
	protected static $ids = array();  // Unique IDs generated (if any).



	// -----------------------------------------------------------------
	// User Methods
	// -----------------------------------------------------------------

	/**
	 * Get Partial as String
	 *
	 * Crunch and return the HTML as a string.
	 *
	 * @param mixed $args Arguments.
	 * @return string Partial.
	 */
	public static function get($args=null) {
		// Start by sanitizing the arguments. If this fails, we're done.
		if (false === ($args = static::sanitize_arguments($args))) {
			return '';
		}

		return static::build($args);
	}

	/**
	 * Print Partial Directly
	 *
	 * Crunch and send the HTML to STDOUT.
	 *
	 * @param mixed $args Arguments.
	 * @return void Nothing.
	 */
	public static function print($args=null) {
		// Start by sanitizing the arguments. If this fails, we're done.
		if (false === ($args = static::sanitize_arguments($args))) {
			return;
		}

		echo static::build($args);
	}

	// ----------------------------------------------------------------- end user methods



	// -----------------------------------------------------------------
	// Partial Building
	// -----------------------------------------------------------------

	/**
	 * Sanitize Arguments
	 *
	 * This function will standardize the arguments passed to the build
	 * method so that life can continue. Return `FALSE` to short-circuit
	 * and abort the attempt.
	 *
	 * @param mixed $args Arguments.
	 * @return bool True/false.
	 */
	protected static function sanitize_arguments(&$args=null) {
		$args = data::parse_args($args, static::TEMPLATE);
		return $args;
	}

	/**
	 * Build the Partial
	 *
	 * This is a meta function that generates an ID, builds content, and
	 * wraps it all up with a bow.
	 *
	 * @param mixed $args Arguments.
	 * @return string Partial.
	 */
	protected static function build(&$args=null) {
		$id = static::random_id();
		$classes = isset($args[static::WRAPPER_CLASSES_KEY]) ? $args[static::WRAPPER_CLASSES_KEY] : null;

		// Our content nugget.
		$str = '';

		// Compile the main partial content.
		static::build_content($str, $id, $args);

		// Save our js data to the environment.
		static::save_env($id, $args);

		// Wrap it like a burrito.
		static::build_wrapper($str, $id, $classes);

		// And done!
		return $str;
	}

	/**
	 * Build the Content
	 *
	 * This is where specificity happens. Child classes should be
	 * overloading this method.
	 *
	 * @param string $str Content.
	 * @param string $id ID.
	 * @param array $args Arguments.
	 * @return bool True.
	 */
	protected static function build_content(string &$str, string $id, array $args) {
		return true;
	}

	/**
	 * Generic Partial Wrapper
	 *
	 * Generated (specific) code is often wrapped inside a basic
	 * <section>.
	 *
	 * @param string $str Content.
	 * @param string $id ID.
	 * @param array $classes Classes.
	 * @return bool True.
	 */
	protected static function build_wrapper(string &$str, string $id, $classes=null) {
		// Set up classes.
		r_format::list_to_array($classes, ' ');
		$classes = array_filter($classes);
		if (!count($classes)) {
			$classes = static::WRAPPER_CLASSES;
		}

		// Replace these with...
		$from = array(
			'%CLASSES%',
			'%ID%',
			'%CONTENT%',
			'id=""',
		);

		// These...
		$to = array(
			esc_attr(implode(' ', $classes)),
			$id,
			$str,
			'',
		);

		$str = str_replace(
			$from,
			$to,
			'<section id="%ID%" class="%CLASSES%">%CONTENT%</section>'
		);

		return true;
	}

	/**
	 * Generate an ID
	 *
	 * A given partial might be run more than once on a page, and might
	 * have JS data that needs to be associated with one incarnation vs
	 * another. A random ID is an easy way to segment the data.
	 *
	 * This should only be called ONCE during a single run of the build
	 * process.
	 *
	 * @return string ID.
	 */
	protected static function random_id() {
		$id = 't' . data::random_string(3);
		while (in_array($id, static::$ids, true)) {
			$id = 't' . data::random_string(3);
		}

		// Remember this for the session.
		static::$ids[] = $id;
		return $id;
	}

	// ----------------------------------------------------------------- end building



	// -----------------------------------------------------------------
	// System
	// -----------------------------------------------------------------

	/**
	 * Merge Env Data
	 *
	 * Add some data to the master list we've got going.
	 *
	 * @param string $id Partial ID.
	 * @param array $data Data.
	 * @return bool True/false.
	 */
	protected static function save_env(string $id, array &$data) {
		if (!count($data)) {
			return false;
		}

		// Easy enough! Shove it in.
		foreach ($data as $k=>$v) {
			static::$data[$id][$k] = $v;
		}

		return true;
	}

	/**
	 * JS Data Callback
	 *
	 * This will export any collected data when the env filter is run,
	 * exposing its knowledge to Javascript.
	 *
	 * @see bbg\wp\common\hook::js_env()
	 *
	 * @param array $data Data.
	 * @return array Data.
	 */
	public static function js_env(array $data=array()) {
		// Combine if needed.
		if (count(static::$data)) {
			foreach (static::$data as $k=>$v) {
				$data['partial'][$k] = $v;
			}

			// Reset our static data to free up some memory.
			static::$data = array();
		}

		return $data;
	}

	// ----------------------------------------------------------------- end system
}
