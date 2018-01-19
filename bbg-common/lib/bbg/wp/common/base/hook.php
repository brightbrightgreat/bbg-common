<?php
/**
 * Abstract: Hooks
 *
 * This abstract is designed to make in-class hook binding more easily
 * automated. Simply call the ::init() function from functions.php to
 * register all actions and filters within.
 *
 * @package blobfolio/site
 * @author Blobfolio, LLC <hello@blobfolio.com>
 */

namespace bbg\wp\common\base;

use \blobfolio\common;

abstract class hook {
	// Cache-breaking string.

	const ASSET_VERSION = '0.7.1';

	// Default arguments for actions.
	const ACTION_OPTIONS = array(
		'priority'=>10,
		'arguments'=>0,
	);

	// Default arguments for filters.
	const FILTER_OPTIONS = array(
		'priority'=>10,
		'arguments'=>1,
	);

	// Actions and Filters: hook=>[callbacks].
	const ACTIONS = array();
	const FILTERS = array();



	// -----------------------------------------------------------------
	// Boostrap
	// -----------------------------------------------------------------

	protected static $_init = array();

	/**
	 * Init
	 *
	 * Bind all of our action and filter hooks.
	 *
	 * @return bool True.
	 */
	public static function init() {
		// Don't need to calculate this a thousand times.
		$class = get_called_class();

		// Don't need to do this twice.
		if (isset(static::$_init[$class])) {
			return true;
		}
		static::$_init[$class] = true;

		// Provide an action people can use before this init.
		do_action("$class::pre_init");

		// Bind actions.
		foreach (static::ACTIONS as $hook=>$callbacks) {
			foreach ($callbacks as $callback=>$args) {
				$args = common\data::parse_args($args, static::ACTION_OPTIONS);
				add_action(
					$hook,
					array($class, $callback),
					$args['priority'],
					$args['arguments']
				);
			}
		}

		// Bind filters.
		foreach (static::FILTERS as $hook=>$callbacks) {
			foreach ($callbacks as $callback=>$args) {
				$args = common\data::parse_args($args, static::FILTER_OPTIONS);
				add_filter(
					$hook,
					array($class, $callback),
					$args['priority'],
					$args['arguments']
				);
			}
		}

		// Special binding? This is up to the child class.
		static::special_init();

		// Provide an action people can use after this init.
		do_action("$class::post_init");

		return true;
	}

	/**
	 * Special Init
	 *
	 * This method can be extended by child themes if they have extra
	 * tasks to handle (that require a bit more than the automatic
	 * logic).
	 *
	 * @return void Nothing.
	 */
	protected static function special_init() {
		return;
	}

	// ----------------------------------------------------------------- end bootstrap
}
