/**
 * Vue Directives
 *
 * This contains a collection of handy directives for Vue. It is loaded
 * automatically.
 */
(function(){

	var BBGDirectiveVue = {};
	BBGDirectiveVue.install = function(Vue, options){

		/**
		 * v-numbers-only
		 *
		 * For text fields that should only allow numeric inputs.
		 */
		Vue.directive('numbers-only', {
			id: 'numbers-only',
			priority: 999999,
			/**
			 * Element with this directive has fully landed in the DOM.
			 *
			 * @param DOMElement $el Element.
			 * @param object $binding Vue data.
			 * @param object $vnode Vue node.
			 * @return void Nothing.
			 */
			bind: function(el, binding, vnode) {
				// Parse the model's parent object.
				var model = _getModelName(vnode);
				model = ('vnode.context.' + model).split('.');
				var modelKey = model.pop();
				/* jshint ignore:start */
				model = eval(model.join('.'));
				/* jshint ignore:end */

				// Bind to the input event.
				el.addEventListener('input', function(){
					// Delay any changes until nextTick to be safe.
					Vue.nextTick(function(){
						var value = el.value;
						if (typeof value !== 'string') {
							value = '';
						}

						value = value.replace(/[^\d]/g, '');
						model[modelKey] = value;
					});
				});
			},
			inserted: function(el) {
				// Try to force an onChange() whenever the element is
				// inserted, just in case things start with a weird
				// value.
				if ('createEvent' in document) {
					var evt = document.createEvent('HTMLEvents');
					evt.initEvent('input', false, true);
					el.dispatchEvent(evt);
				}
				else {
					el.fireEvent('oninput');
				}
			}
		});

		/**
		 * v-country
		 *
		 * For address forms with country-dependent state fields, we
		 * need special watchers to update states in cases where the
		 * previous value is not in the new list.
		 */
		Vue.directive('country', {
			id: 'country',
			priority: 999999,
			/**
			 * Element with this directive has fully landed in the DOM.
			 *
			 * @param DOMElement $el Element.
			 * @param object $binding Vue data.
			 * @param object $vnode Vue node.
			 * @return void Nothing.
			 */
			bind: function(el, binding, vnode) {
				// We should have GEO info loaded.
				if (typeof vnode.context.geo === 'undefined') {
					console.warn('v-country requires geographic data. Define the PHP constant USE_GEO_JS.');
					return;
				}

				// Parse the model's parent object.
				var model = _getModelName(vnode);
				model = ('vnode.context.' + model).split('.');
				model.pop();
				/* jshint ignore:start */
				model = eval(model.join('.'));
				/* jshint ignore:end */

				// The model should have both country and state keys.
				if (
					(typeof model.country !== 'string') ||
					(typeof model.state !== 'string')
				) {
					console.warn('v-country expects both obj.country and obj.state keys.');
					return;
				}

				// Bind to the change event.
				el.addEventListener('change', function(){
					// Delay any changes until nextTick to be safe.
					Vue.nextTick(function(){
						var found = false,
							states;

						// What are we searching?
						if ('US' === model.country) {
							states = vnode.context.geo.states;
						}
						else if ('CA' === model.country) {
							states = vnode.context.geo.provinces;
						}
						// The rest of the world is open-ended.
						else {
							return;
						}

						// Make sure the previous value makes sense.
						if (model.state) {
							for (var i=0; i<states.length; i++) {
								if (
									(model.state === states[i].key) ||
									(model.state.toLowerCase() === states[i].value.toLowerCase())
								) {
									model.state = states[i].key;
									found = true;
									break;
								}
							}
						}

						// Default to the first state.
						if (!found) {
							model.state = states[0].key;
						}
					});
				});
			},
			inserted: function(el) {
				// Try to force an onChange() whenever the element is
				// inserted, just in case things start with a weird
				// state.
				if ('createEvent' in document) {
					var evt = document.createEvent('HTMLEvents');
					evt.initEvent('change', false, true);
					el.dispatchEvent(evt);
				}
				else {
					el.fireEvent('onchange');
				}
			}
		});

		/**
		 * v-click-outside
		 *
		 * Detect whether clicks have happened outside this element.
		 *
		 * @param callback $callback Callback function.
		 */
		Vue.directive('click-outside', {
			bind: function(el, binding, vnode) {
				// Provided expression must evaluate to a function.
				if (typeof binding.value !== 'function') {
					console.warn('[v-click-outside:] provided expression "' + binding.expression + '" is not a function.');
					return;
				}

				// Define Handler and cache it on the element.
				var bubble = binding.modifiers.bubble,
					handler = function(e) {
						if (bubble || (!el.contains(e.target) && el !== e.target)) {
							binding.value.call(vnode.context, e);
						}
					};

				el.__vueClickOutside__ = handler;

				// add Event Listeners
				document.addEventListener('click', handler);
			},

			unbind: function(el, binding) {
				// Remove Event Listeners
				document.removeEventListener('click', el.__vueClickOutside__);
				el.__vueClickOutside__ = null;
			}
		});

		/**
		 * Get Model Name
		 *
		 * Directives are no longer meant to write back to the model,
		 * but why should that stop us? This function will find the
		 * relevant model name so we can do something about it.
		 *
		 * @param Vue $vnode Vnode.
		 * @return string Name.
		 */
		function _getModelName(vnode) {
			try {
				return vnode.data.directives.find(function(o) {
					return o.name === 'model';
				}).expression;
			} catch (Ex) {
				return false;
			}
		}

	};

	if (typeof window !== 'undefined' && window.Vue) {
		window.Vue.use(BBGDirectiveVue);
	}
})();
