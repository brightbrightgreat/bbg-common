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
(function(){

	var BBGInfiniteVue = {};
	BBGInfiniteVue.install = function(Vue, options){

		/**
		 * Infinite Scroll
		 *
		 * Pull post info from subsequent pages automatically.
		 *
		 * @return void Nothing.
		 */
		Vue.prototype.infiniteLock = false;
		Vue.prototype.infiniteDone = false;
		Vue.prototype.infiniteScroll = function(){
			// Don't need to run.
			if((true === this.infiniteLock) || !this.archive) {
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
							var script = el.querySelector('#bbg-common-env').textContent,
								first = script.indexOf('bbgEnv={'),
								last = script.lastIndexOf('}'),
								i;

							// If we have positions, we probably have data.
							if ((first !== -1) && (last !== -1)) {
								var json = JSON.parse(script.substr(first + 7, last - first - 6));

								// Add whatever posts.
								if (
									json.posts &&
									Array.isArray(json.posts) &&
									json.posts.length
								) {
									for(i=0; i<json.posts.length; i++) {
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
						} catch(Ex) {
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
				function(r){
					vue.infiniteLock = false;
					vue.infiniteDone = true;
				}
			);
		};

	};

	if (typeof window !== 'undefined' && window.Vue) {
		window.Vue.use(BBGInfiniteVue);
	}
})();