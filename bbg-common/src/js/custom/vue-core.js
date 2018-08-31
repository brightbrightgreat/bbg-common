/**
 * Core Vue
 *
 * This is the main Vue module. Themes should extend this through
 * plugins.
 */

/* global blobThrottle */
/* global Cookies */
/* global Vue */
(function() {

	// Pull the data from the page.
	var appData = document.getElementById('bbg-common-env');
	if (appData) {
		appData = JSON.parse(appData.textContent);
	}
	else {
		appData = {};
	}

	new Vue({
		el: '#vue-app',
		data: appData,
		methods: {
			/**
			 * Scroll Callback
			 *
			 * @returns {void} Nothing.
			 */
			onScroll: function() {
				var scrollY = window.scrollY || document.documentElement.scrollTop;

				this.window.scrollDirection = (this.window.scrolled < scrollY) ? 'down' : 'up';
				this.window.scrolled = scrollY;
			},

			/**
			 * Loaded Callback
			 *
			 * @returns {void} Nothing.
			 */
			onLoad: function() {
				var vue = this;

				this.onResize();

				// Make sure we have a nonce.
				this.session.n = Cookies.get('bbg_common_n') || '';
				this.session.n_checked = !!Cookies.get('bbg_common_n_checked');
				if (!this.session.n || !this.session.n_checked) {
					vue.heartbeat(vue);
				}
				else {
					setTimeout(function() {
						vue.heartbeat(vue);
					}, 600000);
				}

				// Wp Clean.
				this.wpClean();
				this.fitVids();
			},

			/**
			 * Resize Callback (throttled)
			 *
			 * @returns {void} Nothing.
			 */
			onResize: blobThrottle(function() {
				this.window.width = window.innerWidth;
				this.window.height = window.innerHeight;
				if (this.window.width === this.window.height) {
					this.window.aspect = 'square';
				}
				else if (this.window.width > this.window.height) {
					this.window.aspect = 'landscape';
				}
				else {
					this.window.aspect = 'portrait';
				}

				this.onScroll();
			}, 100),

			/**
			 * WP Cleanup
			 *
			 * @returns {void} Nothing.
			 */
			wpClean: function() {
				// Find images.
				var images = document.querySelectorAll('.t_wysiwyg img:not([data-wpcleaned])');
				if (images.length) {
					for (var i = 0; i < images.length; ++i) {
						images[i].setAttribute('data-wpcleaned', 1);

						// Ignore smileys and aligned images.
						if (
							images[i].classList.contains('wp-smiley') ||
						images[i].classList.contains('emoji') ||
						images[i].classList.contains('alignleft') ||
						images[i].classList.contains('alignright') ||
						images[i].classList.contains('aligncenter')
						) {
							continue;
						}

						// Add a class to unaligned parents.
						var parent = images[i].parentNode;
						if (
							('PICTURE' !== parent.tagName) &&
						!parent.classList.contains('alignleft') &&
						!parent.classList.contains('alignright') &&
						!parent.classList.contains('aligncenter')
						) {
							parent.classList.add('has-img');
						}
					}
				}

				// Find iframes.
				var iframes = document.querySelectorAll('iframe:not([data-wpcleaned])');
				if (iframes.length) {
					for (var j = 0; j < iframes.length; j++) {
						iframes[j].setAttribute('data-wpcleaned', 1);
						iframes[j].parentNode.classList.add('has-embed');
					}
				}
			},

			/**
		 	 * Auto-Scale Videos/iFrames
			 *
			 * @returns {void} Nothing.
			 */
			fitVids: function() {
				var iframes = document.querySelectorAll('iframe:not([data-fitvidsed])');
				if (!iframes.length) {
					return;
				}

				for (var i = 0; i < iframes.length; ++i) {
					var parent = iframes[i].parentNode;
					var offset = this.offset(iframes[i]);
					var width = parseInt(iframes[i].getAttribute('width')) || offset.width;
					var height = parseInt(iframes[i].getAttribute('height')) || offset.height;
					var aspectRatio = height / width;
					var padding = aspectRatio * 100 + '%';

					iframes[i].setAttribute('data-fitvidsed', 1);

					// Ignore Instagram.
					if (iframes[i].classList.contains('instagram-media')) {
					/* jshint ignore:end */
						continue;
					}

					iframes[i].style.cssText = 'position: absolute; top: 0; right: 0; bottom: 0; left: 0; width: 100%; height: 100%;';
					parent.style.cssText = 'position: relative; padding-top:' + padding + ';';
				}
			},

		}, // Methods.

		/**
		 * Mounted
		 *
		 * @returns {void} Nothing.
		 */
		mounted: function() {
			// We'll run this once as soon as Vue seems to be loaded.
			if (('undefined' !== typeof this.session) && (false === this.session.vue)) {
				this.session.vue = true;

				// Two quick polyfills.
				Element.prototype.isNodeList = function() { return false; };
				NodeList.prototype.isNodeList = HTMLCollection.prototype.isNodeList = function() { return true; };

				// Bind our scroll, resize, and load callbacks.
				window.addEventListener('scroll', this.onScroll);
				window.addEventListener('resize', this.onResize);
				document.addEventListener('DOMContentLoaded', this.onLoad);

				// Lastly, make sure we disable any active modal whenever
				// the ESCAPE key is pressed.
				var vue = this;
				document.addEventListener('keyup', function(e) {
					e = e || window.event;

					if (
						('key' in e && (('Escape' === e.key) || ('Esc' === e.key))) ||
						(27 === e.keyCode)
					) {
						vue.modal = '';
					}
				});
			}
		}, // Mounted.
	});


})();
