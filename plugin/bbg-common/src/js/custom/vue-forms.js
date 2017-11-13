/**
 * Form Handling
 *
 * This module provides consistent AJAX form wrappers. It is compiled
 * into the main Vue library automatically.
 */
(function () {

	var BBGFormsVue = {};
	BBGFormsVue.install = function (Vue, options) {

		// -------------------------------------------------------------
		// Setup
		// -------------------------------------------------------------

		// Better email regex.
		var emailRegex = new RegExp(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);

		/**
		 * Directive: v-form
		 *
		 * Any form with a v-form attribute will get all the goodness
		 * within this module.
		 *
		 * Such forms must have a name attribute and a matching entry in
		 * this.forms[].
		 */
		Vue.directive('form', {
			id: 'form',
			priority: 10001,
			inserted: function (el, binding, vnode) {
				// This must be a form.
				if (!isForm(el)) {
					return;
				}

				var name,
					data;

				try {
					name = el.getAttribute('name') || false;
					data = vnode.context.forms[name];
				} catch(ex) { return; }

				// Add some attributes
				data.$valid = false;
				data.$touched = false;
				data.$changed = false;
				data.$errors = {};
				data.$fields = {};
				data.$_lock = false;
				Vue.set(vnode.context.forms, name, data);

				// Don't want to validate the form the usual way.
				el.setAttribute('novalidate', true);

				// Bind an input listener to monitor changes.
				el.addEventListener('input', function (e) {
					if (isField(e.target) && !e.target.getAttribute('multiple')) {
						validateField(vnode.context, el, e.target, true);
					}
				});

				// Validate on the next tick.
				Vue.nextTick(function () {
					validateForm(vnode.context, el);
				});
			}
		});

		// ------------------------------------------------------------- end setup



		// -------------------------------------------------------------
		// Field Helpers
		// -------------------------------------------------------------

		/**
		 * Is Field?
		 *
		 * Check if a DOM element is a form field.
		 *
		 * @param DOMElement $el Element.
		 * @return bool True/false.
		 */
		function isField(el) {
			return !!(el.nodeName && (el.nodeName === "INPUT" || el.nodeName === "TEXTAREA" || el.nodeName === "SELECT"));
		}

		// A Vue method for the above.
		Vue.prototype.isField = function (el) {
			return isField(el);
		};

		/**
		 * Check Field Errors
		 *
		 * Return true if the field is A-OK, otherwise the corresponding
		 * error message.
		 *
		 * @param string $formName Form name.
		 * @param string $fieldName Field name.
		 * @return bool|string True or error.
		 */
		Vue.prototype.fieldOk = function (formName, fieldName) {
			try {
				var error = this.forms[formName].$errors[fieldName];
				if (typeof error === 'undefined' || !error) {
					return true;
				}

				return error;
			} catch(Ex) { return true; }
		};

		// ------------------------------------------------------------- end fields



		// -------------------------------------------------------------
		// Form Helpers
		// -------------------------------------------------------------

		/**
		 * Is Form?
		 *
		 * Check if a DOM element is a form.
		 *
		 * @param DOMElement $el Element.
		 * @return bool True/false.
		 */
		function isForm(el) {
			return !!(el.nodeName && el.nodeName === 'FORM');
		}

		// A Vue method for the above.
		Vue.prototype.isForm = function (el) {
			return isForm(el);
		};

		/**
		 * Get Form Fields
		 *
		 * Return an array of DOMElements for each field within a form.
		 *
		 * @param DOMElement $el Form.
		 * @return array Fields.
		 */
		function formFields(form) {
			var fields = form.querySelectorAll('input, select, textarea'),
				out = [],
				i;

			for (i=0; i<fields.length; i++) {
				var name = fields[i].getAttribute('name') || false;
				if (name) {
					out.push(fields[i]);
				}
			}

			return out;
		}

		// A Vue method for the above.
		Vue.prototype.formFields = function (form) {
			return formFields(form);
		};

		// ------------------------------------------------------------- end forms



		// -------------------------------------------------------------
		// Validation
		// -------------------------------------------------------------

		/**
		 * Validate a Form
		 *
		 * Check if all fields within a form validate and update values
		 * accordingly.
		 *
		 * @param Vue $scope Vue scope.
		 * @param string $form Form name.
		 * @param bool $forceTouch Trigger field touches as we go.
		 * @return bool True/false.
		 */
		function validateForm($scope, form, forceTouch) {
			var name = form.getAttribute('name'),
				fields = formFields(form),
				fieldsOld = Object.keys($scope.forms[name].$fields),
				fieldsNew = [],
				field,
				fieldName,
				i,
				data = $scope.forms[name];

			forceTouch = !!forceTouch;

			// Add new fields (if any).
			for (i=0; i<fields.length; i++) {
				field = fields[i];
				fieldName = fields[i].getAttribute('name');
				fieldsNew.push(fieldName);
				if (fieldsOld.indexOf(fieldName) === -1) {
					data.$fields[fieldName] = {
						$valid: false,
						$touched: false,
						$changed: false,
						$el: field,
						$_lock: false
					};

					/* jshint ignore:start */
					field.addEventListener('blur', function (e) {
						validateField($scope, form, e.target, true);
						e.target.removeEventListener(e.type, arguments.callee);
					});
					/* jshint ignore:end */
				}
			}

			// Remove missing ones.
			for (i=0; i<fieldsOld.length; i++) {
				if (fieldsNew.indexOf(fieldsOld[i]) === -1) {
					delete(data.$fields[fieldsOld[i]]);
					if (data.$errors[fieldsOld[i]]) {
						delete data.$errors[fieldsOld[i]];
					}
				}
			}

			// Clear old errors.
			data.$errors = {};
			Vue.set($scope.forms, name, data);

			// And finally do the checking!
			$scope.forms[name].$_lock = true;
			for (i=0; i<fields.length; i++) {
				field = fields[i];
				validateField($scope, form, field, forceTouch);
			}
			$scope.forms[name].$_lock = false;

			// Update classes and return the result.
			updateFormClasses($scope, form);
			return $scope.forms[name].$valid;
		}

		// A Vue method for the above.
		Vue.prototype.validateForm = function (form, forceTouch) {
			return validateForm(this, form, forceTouch);
		};

		/**
		 * Clear Errors
		 *
		 * Remove form field errors.
		 *
		 * @param DOMElement $el Element.
		 * @return bool True/false.
		 */
		function clearInvalid(field) {
			try {
				field.checkValidity();
				field.setCustomValidity('');
				return true;
			}
			catch(ex) { return false; }
		}

		/**
		 * Validate a Field
		 *
		 * Check if a form field validates and update everything
		 * accordingly.
		 *
		 * @param Vue $scope Vue scope.
		 * @param string $form Form name.
		 * @param DOMElement $field Element.
		 * @param bool $forceTouch Trigger a touch.
		 * @return bool True/false.
		 */
		function validateField($scope, form, field, forceTouch) {
			var name = form.getAttribute('name'),
				fieldName = field.getAttribute('name'),
				fieldValue = field.value || field.getAttribute('value') || field.textContent || '',
				validity = false,
				data = $scope.forms[name].$fields[fieldName];

			if (typeof data === 'undefined') {
				data = $scope.forms[name].$fields[fieldName] = {
					$valid: false,
					$touched: false,
					$changed: false,
					$el: field,
					$_lock: false
				};
				Vue.set($scope.forms[name].$fields, fieldName, data);
			}

			// Abort if a lock is already set.
			if (data.$_lock) {
				return data.$valid;
			}

			Vue.set($scope.forms[name].$fields[fieldName], '$_lock', true);

			// Mark it touched.
			if (!!forceTouch) {
				data.$touched = true;
			}

			// Store the original value to help detect changes.
			if (!field.hasAttribute('data-original')) {
				field.setAttribute('data-original', fieldValue);
				data.$changed = false;
			}
			else {
				data.$changed = (fieldValue !== field.getAttribute('data-original'));
			}

			// Abort if we can't check validity.
			data.$valid = false;
			clearInvalid(field);
			if (typeof field.willValidate === "undefined" || field.checkValidity()) {
				data.$valid = true;
			}
			// Could be an empty/required field.
			else if (!data.$touched) {
				validity = field.validity;
				if (validity.valueMissing && !data.touched) {
					data.$valid = true;
					clearInvalid(field);
				}
			}

			// Email-specific validation.
			if (fieldValue.length && data.$valid && field.getAttribute('type') === 'email') {
				data.$valid = !!emailRegex.test(fieldValue);
				if (!data.$valid) {
					field.setCustomValidity('Make sure to enter an entire email address.');
				}
			}

			// Any extra checks?
			if (data.$valid) {
				// A custom callback, can be specified by including a
				// validation-callback attribute on the element.
				var fieldCallback = field.getAttribute('validation-callback') || false;
				if (fieldCallback && typeof $scope[fieldCallback] === 'function') {
					var callbackResponse = $scope[fieldCallback](fieldValue);
					if (callbackResponse !== true) {
						data.$valid = false;
						if (typeof callbackResponse === 'string' && callbackResponse.length) {
							field.setCustomValidity(callbackResponse);
						}
						else {
							field.setCustomValidity('This input is not valid.');
						}
					}
				}
			}

			// Clear the old error message, if any.
			if (data.$valid) {
				clearInvalid(field);

				if ($scope.forms[name].$errors[fieldName]) {
					Vue.delete($scope.forms[name].$errors, fieldName);
				}
			}
			// Add the error message.
			else {
				Vue.set($scope.forms[name].$errors, fieldName, field.validationMessage);
			}

			// Set the field classes.
			field.classList.toggle('is-valid', data.$valid);
			field.classList.toggle('is-invalid', !data.$valid);
			field.classList.toggle('is-touched', data.$touched);
			field.classList.toggle('is-changed', data.$changed);

			// Remove the lock.
			data.$_lock = false;

			Vue.set($scope.forms[name].$fields, fieldName, data);

			updateFormClasses($scope, form);
			return $scope.forms[name].$fields[fieldName].$valid;
		}

		// A Vue method for the above.
		Vue.prototype.validateField = function (form, field, forceTouch) {
			return validateField(this, form, field, forceTouch);
		};

		/**
		 * Update Form Classes
		 *
		 * Form and field statuses have corresponding DOM classes to
		 * make it easier for styling and external manipulation.
		 *
		 * @param Vue $scope Vue scope.
		 * @param DOMElement $form Form element.
		 * @return bool True/false.
		 */
		function updateFormClasses($scope, form) {
			var name = form.getAttribute('name');

			// If we're updating en masse we don't need to run this a
			// million times.
			if ($scope.forms[name].$_lock) {
				return;
			}

			var valid=true,
				changed=false,
				touched=false;

			// The form is the fields.
			$scope.forEach($scope.forms[name].$fields, function (f) {
				if (!f.$valid) {
					valid = false;
				}
				if (f.$changed) {
					changed = true;
				}
				if (f.$touched) {
					touched = true;
				}
			});

			// Update the internal variables.
			Vue.set($scope.forms[name], '$valid', valid);
			Vue.set($scope.forms[name], '$touched', touched);
			Vue.set($scope.forms[name], '$changed', changed);

			// And update the classes.
			form.classList.toggle('is-valid', $scope.forms[name].$valid);
			form.classList.toggle('is-invalid', !$scope.forms[name].$valid);
			form.classList.toggle('is-touched', $scope.forms[name].$touched);
			form.classList.toggle('is-changed', $scope.forms[name].$changed);

			$scope.$forceUpdate();
		}

		// ------------------------------------------------------------- validation



		// -------------------------------------------------------------
		// Submission
		// -------------------------------------------------------------

		/**
		 * Form: Pre-Submit
		 *
		 * This disables submit buttons and adds an is-loading class
		 * to the form while it is processing.
		 *
		 * @param DOMElement $form Form element.
		 * @return bool True/false.
		 */
		Vue.prototype.formPreSubmit = function (form) {
			if (!this.isForm(form)) {
				return false;
			}

			var submits = form.querySelectorAll('[type=submit]'),
				i;

			for (i=0; i<submits.length; i++) {
				submits[i].setAttribute('disabled','disabled');
			}

			form.classList.add('is-loading');
			return true;
		};

		/**
		 * Form: Submit
		 *
		 * Validate a form, submit it, and send the response to the
		 * specified callback.
		 *
		 * @param DOMElement $form Form element.
		 * @param object $formData Form data.
		 * @param callback $callback Form response callback.
		 * @return bool True/false.
		 */
		Vue.prototype.formSubmit = function (form, formData, callback) {
			if (!this.isForm(form)) {
				return false;
			}

			var action = form.getAttribute('action') || this.session.ajaxurl,
				errorFields = form.querySelectorAll('.error-message'),
				i;

			// Clear prior errors and recheck.
			if (!this.validateForm(form, true)) {
				return false;
			}

			this.formPreSubmit(form);
			formData.loading = true;

			// Submit the form.
			var vue = this;
			return this.formAjax(action, formData, function (r) {
				vue.formPostSubmit(form);
				formData.loading = false;

				// Send the response to the callback, if applicable.
				if (typeof callback === 'function') {
					callback(r);
				}
			});
		};

		/**
		 * Form: AJAX submit
		 *
		 * This function only handles the form submission and response
		 * hand-off. If the pre/post helpers are not applicable, this
		 * method should be called directly.
		 *
		 * @param string $action URL.
		 * @param object $data Data.
		 * @param callback $callback Response handler.
		 * @param string $method Get or Post.
		 * @return bool True/false.
		 */
		Vue.prototype.formAjax = function (action, data, callback, method) {
			var ajax;

			// Append session info to the request.
			data.n = this.session.n;

			// We don't need to submit the $fields noise to the server.
			if (typeof data.$fields !== 'undefined') {
				data = this.clone(data);
				delete(data.$fields);
			}

			// Format for submission.
			if (method && method === 'get') {
				action = action + '?' + param(data);
				ajax = this.$http.get(action, {emulateJSON: true});
			}
			else {
				ajax = this.$http.post(action, data, {emulateJSON: true});
			}

			// Submit!
			ajax.then(
				function (r) {
					r = this.formResponse(r);
					if (typeof callback === 'function') {
						callback(r);
					}
					return true;
				},
				function (r) {
					r = this.formResponse(r);
					if (typeof callback === 'function') {
						callback(r);
					}
					return true;
				}
			);
		};

		/**
		 * Form: Post-Submit
		 *
		 * This undoes the changes made by pre-submit.
		 *
		 * @param DOMElement $form Form element.
		 * @return bool True/false.
		 */
		Vue.prototype.formPostSubmit = function (form) {
			if (!this.isForm(form)) {
				return false;
			}

			var submits = form.querySelectorAll('[type=submit]'),
				i;

			for (i=0; i<submits.length; i++) {
				submits[i].removeAttribute('disabled');
			}

			form.classList.remove('is-loading');
			return true;
		};

		/**
		 * Format Response
		 *
		 * This ensures that error or success, we have a response object
		 * with a predictable structure.
		 *
		 * @param object $r Raw response.
		 * @return bool True/false.
		 */
		Vue.prototype.formResponse = function (r) {
			var out = {
				ok: false,
				status: r.status,
				headers: r.headers,
				data: {},
				errors: {},
				message: '',
			};

			try {
				if ((typeof r.body === 'object') && !Array.isArray(r.body)) {
					// Returned data.
					if (
						r.body.data &&
						(typeof r.body.data === 'object') &&
						!Array.isArray(r.body.data)
					) {
						out.data = r.body.data;
					}

					// Returned errors.
					if (
						r.body.errors &&
						(typeof r.body.errors === 'object') &&
						!Array.isArray(r.body.errors)
					) {
						out.errors = r.body.errors;
					}

					// A simple string message.
					if (r.body.message && (typeof r.body.message === 'string')) {
						out.message = r.body.message.trim();
					}
				}
			} catch(ex) { out.data = {}; }

			// Generate a message for a bogus response.
			if (r.status >= 300 && !this.count(out.errors)) {
				out.status = 500;
				out.errors.other = 'The server garbled the last response. :(';
			}

			// Update the nonce?
			if (r.body.session && r.body.session.n) {
				this.session.n = r.body.session.n + '';
			}

			out.ok = !this.count(out.errors);

			return out;
		};

		/**
		 * Response Errors
		 *
		 * Stuff errors from an AJAX response into the form fields.
		 *
		 * @param string $formName Form name.
		 * @param object $errors Errors.
		 * @return bool True/false.
		 */
		Vue.prototype.formErrors = function (formName, errors) {
			if (!this.forms[formName]) {
				return false;
			}

			if ((typeof errors !== 'object') || Array.isArray(errors)) {
				errors = {};
			}

			this.forms[formName].$errors = errors;
			var keys = Object.keys(errors),
				i;

			for (i=0; i<keys.length; i++) {
				var key = keys[i],
					value = errors[key];

				// If the error corresponds to a field, mention it.
				if (this.forms[formName].$fields[key]) {
					this.forms[formName].$fields[key].$valid = false;
					this.forms[formName].$fields[key].$el.setCustomValidity = value;
					this.forms[formName].$fields[key].$el.classList.add('is-invalid');
					this.forms[formName].$fields[key].$el.classList.remove('is-valid');
				}
			}

			this.$forceUpdate();
			return true;
		};

		// ------------------------------------------------------------- end submission



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
				this.session.ajaxurl,
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
		window.Vue.use(BBGFormsVue);
	}

})();