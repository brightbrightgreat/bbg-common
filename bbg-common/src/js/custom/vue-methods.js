/**
 * Vue Methods
 *
 * This contains a collection of handy methods for Vue. It is loaded
 * automatically.
 *
 * TODO: these functions should be organized into blocks.
 */
(function () {

	var BBGMethodVue = {};
	BBGMethodVue.install = function (Vue, options) {

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
		 * @param mixed $src Source variable.
		 * @return mixed Copy.
		 */
		function _clone(src) {
			// Internal copy helper.
			function mixin(dest, source, copyFunc) {
				var name, s, i, empty = {};
				for (name in source) {
					s = source[name];
					if (!(name in dest) || (dest[name] !== s && (!(name in empty) || empty[name] !== s))) {
						dest[name] = copyFunc ? copyFunc(s) : s;
					}
				}
				return dest;
			}

			if (!src || typeof src != "object" || Object.prototype.toString.call(src) === "[object Function]") {
				// Covers null, undefined, any non-object, or function.
				return src;
			}

			if (src.nodeType && "cloneNode" in src) {
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

			var r, i, l;
			if (src instanceof Array) {
				// Array.
				r = [];
				for (i = 0, l = src.length; i < l; ++i) {
					if (i in src) {
						r.push(_clone(src[i]));
					}
				}
			}
			else{
				// Some other object type.
				r = src.constructor ? new src.constructor() : {};
			}
			return mixin(r, src, _clone);
		}

		// A Vue wrapper for the above.
		Vue.prototype.clone = function (src) {
			return _clone(src);
		};

		/**
		 * Get Variable Type
		 *
		 * Unlike typeof, this will distinguish between Objects and
		 * Arrays.
		 *
		 * @param mixed $value Variable.
		 * @return string Type.
		 */
		Vue.prototype.getType = function (value) {
			var type = typeof value;
			if (type === 'object' && Array.isArray(value))
				return 'array';
			return type;
		};

		/**
		 * Copy to Clipboard
		 *
		 * @param string $Text Text to copy.
		 * @return bool True/false.
		 */
		Vue.prototype.clipboard = function (text) {
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
		 * @param mixed $value Collection.
		 * @return int Count.
		 */
		Vue.prototype.count = function (value) {
			var type = this.getType(value);
			if (type === 'array') {
				return value.length;
			}
			else if (type === 'object') {
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
		 * @param mixed $value Value.
		 * @return string Hash.
		 */
		Vue.prototype.checksum = function (value) {
			// Stringify objects.
			if (typeof value === 'object') {
				value = JSON.stringify(value);
			}

			var hash = 0,
				strlen = value.length,
				i,
				c;

			if (!strlen) return hash;

			for (i=0; i<strlen; i++) {
				c = value.charCodeAt(i);
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
		 * @param mixed $collection Collection.
		 * @param callback $callback Callback function.
		 * @return void Nothing.
		 */
		Vue.prototype.forEach = function (collection, callback) {
			if (Object.prototype.toString.call(collection) === '[object Object]') {
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
		 * @param string $attr Attribute value.
		 * @return string Escaped value.
		 */
		Vue.prototype.escAttr = function (attr) {
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
		 * @param DOMElement $el Element.
		 * @return object Data.
		 */
		Vue.prototype.offset = function (el) {
			var out = {
				top: 0,
				left: 0,
				right: 0,
				bottom: 0,
				width: 0,
				height: 0
			};

			// If an object was passed (because e.g. $(.foo) was sent
			// instead of the real deal), strip it to the basics.
			while(typeof el === 'object' && el[0]) {
				el = el[0];
			}

			// Similarly, if an array was passed, we want just the
			// first element.
			while(Array.isArray(el) && el.length) {
				el = el[0];
			}

			try {
				var rect = el.getBoundingClientRect(),
					scrollY = window.scrollY || document.documentElement.scrollTop,
					scrollX = window.scrollX || document.documentElement.scrollLeft;

				out = {
					top: rect.top + scrollY,
					left: rect.left + scrollX,
					right: rect.right + scrollX,
					bottom: rect.bottom + scrollY,
					width: 0,
					height: 0
				};

				out.width = out.right - out.left;
				out.height = out.bottom - out.top;
			} catch(Ex) {}

			return out;
		};

		/**
		 * Is Element
		 *
		 * Make sure a thing is actually a DOMElement.
		 *
		 * @param mixed $el Element
		 * @return bool True/false.
		 */
		 Vue.prototype.isElement = function (el) {
			try {
				return el instanceof HTMLElement;
			}
			catch(ex) {
				return (
					(typeof el === "object") &&
					(el.nodeType === 1) &&
					(typeof el.style === "object") &&
					(typeof el.ownerDocument === "object")
				);
			}
		};

		/**
		 * Reverse Query Selector: Find Parent
		 *
		 * @param string $selector Selector.
		 * @param DOMElement $el Child.
		 */
		Vue.prototype.parent = function (selector, el) {
			if (!this.isElement(el)) {
				return false;
			}

			try {
				while(el.parentNode && 'matches' in el.parentNode) {
					el = el.parentNode;
					if (el.matches(selector)) {
						return el;
					}
				}
			} catch(Ex) { }

			return false;
		};

		/**
		 * Find the First Match
		 *
		 * @param string $selector Selector.
		 * @param DOMElement $el Container element.
		 */
		Vue.prototype.first = function (selector, el) {
			var results;

			if (this.isElement(el)) {
				results = el.querySelectorAll(selector);
			}
			else{
				results = document.querySelectorAll(selector);
			}

			if (typeof results === 'object' && this.count(results)) {
				return results[0];
			}

			return false;
		};

		/**
		 * Find the Last Match
		 *
		 * @param string $selector Selector.
		 * @param DOMElement $el Container element.
		 */
		Vue.prototype.last = function (selector, el) {
			var results;

			if (this.isElement(el)) {
				results = el.querySelectorAll(selector);
			}
			else{
				results = document.querySelectorAll(selector);
			}

			if (typeof results === 'object' && this.count(results)) {
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
		 * @param string $hash Hash.
		 * @return void Location.
		 */
		Vue.prototype.locationHash = function (hash) {
			if (typeof hash === 'string' && hash.length) {
				hash = hash.replace(/^\#?\/?/, '');
				if (hash.length)
					window.location.hash = '/' + hash;
				else
					window.location.hash = '';
			}
			else {
				window.location.hash = '';
				return;
			}

			return window.location.hash.replace(/^\#?\/?/, '');
		};

		/**
		 * Goto Error
		 *
		 * Smooth scroll to the first error on the page.
		 *
		 * @param string $hash Hash.
		 * @return void Location.
		 */
		Vue.prototype.gotoError = function () {
			var vue = this;
			Vue.nextTick(function () {
				var field,
					classes = ['error','error-message','is-invalid'],
					found = false;

				classes.forEach(function (v) {
					if (!found && false !== (field = vue.first('.' + v))) {
						found = true;
						smoothScroll.animateScroll(field);
					}
				});

				return found;
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
		 * @param string $message Message.
		 * @param string $type Type.
		 * @param int $timeout Timeout.
		 * @return int Count.
		 */
		Vue.prototype.setStatus = function (message, type, timeout) {
			// Clear the previous timeout, if any.
			if (this.status.timeout) {
				clearTimeout(this.status.timeout);
			}

			this.status.message = message;

			// The default type is "info".
			this.status.type = type;
			if (['error','info','success'].indexOf(type) === -1) {
				type = 'info';
			}

			timeout = parseInt(timeout, 10) || 0;
			if (timeout > 0) {
				var vue = this;

				// Clear the message after a certain amount of time has
				// elapsed.
				this.status.timeout = setTimeout(function () {
					vue.status.timeout = '';

					Vue.nextTick(function () {
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
		 * @param string $menu Menu.
		 * @return void Nothing.
		 */
		Vue.prototype.setMenu = function (menu) {
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
		 * @return void Nothing.
		 */
		Vue.prototype.closeMenu = function () {
			this.menu = '';
		};

		/**
		 * Slide In
		 *
		 * Uses blobslide to slide in an element.
		 *
		 * @return void Nothing.
		 */
		Vue.prototype.vSlideIn = function(el) {
			Vue.nextTick(function(){
				blobSlide.vslide(el, {
					duration: 500,
					transition: 'ease',
					force: 'show',
				});
			});
		};

		/**
		 * Slide Out
		 *
		 * Uses blobslide to slide out an element.
		 *
		 * @return void Nothing.
		 */
		Vue.prototype.vSlideOut = function(el) {
			Vue.nextTick(function(){
				blobSlide.vslide(el, {
					duration: 500,
					transition: 'ease',
					force: 'hide',
				});
			});
		};

		Vue.prototype.socialShare = function(url) {
			var
			windowFeatures,
			leftPosition,
			topPosition,
			width = 550,
			height = 450;

			leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
			topPosition = (window.screen.height / 2) - ((height / 2) + 50);

			windowFeatures = "status=no,height=" + height + ",width=" + width + ",resizable=no,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no";

			window.open(url, 'sharer', windowFeatures);
			return false;
		};
		// ------------------------------------------------------------- end misc

	};

	if (typeof window !== 'undefined' && window.Vue) {
		window.Vue.use(BBGMethodVue);
	}
})();
