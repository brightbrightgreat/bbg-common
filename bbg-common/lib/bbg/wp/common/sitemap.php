<?php
/**
 * BBG Common: Sitemap
 *
 * Generate an XML Sitemap for a site, nice and easy.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \blobfolio\common\data;
use \blobfolio\common\ref\format as r_format;
use \blobfolio\common\ref\sanitize as r_sanitize;

class sitemap extends base\hook {
	const ACTIONS = array(
		'wp'=>array(
			'sitemap_init'=>array('priority'=>5),
		),
	);

	const SITEMAP_FREQUENCIES = array(
		'always',
		'hourly',
		'daily',
		'weekly',
		'monthly',
		'yearly',
		'never',
	);

	// Default post types.
	const SITEMAP_TYPES = array(
		'page'=>array(
			'priority'=>0.8,
			'frequency'=>'weekly',
		),
		'post'=>array(
			'priority'=>0.6,
			'frequency'=>'monthly',
		),
	);

	// phpcs:disable
	const SITEMAP_XML = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="%s"?>
<!-- generated-on="%s" -->
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">%s</urlset>';
	// phpcs:enable

	const SITEMAP_DATETIME = 'Y-m-d\TH:i:s+00:00';



	/**
	 * Display Sitemap
	 *
	 * @return void Nothing.
	 */
	public static function sitemap_init() {
		// If we aren't asking for a sitemap, abort.
		if (
			!defined('BBG_COMMON_SITEMAP') ||
			!BBG_COMMON_SITEMAP ||
			!isset($_SERVER['REQUEST_URI']) ||
			('/sitemap.xml' !== $_SERVER['REQUEST_URI'])
		) {
			return;
		}

		// Do we have any URLs?
		$urls = static::sitemap_post_urls() + static::sitemap_term_urls();

		$sitemap = sprintf(
			static::SITEMAP_XML,
			BBGCOMMON_PLUGIN_URL . 'css/sitemap.xsl',
			date(static::SITEMAP_DATETIME),
			implode("\n", $urls)
		);

		header('Content-Type: text/xml; charset=utf-8');
		echo $sitemap;
		exit;
	}

	/**
	 * Build Sitemap Entries (Posts)
	 *
	 * @return array XML.
	 */
	protected static function sitemap_post_urls() {
		global $wpdb;
		$out = array();

		// Post types.
		$post_types = (array) apply_filters(
			'bbg_common_sitemap_types',
			static::SITEMAP_TYPES
		);
		$default = array(
			'priority'=>0.2,
			'frequency'=>'monthly',
		);
		foreach ($post_types as $k=>$v) {
			if (!preg_match('/^[a-z\d\-_]+$/i', $k)) {
				unset($post_types[$k]);
			}
			else {
				$post_types[$k] = data::parse_args($v, $default);
			}
		}
		if (!count($post_types)) {
			return $out;
		}

		// Clean type slugs for queries.
		$types = "'" . implode("','", array_keys($post_types)) . "'";

		// Exclude posts?
		$exclude = (array) apply_filters('bbg_common_sitemap_exclude', array(0));
		foreach ($exclude as $k=>$v) {
			$exclude[$k] = (int) $v;
			if ($exclude[$k] <= 0) {
				unset($exclude[$k]);
			}
		}
		if (!count($exclude)) {
			$exclude = array(0);
		}
		else {
			$exclude = array_unique($exclude);
			sort($exclude);
		}
		$exclude = implode(',', $exclude);

		// Whether or not we should pull featured images.
		$include_images = apply_filters('bbg_common_sitemap_images', true);
		// If we're looking for images, we need some joins.
		if ($include_images) {
			$query = "
				SELECT
					p.ID AS `post_id`,
					p.post_type,
					p.post_title,
					p.post_date_gmt AS `date_created`,
					p.post_modified_gmt AS `date_updated`,
					IFNULL(m.meta_value, 0) AS `image_id`,
					i.post_title AS `image_title`,
					i.post_excerpt AS `image_caption`
				FROM
					`{$wpdb->posts}` AS p LEFT JOIN
					`{$wpdb->postmeta}` AS m ON
						m.post_id=p.ID AND
						m.meta_key='_thumbnail_id' LEFT JOIN
					`{$wpdb->posts}` AS i ON
						m.meta_value=i.ID AND
						i.post_type='attachment'
				WHERE
					p.post_status = 'publish' AND
					p.post_password = '' AND
					p.post_type IN ($types) AND
					NOT(p.ID IN ($exclude))
				ORDER BY p.ID ASC
			";
		}
		// Otherwise we can do something simpler.
		else {
			$query = "
				SELECT
					`ID` AS `post_id`,
					`post_type`,
					`post_title`,
					`post_date_gmt` AS `date_created`,
					`post_modified_gmt` AS `date_updated`
				FROM `{$wpdb->posts}`
				WHERE
					`post_status` = 'publish' AND
					`post_password` = '' AND
					`post_type` IN ($types) AND
					NOT(`ID` IN ($exclude))
				ORDER BY `ID` ASC
			";
		}

		// Search!
		$default = array(
			'post_id'=>0,
			'post_type'=>'',
			'post_title'=>'',
			'date_created'=>'0000-00-00 00:00:00',
			'date_updated'=>'0000-00-00 00:00:00',
			'image_id'=>0,
			'image_title'=>'',
			'image_caption'=>'',
		);
		$dbResult = $wpdb->get_results($query, ARRAY_A);
		if (isset($dbResult[0])) {
			// We'll need to escape certain characters for XML.
			$replace_keys = array('&', '"', "'", '<', '>');
			$replace_values = array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;');

			$home_id = (int) get_option('page_on_front');
			$blog_id = (int) get_option('page_for_posts');

			// Loop through results.
			foreach ($dbResult as $Row) {
				$Row = data::parse_args($Row, $default);
				if (!$Row['post_id'] || !isset($post_types[$Row['post_type']])) {
					continue;
				}

				r_format::decode_entities($Row['post_title']);

				$url = \get_permalink($Row['post_id']);
				if (!$url || isset($out[$url])) {
					continue;
				}
				$line = array(
					'loc'=>$url,
					'lastmod'=>date(
						static::SITEMAP_DATETIME,
						strtotime(max($Row['date_created'], $Row['date_updated']))
					),
					'changefreq'=>$post_types[$Row['post_type']]['frequency'],
					'priority'=>$post_types[$Row['post_type']]['priority'],
				);

				// The home page?
				if ($home_id === $Row['post_id']) {
					$line['changefreq'] = 'daily';
					$line['priority'] = 1.0;
				}

				// Are we doing images?
				if ($include_images && $Row['image_id']) {
					$tmp = array(
						'image:loc'=>wp_get_attachment_url($Row['image_id']),
					);

					// Try to make sure images have a title of some
					// sort.
					if (!$Row['image_title']) {
						if ($Row['post_title']) {
							$Row['image_title'] = $Row['post_title'];
						}
						else {
							$Row['image_title'] = basename($tmp['image:loc']);
						}
					}

					// Images may or may not have useful captions.
					foreach (array('caption', 'title') as $v) {
						r_format::decode_entities($Row["image_$v"]);

						if ($Row["image_$v"]) {
							$tmp["image:$v"] = $Row["image_$v"];
						}
					}

					if ($tmp['image:loc']) {
						$line['image:image'] = $tmp;
					}
				}

				// Build it!
				$line_out = array();
				foreach ($line as $k=>$v) {
					switch ($k) {
						// Images require a recursive XMLization.
						case 'image:image':
							$tmp = array();
							foreach ($line[$k] as $k2=>$v2) {
								$line[$k][$k2] = str_replace($replace_keys, $replace_values, $line[$k][$k2]);
								$tmp[] = "<$k2>{$line[$k][$k2]}</$k2>";
							}
							$line[$k] = implode("\n", $tmp);
							break;
						// Priority should have one decimal.
						case 'priority':
							r_sanitize::to_range($line[$k], 0.1, 1.0);
							$line[$k] = number_format($line[$k], 1, '.', '');
							break;
						default:
							$line[$k] = str_replace(
								$replace_keys,
								$replace_values,
								$line[$k]
							);
					}

					$line_out[] = "<$k>{$line[$k]}</$k>";
				}
				$out[$url] = '<url>' . implode("\n", $line_out) . '</url>';
			}
		}

		ksort($out);
		return $out;
	}

	/**
	 * Build Sitemap Entries (Posts)
	 *
	 * @return array XML.
	 */
	protected static function sitemap_term_urls() {
		global $wpdb;
		$out = array();

		// Post types.
		$taxonomies = (array) apply_filters(
			'bbg_common_sitemap_taxonomies',
			array()
		);
		$default = array(
			'priority'=>0.2,
			'frequency'=>'monthly',
		);
		foreach ($taxonomies as $k=>$v) {
			if (!preg_match('/^[a-z\d\-_]+$/i', $k)) {
				unset($taxonomies[$k]);
			}
			else {
				$taxonomies[$k] = data::parse_args($v, $default);
			}
		}
		if (!count($taxonomies)) {
			return $out;
		}

		// Get all terms.
		$terms = get_terms(array('taxonomy'=>array_keys($taxonomies)));
		if (is_array($terms)) {
			// We'll need to escape certain characters for XML.
			$replace_keys = array('&', '"', "'", '<', '>');
			$replace_values = array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;');

			foreach ($terms as $v) {
				$url = get_term_link($v);

				$line = array(
					'loc'=>$url,
					'lastmod'=>date(
						static::SITEMAP_DATETIME,
						strtotime(current_time('Y-m-d 00:00:00'))
					),
					'changefreq'=>$taxonomies[$v->taxonomy]['frequency'],
					'priority'=>$taxonomies[$v->taxonomy]['priority'],
				);

				$line_out = array();
				foreach ($line as $k=>$v) {
					switch ($k) {
						// Priority should have one decimal.
						case 'priority':
							r_sanitize::to_range($line[$k], 0.1, 1.0);
							$line[$k] = number_format($line[$k], 1, '.', '');
							break;
						default:
							$line[$k] = str_replace(
								$replace_keys,
								$replace_values,
								$line[$k]
							);
					}

					$line_out[] = "<$k>{$line[$k]}</$k>";
				}

				$out[$url] = '<url>' . implode("\n", $line_out) . '</url>';
			}
		}

		ksort($out);
		return $out;
	}
}
