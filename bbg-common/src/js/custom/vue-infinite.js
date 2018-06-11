/**
 * Vue: Infinite Scroll
 *
 * This is a conditionally-enqueued infinite-scroll handler. Define
 * USE_INFINITE_JS in PHP to enqueue it.
 *
 * On the data side, there should be:
 * this.archive {}
 * this.posts []
 *
 * Refer to the documentation in hooks.php for the archive piece. It is
 * up to themes to handle population of posts.
 */
(function() {

	var BBGInfiniteVue = {
		/**
		 * Install
		 *
		 * @param {Vue} Vue Vue.
		 * @returns {void} Nothing.
		 */
		install: function(Vue) {

			Vue.mixin({
				/**
				 * Mounted
				 *
				 * @returns {void} Nothing.
				 */
				mounted: function() {
					// We want to set up an Observer to handle infinite
					// scroll, but only once, and only if needed.
					if (
						('undefined' !== typeof this.archive) &&
						(false === this.archive.mounted) &&
						(this.archive.pages > this.archive.page) &&
						('string' === typeof this.archive.marker) &&
						(-1 !== this.archive.base.indexOf('%#%'))
					) {
						// Find the marker.
						var vue = this;
						var archiveObserverEl = document.getElementById(this.archive.marker);

						// Update the mounted flag so we don't re-run.
						this.archive.mounted = true;

						// Add an observer if the element is a Go!
						if (archiveObserverEl) {
							// Make sure the offset is a number.
							this.archive.offset = parseInt(this.archive.offset, 10) || 0;

							var archiveObserver = new IntersectionObserver(function(entries, archiveObserver) {
								// Pull more data.
								if (entries[0].isIntersecting) {
									vue.infiniteScroll();
								}

								// Stop watching if we're done.
								if (true === vue.infiniteDone) {
									archiveObserver.unobserve(archiveObserverEl);
									archiveObserverEl.parentNode.removeChild(archiveObserverEl);
								}
							}, { root: null, rootMargin: this.archive.offset + 'px', threshold: 1 });
							archiveObserver.observe(archiveObserverEl);
						}
					}
				},
			});

			/**
			 * Infinite Scroll
			 *
			 * Pull post info from subsequent pages automatically.
			 *
			 * @returns {void} Nothing.
			 */
			Vue.prototype.infiniteLock = false;
			Vue.prototype.infiniteDone = false;
			Vue.prototype.infiniteScroll = function() {
				// Don't need to run.
				if ((true === this.infiniteLock) || !this.archive) {
					// This flag will kill the associated observer.
					if (!this.archive) {
						this.infiniteDone = true;
					}
					return false;
				}
				this.infiniteLock = true;

				// Increase the page.
				this.archive.page++;

				// We're done.
				if (this.archive.page > this.archive.pages) {
					this.infiniteDone = true;
					return false;
				}

				var url = this.archive.base.replace('%#%', this.archive.page);
				var vue = this;

				// Pull it via AJAX.
				this.$http.get(url).then(
					function(r) {
						if (r.ok) {
							try {
								// First put the contents somewhere.
								var el = document.createElement('html');
								el.innerHTML = r.body;

								// Parse it to see if we have our blobEnv var.
								var script = el.querySelector('#bbg-common-env');
								var i;

								// If we have positions, we probably have data.
								if (script) {
									var json = JSON.parse(script.textContent);

									// Add whatever posts.
									if (
										json.posts &&
										Array.isArray(json.posts) &&
										json.posts.length
									) {
										for (i = 0; i < json.posts.length; ++i) {
											vue.posts.push(json.posts[i]);
										}
										vue.infiniteLock = false;
										return true;
									}
								}

								// If we're here, we got nothing and are done.
								vue.infiniteLock = false;
								vue.infiniteDone = true;
								return false;
							} catch (Ex) {
								console.warn(Ex);
							}
						}
						// If there was an error, just assume we're done.
						else {
							vue.infiniteLock = false;
							vue.infiniteDone = true;
							return false;
						}
					},
					// Again, treat errors like being done.
					function() {
						vue.infiniteLock = false;
						vue.infiniteDone = true;
					}
				);
			};

		},
	};

	if ('undefined' !== typeof window && window.Vue) {
		window.Vue.use(BBGInfiniteVue);
	}
})();
