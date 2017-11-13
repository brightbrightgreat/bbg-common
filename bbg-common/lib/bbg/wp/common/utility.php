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
		'item_classes'=>'',
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
			'url'=>get_permalink($link['link_internal'][0]['id']),
			'target'=>'_self',
			'download'=>false,
		);

		switch ($link['link_type']) {
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
}
