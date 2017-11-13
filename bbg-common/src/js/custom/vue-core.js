/**
 * Core Vue
 *
 * This is the main Vue module. Themes should extend this through
 * plugins.
 */
(function(){

var app = new Vue({
	el: '#vue-app',
	data: window.bbgEnv || {},
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
				}, 1800000);
			}
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
