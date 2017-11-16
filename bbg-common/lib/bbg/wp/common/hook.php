<?php
/**
 * BBG Common: Hooks
 *
 * All action and filter binding for the plugin happens by calling
 * ::init() once after load.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \blobfolio\common\data;
use \blobfolio\common\ref\sanitize as r_sanitize;

class hook extends base\hook {

	// Actions: hook=>[callbacks].
	const ACTIONS = array(
		'wp_enqueue_scripts'=>array(
			'scripts'=>array('priority'=>100),
		),
		'wp_footer'=>array(
			'gtm_fallback'=>null,
			'js_env'=>array('priority'=>0),
		),
		'wp_head'=>array(
			'gtm'=>array('priority'=>550),
			'gtm_data'=>array('priority'=>500),
			'inline_css'=>null,
		),
		'after_setup_theme'=>array(
			'theme_config'=>null,
		),
	);

	// Filters: hook=>[callbacks].
	const FILTERS = array(
		'body_class'=>array(
			'body_class'=>null,
		),
	);

	protected static $gtm;
	protected static $vue_deps = array();



	/**
	 * Special Init
	 *
	 * Extra init methods that require a bit of manual conditioning.
	 *
	 * @return void Nothing.
	 */
	protected static function special_init() {
		// Discourage search engines from indexing certain pages. Seems
		// like as good a place as any to add it.
		if (
			BBG_TESTMODE ||
			(
				isset($_SERVER['REQUEST_URI']) &&
				preg_match('#^/(account|checkout)#', $_SERVER['REQUEST_URI'])
			)
		) {
			add_action('wp_head', 'wp_no_robots');
		}
	}



	// -----------------------------------------------------------------
	// Header Business
	// -----------------------------------------------------------------

	/**
	 * Inline CSS
	 *
	 * These are small styles that can be injected into the header.
	 *
	 * @return void Nothing.
	 */
	public static function inline_css() {
		ob_start();
		?>
		<style>
			[v-cloak] { display: none; }
		</style>
		<?php
		$out = ob_get_clean();
		r_sanitize::whitespace($out);
		echo "\n$out\n";
	}

	/**
	 * Add Vue Dependency
	 *
	 * For Vue in particular, there might be dependencies in the plugin
	 * and theme. Since the plugin handles the actual enqueing of Vue,
	 * this method can be used by themes to make sure their deps are
	 * properly registered.
	 *
	 * @param string $uri URI.
	 * @return void Nothing.
	 */
	public static function add_vue_dep(string $uri) {
		static::$vue_deps[] = $uri;
	}

	/**
	 * Enqueue Scripts
	 *
	 * @return void Nothing.
	 */
	public static function scripts() {
		global $post;

		$js_url = BBGCOMMON_PLUGIN_URL . 'js/';

		// Global plugins.
		wp_register_script(
			'bbg-common-plugins-js',
			"{$js_url}lib.min.js",
			array(),
			static::ASSET_VERSION,
			true
		);
		wp_enqueue_script('bbg-common-plugins-js');

		// Our main Vue bundle.
		$vue = BBG_TESTMODE ? "{$js_url}vue-testmode.min.js" : "{$js_url}vue.min.js";
		wp_register_script(
			'bbg-common-vue-deps-js',
			$vue,
			array(),
			static::ASSET_VERSION,
			true
		);
		// Bump this one thing to the top of the list.
		array_unshift(static::$vue_deps, 'bbg-common-vue-deps-js');

		// A phone number format helper. This conditionally enqueued
		// because of its size. To turn it on, define the USE_PHONE_JS
		// constant.
		wp_register_script(
			'blob-phone-js',
			"{$js_url}blob-phone.min.js",
			array('bbg-common-vue-deps-js'),
			static::ASSET_VERSION,
			true
		);
		if (defined('USE_PHONE_JS') && USE_PHONE_JS) {
			static::$vue_deps[] = 'blob-phone-js';
		}

		// Our main Vue file. This is enqueued last, and depends on all
		// other Vue-related pieces. To add a dependency from a theme,
		// etc., use the 'bbg_common_vue_deps' filter.
		wp_register_script(
			'bbg-common-vue-js',
			"{$js_url}vue-core.min.js",
			static::$vue_deps,
			static::ASSET_VERSION,
			true
		);
		wp_enqueue_script('bbg-common-vue-js');
	}

	// ----------------------------------------------------------------- end header



	// -----------------------------------------------------------------
	// Footer Business
	// -----------------------------------------------------------------

	/**
	 * JS Environment Data
	 *
	 * This exposes data to Javascript frameworks like Vue. This hook
	 * will trigger at the very *beginning* of the 'wp_footer' action.
	 *
	 * Up until that point, any arbitrary data can be bound to this by
	 * hooking into the 'bbg_common_js_env' filter.
	 *
	 * @return void Nothing.
	 */
	public static function js_env() {
		// Start with some default data.
		$data = array(
			'forms'=>array(),
			'menu'=>'',
			'modal'=>'',
			'session'=>array(
				'ajaxurl'=>ajax::get_url(),
				'n'=>ajax::get_soft_nonce(),
				'vue'=>false,
			),
			'status'=>array(
				'message'=>'',
				'type'=>'info',
				'timeout'=>null,
			),
			'window'=>array(
				'aspect'=>'landscape',
				'height'=>0,
				'scrollDirection'=>'down',
				'scrolled'=>0,
				'width'=>0,
			),
		);

		// Merge in any other data that might be floating around.
		$data = apply_filters('bbg_common_js_env', $data);

		// Fix UTF-8 and print.
		r_sanitize::utf8($data);
		echo "\n<script>var bbgEnv=" . json_encode($data) . ";</script>\n";
	}

	// ----------------------------------------------------------------- end footer


	// -----------------------------------------------------------------
	// General Config
	// -----------------------------------------------------------------

	/**
	 * Misc Theme Settings
	 *
	 * @return void Nothing.
	 */
	public static function theme_config() {

		// Disable the admin bar.
		show_admin_bar( false );

		// Use modern WP titles.
		add_theme_support( 'title-tag' );

		// ---------------------------------------------------------------------
		// Images
		// ---------------------------------------------------------------------
		add_theme_support('post-thumbnails');

		add_filter('jpeg_quality', function($arg) {
			return 100;
		});

		// OG image.
		add_image_size('og-img', 1200, 630, true);
	}


	/**
	 * Extend Body Classes
	 *
	 * @param array $classes Classes.
	 * @return array Classes.
	 */
	public static function body_class(array $classes) {
		if (is_singular()) {
			global $post;
			$classes[] = "type:{$post->post_type}";
			$classes[] = "slug:{$post->post_name}";
			$classes[] = "{$post->post_type}:{$post->post_name}";
		}

		global $template;
		$file_slug = basename($template);
		$file_slug = preg_replace('/\.php$/i', '', $file_slug);
		$classes[] = 'template:' . $file_slug;

		$classes[] = (BBG_TESTMODE ? 'mode:test' : 'mode:live');

		return $classes;
	}

	// ----------------------------------------------------------------- end config



	// -----------------------------------------------------------------
	// Google Tag Manager
	// -----------------------------------------------------------------

	/**
	 * Has GTM?
	 *
	 * GTM code is only relevant if the site uses it. We should also
	 * disable it for WP users and testing sites.
	 *
	 * @return string|bool Code or false.
	 */
	protected static function get_gtm() {
		if (is_null(static::$gtm)) {
			static::$gtm = carbon_get_theme_option('gtm');
			if (!static::$gtm) {
				static::$gtm = false;
			}
		}

		if (
			static::$gtm &&
			!is_user_loged_in() &&
			!BBG_TESTMODE
		) {
			return static::$gtm;
		}

		return false;
	}

	/**
	 * Main GTM Tracking
	 *
	 * @return void Nothing.
	 */
	public static function gtm() {
		// Not applicable?
		if (false === ($gtm = static::get_gtm())) {
			return;
		}
		?>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?=$gtm?>');</script>
		<!-- End Google Tag Manager -->
		<?php
	}

	/**
	 * GTM DataLayer
	 *
	 * This populates various information about the request, and can be
	 * extended by themes to provide more/different data.
	 *
	 * @return void Nothing.
	 */
	public static function gtm_data() {
		// Not applicable?
		if (false === ($gtm = static::get_gtm())) {
			return;
		}

		global $template;

		$out = array(
			'pageSlug'=>'',
			'pageType'=>'other',
			'pageTemplate'=>$template ? basename($template) : '',
			'pageId'=>0,
		);

		// Single page.
		if (is_singular()) {
			global $post;
			$out['pageSlug'] = $post->post_name;
			$out['pageId'] = $post->ID;
			$out['pageType'] = $post->post_type;
		}
		// Archive.
		elseif (is_archive()) {
			$out['pageType'] = 'archive';
		}
		// Nothing.
		elseif (is_404()) {
			$out['pageType'] = '404';
		}

		// Let themes modify this data.
		$data = array($out);
		$data = apply_filters('bbg_common_gtm_datalayer', $data);

		if (is_array($data) && count($data)) {
			r_sanitize::utf8($data);

			echo '<!-- gtm data --><script>var dataLayer = dataLayer || [];';
			foreach ($data as $v) {
				if (is_array($v) && count($v)) {
					echo "\ndataLayer.push(" . json_encode($v) . ');';
				}
			}
			echo '</script>';
		}
	}

	/**
	 * Fallback GTM Code
	 *
	 * @return void Nothing.
	 */
	public static function gtm_fallback() {
		// Not applicable?
		if (false === ($gtm = static::get_gtm())) {
			return;
		}
		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MVF523C" height="0" width="0" style="display:none; visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
	}

	// ----------------------------------------------------------------- end gtm
}
