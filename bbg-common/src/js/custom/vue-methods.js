/**
 * Vue Methods
 *
 * This contains a collection of handy methods for Vue. It is loaded
 * automatically.
 *
 * TODO: these functions should be organized into blocks.
 */
/* global smoothScroll */
/* global blobSlide */
(function() {

	var BBGMethodVue = {
		/**
		 * Install
		 *
		 * @param {Vue} Vue Vue.
		 * @returns {void} Nothing.
		 */
		install: function(Vue) {

			// -------------------------------------------------------------
			// Tools
			// -------------------------------------------------------------

			/**
			 * Deep Clone
			 *
			 * Javascript objects are always passed by reference. This is a
			 * simple clone method that can allow code to work with a copy
			 * instead.
			 *
			 * @see {https://davidwalsh.name/javascript-clone}
			 *
			 * @param {mixed} src Source variable.
			 * @returns {mixed} Copy.
			 */
			function _clone(src) {
				/**
				 * Copy Helper
				 *
				 * @param {mixed} dest Destination.
				 * @param {mixed} source Source.
				 * @param {mixed} copyFunc Copy function.
				 * @returns {mixed} Clone.
				 */
				function mixin(dest, source, copyFunc) {
					var name;
					var s;
					var empty = {};
					for (name in source) {
						s = source[name];
						if (!(name in dest) || (dest[name] !== s && (!(name in empty) || empty[name] !== s))) {
							dest[name] = copyFunc ? copyFunc(s) : s;
						}
					}
					return dest;
				}

				if (!src || 'object' !== typeof src || '[object Function]' === Object.prototype.toString.call(src)) {
					// Covers null, undefined, any non-object, or function.
					return src;
				}

				if (src.nodeType && 'cloneNode' in src) {
					// DOM Node.
					return src.cloneNode(true); // Node
				}

				if (src instanceof Date) {
					// Date.
					return new Date(src.getTime());	// Date
				}

				if (src instanceof RegExp) {
					// RegExp.
					return new RegExp(src);   // RegExp
				}

				var r;
				var i;
				var l;
				if (src instanceof Array) {
					// Array.
					r = [];
					for (i = 0, l = src.length; i < l; ++i) {
						if (i in src) {
							r.push(_clone(src[i]));
						}
					}
				}
				else {
					// Some other object type.
					r = src.constructor ? new src.constructor() : {};
				}
				return mixin(r, src, _clone);
			}

			// A Vue wrapper for the above.
			Vue.prototype.clone = function(src) {
				return _clone(src);
			};

			/**
			 * Get Variable Type
			 *
			 * Unlike typeof, this will distinguish between Objects and
			 * Arrays.
			 *
			 * @param {mixed} value Variable.
			 * @returns {string} Type.
			 */
			Vue.prototype.getType = function(value) {
				var type = typeof value;
				if ('object' === type && Array.isArray(value))
				{return 'array';}
				return type;
			};

			/**
			 * Copy to Clipboard
			 *
			 * @param {string} text Text to copy.
			 * @returns {bool} True/false.
			 */
			Vue.prototype.clipboard = function(text) {
				var foo = document.createElement('textarea');
				foo.value = text;

				// TODO update the class. This should hide the element
				// without preventing it from being interacted with.
				foo.classList.add('hide-safe');

				document.body.appendChild(foo);
				foo.select();
				document.execCommand('copy');
				document.body.removeChild(foo);

				return true;
			};

			/**
			 * Count
			 *
			 * Find the number of values in an Array or an Object.
			 *
			 * @param {mixed} value Collection.
			 * @returns {int} Count.
			 */
			Vue.prototype.count = function(value) {
				var type = this.getType(value);
				if ('array' === type) {
					return value.length;
				}
				else if ('object' === type) {
					return Object.keys(value).length;
				}

				value = this.setType(value, 'string');
				return value.length;
			};

			/**
			 * Simple Checksum
			 *
			 * This will generate a simple hash key to aid with change
			 * tracking, etc.
			 *
			 * @param {mixed} value Value.
			 * @returns {string} Hash.
			 */
			Vue.prototype.checksum = function(value) {
				// Stringify objects.
				if ('object' === typeof value) {
					value = JSON.stringify(value);
				}

				var hash = 0;
				var strlen = value.length;

				if (!strlen) return hash;

				for (var i = 0; i < strlen; ++i) {
					var c = value.charCodeAt(i);
					hash = ((hash << 5) - hash) + c;
					hash = hash & hash; // Convert to 32-bit integer.
				}

				return hash;
			};

			/**
			 * Looper
			 *
			 * Loop through an Array or Object.
			 *
			 * @param {mixed} collection Collection.
			 * @param {callback} callback Callback function.
			 * @returns {void} Nothing.
			 */
			Vue.prototype.forEach = function(collection, callback) {
				if ('[object Object]' === Object.prototype.toString.call(collection)) {
					for (var key in collection) {
						if (Object.prototype.hasOwnProperty.call(collection, key)) {
							callback(collection[key], key, collection);
						}
					}
				}
				else {
					for (var akey = 0, len = collection.length; akey < len; akey++) {
						callback(collection[akey], akey, collection);
					}
				}
			};

			// ------------------------------------------------------------- end tools



			// -------------------------------------------------------------
			// Formatting
			// -------------------------------------------------------------

			/**
			 * Escape Attribute
			 *
			 * If for some reason an arbitrary string needs to be shoved
			 * into an HTML context, this function can be used to escape
			 * it in a similar way to the WP esc_attr() function.
			 *
			 * @param {string} attr Attribute value.
			 * @returns {string} Escaped value.
			 */
			Vue.prototype.escAttr = function(attr) {
				return ('' + attr)			// Force string.
					.replace(/&/g, '&amp;')		// & must be first.
					.replace(/'/g, '&apos;')	// Other entities.
					.replace(/"/g, '&quot;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;');
			};

			// ------------------------------------------------------------- end formatting



			// -------------------------------------------------------------
			// DOM Helpers
			// -------------------------------------------------------------

			/**
			 * Element Offsets
			 *
			 * Find an element's position offsets and dimensions, similar
			 * to how jQuery manages it.
			 *
			 * @param {DOMElement} el Element.
			 * @returns {object} Data.
			 */
			Vue.prototype.offset = function(el) {
				var out = {
					top: 0,
					left: 0,
					right: 0,
					bottom: 0,
					width: 0,
					height: 0,
				};

				// If an object was passed (because e.g. $(.foo) was sent
				// instead of the real deal), strip it to the basics.
				while ('object' === typeof el && el[0]) {
					el = el[0];
				}

				// Similarly, if an array was passed, we want just the
				// first element.
				while (Array.isArray(el) && el.length) {
					el = el[0];
				}

				try {
					var rect = el.getBoundingClientRect();
					var scrollY = window.scrollY || document.documentElement.scrollTop;
					var scrollX = window.scrollX || document.documentElement.scrollLeft;

					out = {
						top: rect.top + scrollY,
						left: rect.left + scrollX,
						right: rect.right + scrollX,
						bottom: rect.bottom + scrollY,
						width: 0,
						height: 0,
					};

					out.width = out.right - out.left;
					out.height = out.bottom - out.top;
				} catch (Ex) {
					return out;
				}

				return out;
			};

			/**
			 * Is Element
			 *
			 * Make sure a thing is actually a DOMElement.
			 *
			 * @param {mixed} el Element
			 * @returns {bool} True/false.
			 */
			Vue.prototype.isElement = function(el) {
				try {
					return el instanceof HTMLElement;
				}
				catch (ex) {
					return (
						('object' === typeof el) &&
						(1 === el.nodeType) &&
						('object' === typeof el.style) &&
						('object' === typeof el.ownerDocument)
					);
				}
			};

			/**
			 * Reverse Query Selector: Find Parent
			 *
			 * @param {string} selector Selector.
			 * @param {DOMElement} el Child.
			 * @returns {mixed} Element or false.
			 */
			Vue.prototype.parent = function(selector, el) {
				if (!this.isElement(el)) {
					return false;
				}

				try {
					while (el.parentNode && 'matches' in el.parentNode) {
						el = el.parentNode;
						if (el.matches(selector)) {
							return el;
						}
					}
				} catch (Ex) {
					return false;
				}

				return false;
			};

			/**
			 * Find the First Match
			 *
			 * @param {string} selector Selector.
			 * @param {DOMElement} el Container element.
			 * @returns {mixed} Element or false.
			 */
			Vue.prototype.first = function(selector, el) {
				var results;

				if (this.isElement(el)) {
					results = el.querySelectorAll(selector);
				}
				else {
					results = document.querySelectorAll(selector);
				}

				if ('object' === typeof results && this.count(results)) {
					return results[0];
				}

				return false;
			};

			/**
			 * Find the Last Match
			 *
			 * @param {string} selector Selector.
			 * @param {DOMElement} el Container element.
			 * @returns {mixed} Element or false.
			 */
			Vue.prototype.last = function(selector, el) {
				var results;

				if (this.isElement(el)) {
					results = el.querySelectorAll(selector);
				}
				else {
					results = document.querySelectorAll(selector);
				}

				if ('object' === typeof results && this.count(results)) {
					return results[this.count(results) - 1];
				}

				return false;
			};

			// ------------------------------------------------------------- end DOM



			// -------------------------------------------------------------
			// Navigation
			// -------------------------------------------------------------

			/**
			 * Location Hash
			 *
			 * Set a location-style hash like /#/foobar.
			 *
			 * @param {string} hash Hash.
			 * @returns {void} Location.
			 */
			Vue.prototype.locationHash = function(hash) {
				if ('string' === typeof hash && hash.length) {
					hash = hash.replace(/^#?\/?/, '');
					if (hash.length)
					{window.location.hash = '/' + hash;}
					else
					{window.location.hash = '';}
				}
				else {
					window.location.hash = '';
					return;
				}

				return window.location.hash.replace(/^#?\/?/, '');
			};

			/**
			 * Goto Error
			 *
			 * Smooth scroll to the first error on the page.
			 *
			 * @returns {void} Location.
			 */
			Vue.prototype.gotoError = function() {
				var vue = this;
				Vue.nextTick(function() {
					var classes = ['error', 'error-message', 'is-invalid'];
					var offset = 170;
					var main = document.querySelector('main');

					// If the page has a <main> element, use it to find an
					// appropriate offset (for working around e.g. sticky
					// headers).
					if (main) {
						var style = window.getComputedStyle(main);
						offset = parseInt(style.getPropertyValue('margin-top')) || 0;
						offset += 30;
					}

					// Check for matches in order and scroll to the first we
					// find!
					for (var i = 0; i < classes.length; ++i) {
						var field = vue.first('.' + classes[i]);
						if (field) {
							smoothScroll.animateScroll(field.parentNode, null, {offset: offset});
							return true;
						}
					}

					return false;
				});
			};

			// ------------------------------------------------------------- end nav



			// -------------------------------------------------------------
			// Misc
			// -------------------------------------------------------------

			/**
			 * Set Status
			 *
			 * Set a system status. The type should be one of:
			 * info: A neutral message.
			 * success: Something positive.
			 * error: An error.
			 *
			 * @param {string} message Message.
			 * @param {string} type Type.
			 * @param {int} timeout Timeout.
			 * @returns {int} Count.
			 */
			Vue.prototype.setStatus = function(message, type, timeout) {
				// Clear the previous timeout, if any.
				if (this.status.timeout) {
					clearTimeout(this.status.timeout);
				}

				this.status.message = message;

				// The default type is "info".
				this.status.type = type;
				if (-1 === ['error', 'info', 'success'].indexOf(type)) {
					type = 'info';
				}

				timeout = parseInt(timeout, 10) || 0;
				if (0 < timeout) {
					var vue = this;

					// Clear the message after a certain amount of time has
					// elapsed.
					this.status.timeout = setTimeout(function() {
						vue.status.timeout = '';

						Vue.nextTick(function() {
							vue.status.message = '';
							vue.status.type = '';
						});
					}, timeout);
				}
			};

			/**
			 * Set a Menu
			 *
			 * This sets (or unsets) a menu. What happens after that is up
			 * to the code on the page.
			 *
			 * @param {string} menu Menu.
			 * @returns {void} Nothing.
			 */
			Vue.prototype.setMenu = function(menu) {
				// If this is already the active menu, close it.
				if (!menu || menu === this.menu) {
					this.closeMenu();
				}
				// Otherwise make it the menu!
				else {
					this.menu = menu;
				}
			};

			/**
			 * Unset Menu
			 *
			 * This sets unsets the selected menu, if any.
			 *
			 * @returns {void} Nothing.
			 */
			Vue.prototype.closeMenu = function() {
				this.menu = '';
			};

			/**
			 * Slide In
			 *
			 * Uses blobslide to slide in an element.
			 *
			 * @param {DOMElement} el Element.
			 * @returns {void} Nothing.
			 */
			Vue.prototype.vSlideIn = function(el) {
				var duration = parseInt(el.getAttribute('data-blobslide-duration'), 10) || 500;
				var transition = el.getAttribute('data-blobslide-transition') || 'ease';
				var type = el.getAttribute('data-blobslide-type') || 'block';

				Vue.nextTick(function() {
					blobSlide.vslide(el, {
						duration: duration,
						transition: transition,
						display: type,
						force: 'show',
					});
				});
			};

			/**
			 * Slide Out
			 *
			 * Uses blobslide to slide out an element.
			 *
			 * @param {DOMElement} el Element.
			 * @returns {void} Nothing.
			 */
			Vue.prototype.vSlideOut = function(el) {
				var duration = parseInt(el.getAttribute('data-blobslide-duration'), 10) || 500;
				var transition = el.getAttribute('data-blobslide-transition') || 'ease';
				var type = el.getAttribute('data-blobslide-type') || 'block';

				Vue.nextTick(function() {
					blobSlide.vslide(el, {
						duration: duration,
						transition: transition,
						display: type,
						force: 'hide',
					});
				});
			};

			Vue.prototype.socialShare = function(url) {
				var width = 550;
				var height = 450;

				var leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
				var topPosition = (window.screen.height / 2) - ((height / 2) + 50);

				var windowFeatures = 'status=no,height=' + height + ',width=' + width + ',resizable=no,left=' + leftPosition + ',top=' + topPosition + ',screenX=' + leftPosition + ',screenY=' + topPosition + ',toolbar=no,menubar=no,scrollbars=no,location=no,directories=no';

				window.open(url, 'sharer', windowFeatures);
				return false;
			};
			// ------------------------------------------------------------- end misc

		},
	};

	if ('undefined' !== typeof window && window.Vue) {
		window.Vue.use(BBGMethodVue);
	}
})();
