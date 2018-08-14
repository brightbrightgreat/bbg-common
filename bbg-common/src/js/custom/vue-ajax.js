/**
 * AJAX
 */
/* global param */
/* global Cookies */
(function() {

	var BlobAjaxVue = {
		/**
		 * Install
		 *
		 * @param {Vue} Vue Vue.
		 * @returns {void} Nothing.
		 */
		install: function(Vue) {

			// -------------------------------------------------------------
			// Submission
			// -------------------------------------------------------------

			/**
			 * Pre-Submit
			 *
			 * Transition a form to a "loading" state.
			 *
			 * @param {DOMElement} form Form.
			 * @returns {bool} True/false.
			 */
			Vue.prototype.formPreSubmit = function(form) {
				// This must be a form element.
				if (!form.nodeName || ('FORM' !== form.nodeName)) {
					return false;
				}

				// Disable submit buttons.
				var submits = form.querySelectorAll('[type=submit]');

				for (var i = 0; i < submits.length; ++i) {
					submits.item(i).setAttribute('disabled', 'disabled');
				}

				// Mark the form as loading.
				form.classList.add('is-loading');
				return true;
			};

			/**
			 * Submit Form
			 *
			 * This wraps pre- and post-submit tasks.
			 *
			 * @param {DOMElement} form Form.
			 * @param {object} formData Data.
			 * @param {function} callback Callback function.
			 * @returns {bool} True/false.
			 */
			Vue.prototype.formSubmit = function(form, formData, callback) {
				// This must be a form element.
				if (!form.nodeName || ('FORM' !== form.nodeName)) {
					return false;
				}

				var action = form.getAttribute('action') || this.session.ajaxurl;
				var formName = form.getAttribute('name') || '';

				// Recheck form for errors.
				if (true !== this.validateForm(formName)) {
					this.gotoError();
					return false;
				}

				// Pre-submit.
				this.formPreSubmit(form);

				// And the main submit.
				var vue = this;
				return this.formAjax(action, formData, function(r) {
					vue.formPostSubmit(form);
					if ('function' === typeof callback) {
						callback.call(vue, r);
					}
				});
			};

			/**
			 * Post-Submit
			 *
			 * Reset the form state so it can be submitted again.
			 *
			 * @param {DOMElement} form Form.
			 * @returns {void} Nothing.
			 */
			Vue.prototype.formPostSubmit = function(form) {
				// This must be a form.
				if (!form.nodeName || ('FORM' !== form.nodeName)) {
					return false;
				}

				// Re-enable submit buttons.
				var submits = form.querySelectorAll('[type=submit]');

				for (var i = 0; i < submits.length; ++i) {
					submits.item(i).removeAttribute('disabled');
				}

				// Clear the loading class.
				form.classList.remove('is-loading');

				return true;
			};

			/**
			 * AJAX Handler
			 *
			 * @param {string} action URL.
			 * @param {object} data Data.
			 * @param {function} callback Callback function.
			 * @param {string} method Method (get or post).
			 * @returns {void} Nothing.
			 */
			Vue.prototype.formAjax = function(action, data, callback, method) {
				var ajax;
				var vue = this;

				// Append session info to the request.
				data.n = this.session.n;

				// Format for submission.
				if (method && 'get' === method) {
					action = action + '?' + param(data);
					ajax = this.$http.get(action, {emulateJSON: true});
				}
				else {
					ajax = this.$http.post(action, data, {emulateJSON: true});
				}

				// Submit!
				ajax.then(
					function(r) {
						r = this.formResponse(r);
						if ('function' === typeof callback) {
							callback.call(vue, r);
						}
						return true;
					},
					function(r) {
						r = this.formResponse(r);
						if ('function' === typeof callback) {
							callback.call(vue, r);
						}
						return true;
					}
				);
			};

			// ------------------------------------------------------------- end submission


			// -------------------------------------------------------------
			// Response Handling
			// -------------------------------------------------------------

			/**
			 * Format Response
			 *
			 * The response should be a JSON object matching a certain
			 * structure, but various errors might get in the way. This
			 * ensures that the form callbacks always get a predictable
			 * response.
			 *
			 * @param {mixed} r Response.
			 * @returns {object} Response.
			 */
			Vue.prototype.formResponse = function(r) {
				// It should look like this.
				var out = {
					ok: false,
					status: r.status,
					headers: r.headers,
					data: {},
					errors: {},
					message: '',
				};

				// Parse the data.
				try {
					if (('object' === typeof r.body) && !Array.isArray(r.body)) {
						// Main data.
						if (
							r.body.data &&
							('object' === typeof r.body.data) &&
							!Array.isArray(r.body.data)
						) {
							out.data = r.body.data;
						}

						// Errors.
						if (
							r.body.errors &&
							('object' === typeof r.body.errors) &&
							!Array.isArray(r.body.errors)
						) {
							out.errors = r.body.errors;
						}

						// Message.
						if (r.body.message && ('string' === typeof r.body.message)) {
							out.message = r.body.message;
						}
					}
				} catch (ex) { out.data = {}; }

				// Add an error if the response is errory.
				if (
					(300 <= r.status || !this.count(out.data)) &&
					!this.count(out.errors)
				) {
					out.status = 500;
					out.errors.other = 'The server garbled the last response. :(';
				}

				// Update the nonce?
				if (('object' === typeof r.body) && r.body.session && r.body.session.n) {
					this.session.n = r.body.session.n;
				}

				// The overall status.
				out.ok = !this.count(out.errors);

				return out;
			};

			// ------------------------------------------------------------- end response



			// -------------------------------------------------------------
			// Misc
			// -------------------------------------------------------------

			/**
			 * Update Nonce
			 *
			 * This requests a Nonce via an AJAX call so that the main page
			 * can remain static-friendly.
			 *
			 * @param {mixed} vue Scope.
			 * @returns {bool} True.
			 */
			Vue.prototype.heartbeat = function(vue) {
				if (!vue) vue = this;

				// A simple ping will do suffice.
				vue.formAjax(
					vue.session.ajaxurl,
					{action : 'bbg_common_ajax_heartbeat'},
					function() {
						Cookies.set('bbg_common_n_checked', 1);
						setTimeout(function() {
							vue.heartbeat(vue);
						}, 600000);
						return true;
					}
				);

				return true;
			};

			// ------------------------------------------------------------- end misc
		},
	};

	if ('undefined' !== typeof window && window.Vue) {
		window.Vue.use(BlobAjaxVue);
	}
})();
