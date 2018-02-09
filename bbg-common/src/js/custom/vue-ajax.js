(function(){

	var BlobAjaxVue = {};
	BlobAjaxVue.install = function(Vue, options){

		// -------------------------------------------------------------
		// Submission
		// -------------------------------------------------------------

		/**
		 * Pre-Submit
		 *
		 * Transition a form to a "loading" state.
		 *
		 * @param DOMElement $form Form.
		 * @return bool True/false.
		 */
		Vue.prototype.formPreSubmit = function(form){
			// This must be a form element.
			if(!form.nodeName || (form.nodeName !== 'FORM')){
				return false;
			}

			// Disable submit buttons.
			var submits = form.querySelectorAll('[type=submit]'),
				i;

			for(i=0; i<submits.length; i++){
				submits.item(i).setAttribute('disabled','disabled');
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
		 * @param DOMElement $form Form.
		 * @param object $formData Data.
		 * @param function $callback Callback function.
		 * @return bool True/false.
		 */
		Vue.prototype.formSubmit = function(form, formData, callback){
			// This must be a form element.
			if(!form.nodeName || (form.nodeName !== 'FORM')){
				return false;
			}

			var action = form.getAttribute('action') || this.session.ajaxurl,
				errorFields = form.querySelectorAll('.error'),
				formName = form.getAttribute('name') || '',
				i;

			// Recheck form for errors.
			if(true !== this.validateForm(formName)){
				this.gotoError();
				return false;
			}

			// Pre-submit.
			this.formPreSubmit(form);

			// And the main submit.
			var vue = this;
			return this.formAjax(action, formData, function(r){
				vue.formPostSubmit(form);
				if(typeof callback === 'function'){
					callback.call(vue, r);
				}
			});
		};

		/**
		 * Post-Submit
		 *
		 * Reset the form state so it can be submitted again.
		 *
		 * @param DOMElement $form Form.
		 * @return void Nothing.
		 */
		Vue.prototype.formPostSubmit = function(form){
			// This must be a form.
			if(!form.nodeName || (form.nodeName !== 'FORM')){
				return false;
			}

			// Re-enable submit buttons.
			var submits = form.querySelectorAll('[type=submit]'),
				i;

			for(i=0; i<submits.length; i++){
				submits.item(i).removeAttribute('disabled');
			}

			// Clear the loading class.
			form.classList.remove('is-loading');

			return true;
		};

		/**
		 * AJAX Handler
		 *
		 * @param string $action URL.
		 * @param object $data Data.
		 * @param function $callback Callback function.
		 * @param string $method Method (get or post).
		 * @return void Nothing.
		 */
		Vue.prototype.formAjax = function(action, data, callback, method){
			var ajax,
				vue = this;

			// Append session info to the request.
			data.n = this.session.n;

			// Format for submission.
			if(method && method === 'get'){
				action = action + '?' + param(data);
				ajax = this.$http.get(action, {emulateJSON: true});
			}
			else {
				ajax = this.$http.post(action, data, {emulateJSON: true});
			}

			// Submit!
			ajax.then(
				function(r){
					r = this.formResponse(r);
					if(typeof callback === 'function'){
						callback.call(vue, r);
					}
					return true;
				},
				function(r){
					r = this.formResponse(r);
					if(typeof callback === 'function'){
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
		 * @param mixed $response Response.
		 * @return object Response.
		 */
		Vue.prototype.formResponse = function(r){
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
				if((typeof r.body === 'object') && !Array.isArray(r.body)){
					// Main data.
					if(
						r.body.data &&
						(typeof r.body.data === 'object') &&
						!Array.isArray(r.body.data)
					) {
						out.data = r.body.data;
					}

					// Errors.
					if(
						r.body.errors &&
						(typeof r.body.errors === 'object') &&
						!Array.isArray(r.body.errors)
					){
						out.errors = r.body.errors;
					}

					// Message.
					if (r.body.message && (typeof r.body.message === 'string')) {
						out.message = r.body.message;
					}
				}
			} catch(ex) { out.data = {}; }

			// Add an error if the response is errory.
			if(
				(r.status >= 300 || !this.count(out.data)) &&
				!this.count(out.errors)
			) {
				out.status = 500;
				out.errors.other = 'The server garbled the last response. :(';
			}

			// Update the nonce?
			if((typeof r.body === 'object') && r.body.session && r.body.session.n) {
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
		 * @param mixed $vue Scope.
		 * @return bool True.
		 */
		Vue.prototype.heartbeat = function (vue) {
			if (!vue) vue = this;

			// A simple ping will do suffice.
			vue.formAjax(
				vue.session.ajaxurl,
				{action : 'bbg_common_ajax_heartbeat'},
				function (r) {
					setTimeout(function () {
						vue.heartbeat(vue);
					}, 1800000);
					return true;
				}
			);

			return true;
		};

		// ------------------------------------------------------------- end misc
	};

	if (typeof window !== 'undefined' && window.Vue) {
		window.Vue.use(BlobAjaxVue);
	}
})();