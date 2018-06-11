/**
 * Vue Filters
 *
 * This contains a collection of handy filters for Vue. It is loaded
 * automatically.
 */
/* global fecha */
(function() {

	var BBGFilterVue = {
		/**
		 * Install
		 *
		 * @param {Vue} Vue Vue.
		 * @returns {void} Nothing.
		 */
		install: function(Vue) {
			Vue.mixin({
				filters: {
					/**
					 * Format USD
					 *
					 * @param {float} value Value.
					 * @returns {string} Value.
					 */
					money: function(value) {
						value = value + '';
						value = value.replace(/[^\d.]/g, '');
						value = Number(value) || 0;
						value = value.toFixed(2);
						value = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
						return '$' + value;
					},

					/**
					 * Address
					 *
					 * @param {object} value Value.
					 * @returns {string} Value.
					 */
					address: function(value) {
						var out = [];
						var tmp;

						if (value.firstname || value.lastname) {
							tmp = value.firstname + ' ' + value.lastname;
							out.push(tmp.trim());
						}
						else if (value.name) {
							out.push(value.name);
						}

						// 2-line addresses. This is the most common.
						if (
							('undefined' !== typeof value.address_1) &&
							('undefined' !== typeof value.address_2)
						) {
							if (value.address_1) {
								out.push(value.address_1);
							}

							if (value.address_2) {
								out.push(value.address_2);
							}
						}
						// Single-line addresses.
						else if (('undefined' !== typeof value.address) && value.address) {
							out.push(value.address);
						}

						// Do city, state, and zip together.
						if (value.city) {
							tmp = [];
							if (value.city) {
								tmp.push(value.city);
							}
							if (value.state) {
								tmp.push(value.state);
							}
							tmp = tmp.join(', ');
							if (('US' === value.country) && value.zip) {
								tmp += ' ' + value.zip;
							}
							if (tmp) {
								out.push(tmp);
							}
						}

						// For non-US countries, add the zip to the end.
						if (value.country) {
							out.push(value.country);

							if (('US' !== value.country) && value.zip) {
								out.push(value.zip);
							}
						}

						return out.join('\n');
					},

					/**
					 * Zero Pad
					 *
					 * @param {int} value Value.
					 * @param {int} length Length.
					 * @returns {string} Value.
					 */
					zeroPad: function(value, length) {
						value = parseInt(value, 10) || 0;
						value = value + '';

						length = parseInt(length, 10) || 0;
						while (length > value.length) {
							value = '0' + value;
						}

						return value;
					},

					/**
					 * Nice Date
					 *
					 * @param {string} value Value.
					 * @param {string} format Format.
					 * @returns {string} Value.
					 */
					niceDate: function(value, format) {
						var original = (value + '').trim();
						var inFormat = 'YYYY-MM-DD HH:mm:ss';

						// Default date format.
						if (!format) {
							format = 'MM/DD/YYYY';
						}

						// Parsing.
						if (10 === value.length) {
							inFormat = 'YYYY-MM-DD';
						}

						// Format it.
						try {
							if ('string' === typeof value) {
								value = fecha.format(fecha.parse(value, inFormat), format);
								return value;
							}
						} catch (Ex) {
							return original;
						}
					},

					/**
					 * Ucwords
					 *
					 * @param {string} value Value.
					 * @returns {string} Value.
					 */
					ucwords: function(value) {
						return (value + '').replace(/^(.)|\s+(.)/g, function($1) {
							return $1.toUpperCase();
						});
					},
				},
			});

		},
	};

	if ('undefined' !== typeof window && window.Vue) {
		window.Vue.use(BBGFilterVue);
	}
})();
