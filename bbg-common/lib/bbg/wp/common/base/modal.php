<?php
/**
 * BBG Common: Modal
 *
 * This abstract class serves as both a starting point and bootstrap
 * for theme modals.
 *
 * Extended classes should override the ::print() method, and if the
 * display is conditional, the ::show() method.
 *
 * Bootstrapping happens via the bbg_common_modals action, which must be
 * triggered within the v-app tree.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common\base;

use \blobfolio\common\ref\cast as r_cast;
use \blobfolio\common\ref\sanitize as r_sanitize;

abstract class modal extends hook {

	// Transition attributes.
	const TRANSITION_ATTS = array(
		'name'=>'fade',
	);

	// Overlay attributes.
	const OVERLAY_ATTS = array(
		'class'=>'m:modal-overlay p:f m:overlay',
	);

	// The bootstrap action.
	const ACTIONS = array(
		'bbg_common_modals'=>array(
			'print_modals'=>null,
		),
	);



	// -----------------------------------------------------------------
	// Override Methods
	// -----------------------------------------------------------------

	/**
	 * Conditional Include
	 *
	 * If a modal's code is conditional (e.g. a login form), use this
	 * method to run the necessary checks. Return false to prevent
	 * inclusion of the modal.
	 *
	 * @return bool True/false.
	 */
	public static function is_valid() {
		return true;
	}

	/**
	 * The Modal Code
	 *
	 * Return the modal's inner HTML as a string.
	 *
	 * @return string HTML.
	 */
	public static function inner() {
		return '';
	}

	// ----------------------------------------------------------------- end overrides



	// -----------------------------------------------------------------
	// Bootstrap
	// -----------------------------------------------------------------

	/**
	 * The Outer Modal
	 *
	 * This takes the contents of ::inner() and wraps them in a
	 * <transition><div></div></transition>.
	 *
	 * @return string HTML.
	 */
	protected static function outer() {
		// Check whether the modal applies.
		if (!static::is_valid()) {
			return '';
		}

		// If no inner code is generated, do not render the modal.
		$inner = static::inner();
		if (!$inner) {
			return '';
		}

		// Parse the transition attributes.
		$outer = '<transition';
		if (is_array(static::TRANSITION_ATTS) && count(static::TRANSITION_ATTS)) {
			foreach (static::TRANSITION_ATTS as $k=>$v) {
				r_cast::string($k, true);
				$k = trim(strtolower($k));

				r_cast::string($v, true);
				r_sanitize::whitespace($v);

				$outer .= " $k=" . '"' . esc_attr($v) . '"';
			}
		}
		$outer .= '>';

		// Parse the overlay attributes.
		$outer .= '<div';
		if (is_array(static::OVERLAY_ATTS) && count(static::OVERLAY_ATTS)) {
			foreach (static::OVERLAY_ATTS as $k=>$v) {
				r_cast::string($k, true);
				$k = trim(strtolower($k));

				r_cast::string($v, true);
				r_sanitize::whitespace($v);

				// If these are classes, make sure m:modal-overlay is
				// there.
				if ('class' === $k) {
					if (false === strpos($v, 'm:modal-overlay')) {
						$v = "m:modal-overlay $v";
					}
				}

				$outer .= " $k=" . '"' . esc_attr($v) . '"';
			}
		}
		// If overlay isn't set, add the default class.
		else {
			$outer .= ' class="m:modal-overlay"';
		}
		$outer .= '>';

		// Throw in the inner contents, close it off, and return.
		return "{$outer}{$inner}</div></transition>";
	}

	/**
	 * Print All (relevant) Modals
	 *
	 * @return void Nothing.
	 */
	public static function print_modals() {
		// Figure out the modal base path.
		$base = defined('BBG_THEME_PATH') ? BBG_THEME_PATH : get_bloginfo('template_url');
		$base = trailingslashit($base) . 'lib/bbg/modal/';

		// If this directory doesn't exist, we have nothing to do.
		if (!@is_dir($base)) {
			return;
		}

		// Are there any files?
		$files = glob("$base*.php");
		if (!is_array($files) || !count($files)) {
			return;
		}

		// Loop through and enqueue the files.
		foreach ($files as $file) {
			$class = '\\bbg\\modal\\' . pathinfo($file, PATHINFO_FILENAME);

			// Make sure this is a proper modal class.
			if (is_subclass_of($class, 'bbg\\wp\\common\\base\\modal')) {
				echo $class::outer();
			}
		}
	}

	// -----------------------------------------------------------------
}
