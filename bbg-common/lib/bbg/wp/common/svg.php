<?php
/**
 * SVG Helpers
 *
 * Handle SVG icons as efficiently as possible. Inline references will
 * be returned as <use> references, with an auto-sprite thrown in at the
 * the end containing what it needs to contain.
 *
 * @package brightbrightgreat/bbg-common
 * @author	Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \blobfolio\common;

class svg extends base\hook {
	const ACTIONS = array(
		'wp_footer'=>array(
			'sprite'=>array('priority'=>PHP_INT_MAX),
		),
	);

	protected static $_sprite = array();

	/**
	 * Get Icon
	 *
	 * Return an SVG icon for inline use.
	 *
	 * @param string $icon Icon filename.
	 * @param array $classes Classes.
	 * @return string|bool Icon or false.
	 */
	public static function get_icon(string $icon, $classes=null) {
		// Bad icon.
		if (!defined('BBG_THEME_PATH') || !file_exists(BBG_THEME_PATH . "dist/svgs/$icon.svg")) {
			return false;
		}

		// Load the data.
		if (!isset(static::$_sprite[$icon])) {
			$content = common\image::clean_svg(
				BBG_THEME_PATH . "dist/svgs/$icon.svg",
				array(
					'strip_style'=>true,
					'fix_dimensions'=>true,
					'save'=>true,
					'strip_data'=>true,
					'strip_title'=>true,
				)
			);
			if (!$content) {
				return false;
			}

			// Make sure there aren't lingering classes.
			if (false !== strpos($content, 'class')) {
				// Log a warning.
				error_log("SVG Icon $icon contains CSS classes; these properties should be inlined as non-style attributes.");

				// We'll just assume any styles are meant to be about
				// fill color.
				$content = preg_replace('/(class\s*=\s*"[^"]*")/i', 'fill="currentColor"', $content);
			}

			// Reopen with DOMDocument.
			if (false === ($dom = common\dom::load_svg($content))) {
				return false;
			}

			// Our base data.
			static::$_sprite[$icon] = array(
				'id'=>"i-$icon",
				'viewBox'=>'',
				'content'=>'',
			);

			// Parse out the content and viewBox.
			$svg = $dom->getElementsByTagName('svg')->item(0);
			static::$_sprite[$icon]['viewBox'] = (string) $svg->getAttribute('viewBox');
			static::$_sprite[$icon]['content'] = common\dom::innerhtml($svg, true, LIBXML_NOBLANKS);
		}

		// Parse classes.
		common\ref\cast::array($classes);
		$classes[] = "i_$icon";

		return '<svg class="' . esc_attr(implode(' ', $classes)) . '"><use xlink:href="#' . static::$_sprite[$icon]['id'] . '"></use></svg>';
	}

	/**
	 * Get Sprite
	 *
	 * Output an SVG sprite of every icon we've used on this page.
	 *
	 * @return void Nothing.
	 */
	public static function sprite() {
		// Nothing to do?
		if (!count(static::$_sprite)) {
			return;
		}

		// Our sprite code!
		echo '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="hide-safe">';
		foreach (static::$_sprite as $svg) {
			echo '<symbol ' . ($svg['viewBox'] ? 'viewBox="' . esc_attr($svg['viewBox']) . '" ' : '') . 'id="' . esc_attr($svg['id']) . '">' . $svg['content'] . '</symbol>';
		}
		echo '</svg>';
	}
}
