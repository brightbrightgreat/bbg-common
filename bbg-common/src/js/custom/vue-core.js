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

			// Infinite Scroll?
			if (
				(typeof this.archive !== 'undefined') &&
				(this.archive.pages > this.archive.page) &&
				this.archive.marker &&
				(typeof this.archive.marker === 'string') &&
				(typeof this.archive.base === 'string') &&
				(this.archive.base.indexOf('%#%') !== -1)
			) {
				// Find the marker.
				var archiveObserverEl = document.getElementById(this.archive.marker);
				if (archiveObserverEl) {
					// Make sure the offset is a number.
					this.archive.offset = parseInt(this.archive.offset, 10) || 0;

					var archiveObserver = new IntersectionObserver(function(entries, archiveObserver) {
						// Pull more data.
						if (entries[0].isIntersecting) {
							vue.infiniteScroll();
						}

						// Stop watching if we're done.
						if (vue.infiniteDone === true) {
							archiveObserver.unobserve(archiveObserverEl);
							archiveObserverEl.parentNode.removeChild(archiveObserverEl);
						}
					}, { root: null, rootMargin: this.archive.offset + 'px', threshold: 1 });
					archiveObserver.observe(archiveObserverEl);
				}
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

		/**
		 * Infinite Scroll
		 *
		 * Archive landings just pull results from subsequent pages as
		 * users near the end of the current list.
		 *
		 * @return void Nothing.
		 */
		infiniteLock: false,
		infiniteDone: false,
		infiniteScroll: function(){
			// Don't need to run.
			if((true === this.infiniteLock) || !this.archive) {
				// This flag will kill the associated observer.
				if (!this.archive) {
					this.infiniteDone = true;
				}
				return false;
			}
			this.infiniteLock = true;

			// Make sure our current posts list makes sense.
			if(!this.archive.posts || !Array.isArray(this.archive.posts)){
				this.archive.posts = [];
			}

			// Increase the page.
			this.archive.page++;

			// We're done.
			if(this.archive.page > this.archive.pages){
				this.infiniteDone = true;
				return false;
			}

			var url = this.archive.base.replace('%#%', this.archive.page),
				vue = this;

			// Pull it via AJAX.
			this.$http.get(url).then(
				function(r){
					if(r.ok){
						try {
							// First put the contents somewhere.
							var el = document.createElement('html');
							el.innerHTML = r.body;

							// Parse it to see if we have our blobEnv var.
							var script = el.getElementById('bbg-common-env').textContent,
								first = script.indexOf('bbgEnv={'),
								last = script.lastIndexOf('}'),
								i;

							// If we have positions, we probably have data.
							if ((first !== -1) && (last !== -1)) {
								var json = JSON.parse(script.substr(first + 6, last - first - 6));

								// Add whatever posts.
								if (
									json.archive &&
									json.archive.posts &&
									Array.isArray(json.archive.posts)
								) {
									for(i=0; i<json.archive.posts.length; i++){
										vue.archive.posts.push(json.archive.posts[i]);
									}
									vue.infiniteLock = false;
									return true;
								}
							}

							// If we're here, we got nothing and are done.
							vue.infiniteLock = false;
							vue.infiniteDone = true;
							return false;
						} catch(Ex) {}
					}
					// If there was an error, just assume we're done.
					else {
						vue.infiniteLock = false;
						vue.infiniteDone = true;
						return false;
					}
				},
				// Again, treat errors like being done.
				function(r){
					vue.infiniteLock = false;
					vue.infiniteDone = true;
				}
			);
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
