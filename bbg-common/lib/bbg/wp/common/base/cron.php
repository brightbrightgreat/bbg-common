<?php
/**
 * BBG Common: CRON
 *
 * This file includes helpers for registering CRON tasks.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common\base;

abstract class cron {
	// This is the prefix applied to AJAX actions.
	const PREFIX = 'bbg_common_cron_';

	// A list of callbacks to register, slug=>frequency.
	const ACTIONS = array();

	// Keep track of initialization (run just once).
	protected static $_init = array();



	// -----------------------------------------------------------------
	// Setup
	// -----------------------------------------------------------------

	/**
	 * Init
	 *
	 * Bind our AJAX callbacks.
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

		foreach (static::ACTIONS as $action=>$frequency) {
			// Add the action.
			add_action(static::PREFIX . $action, array($class, $action));

			// Schedule it?
			if (!wp_next_scheduled(static::PREFIX . $action)) {
				wp_schedule_event(time(), $frequency, static::PREFIX . $action);
			}
		}

		return true;
	}

	// ----------------------------------------------------------------- end setup
}
