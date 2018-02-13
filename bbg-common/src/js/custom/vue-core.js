/**
 * Core Vue
 *
 * This is the main Vue module. Themes should extend this through
 * plugins.
 */
(function(){

// Pull the data from the page.
var appData = document.getElementById('bbg-common-env');
if (appData) {
	appData = JSON.parse(appData.textContent);
}
else {
	appData = {};
}

var app = new Vue({
	el: '#vue-app',
	data: appData,
	methods: {

		// Runs onScroll.
		onScroll: function(){
			this.window.scrollDirection = (this.window.scrolled < window.scrollY) ? 'down' : 'up';
			this.window.scrolled = window.scrollY;
		},

		// Runs once after everything has loaded.
		onLoad: function(){
			var vue = this;

			this.onResize();

			// Make sure we have a nonce.
			this.session.n = Cookies.get('bbg_common_n') || '';
			if(!this.session.n) {
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

		// Runs when the window has been resized. This is throttled for
		// performance reasons.
		onResize: blobThrottle(function(){
			this.window.width = window.innerWidth;
			this.window.height = window.innerHeight;
			if(this.window.width === this.window.height) {
				this.window.aspect = 'square';
			}
			else if(this.window.width > this.window.height) {
				this.window.aspect = 'landscape';
			}
			else {
				this.window.aspect = 'portrait';
			}

			this.onScroll();
		}, 100),


		// Some WordPress-specific items we need to take care of.
		wpClean: function() {
			// Find images.
			var images = document.querySelectorAll('.t_wysiwyg img:not([data-wpcleaned])');
			if (images.length) {
				for (var i=0; i<images.length; i++) {
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
					if(
						(parent.tagName !== 'PICTURE') &&
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
				for (var j=0; j<iframes.length; j++) {
					iframes[j].setAttribute('data-wpcleaned', 1);
					iframes[j].parentNode.classList.add('has-embed');
				}
			}
		},

		fitVids: function() {
			var iframes = document.querySelectorAll('iframe:not([data-fitvidsed])');
			if (!iframes.length) {
				return;
			}

			for (var i=0; i<iframes.length; i++) {
				var parent = iframes[i].parentNode,
					offset = this.offset(iframes[i]),
					width = offset.width,
					height = offset.height,
					aspectRatio = height / width,
					padding = aspectRatio * 100 + '%';

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

	mounted: function() {
		// We'll run this once as soon as Vue seems to be loaded.
		if ((typeof this.session !== 'undefined') && (this.session.vue === false)) {
			this.session.vue = true;

			// Set up our smooth scroller.
			smoothScroll.init({
				selector: 'a[href^="#"]',
				speed: 1000,
				offset: 170
			});

			// Maybe we should jump to somewhere on the page?
			if(window.location.hash){
				try {
					var anchor = document.querySelector(window.location.hash);
					if(anchor){
						smoothScroll.animateScroll(anchor);
					}
				} catch(Ex) {}
			}

			// Two quick polyfills.
			Element.prototype.isNodeList = function() { return false; };
			NodeList.prototype.isNodeList = HTMLCollection.prototype.isNodeList = function(){ return true; };

			// Bind our scroll, resize, and load callbacks.
			window.addEventListener('scroll', this.onScroll);
			window.addEventListener('resize', this.onResize);
			document.addEventListener('DOMContentLoaded', this.onLoad);

			// Lastly, make sure we disable any active modal whenever
			// the ESCAPE key is pressed.
			var vue = this;
			document.addEventListener('keyup', function(e) {
				e = e || window.event;

				if(
					('key' in e && ((e.key === "Escape") || (e.key === "Esc"))) ||
					e.keyCode == 27
				) {
					vue.modal = '';
				}
			});
		}
	}, // Mounted.
});


})();
