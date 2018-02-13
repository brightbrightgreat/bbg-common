/**
 * Vue Filters
 *
 * This contains a collection of handy filters for Vue. It is loaded
 * automatically.
 */
(function(){

	var BBGFilterVue = {};
	BBGFilterVue.install = function(Vue, options){

		Vue.mixin({
			filters: {
				// Format a number as USD.
				money: function(value) {
					value = value + '';
					value = value.replace(/[^\d\.]/g, '');
					value = Number(value) || 0;
					value = value.toFixed(2);
					value = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
					return '$' + value;
				},

				// Format an address.
				address: function(value) {
					var out = [],
						tmp;

					if (value.firstname || value.lastname) {
						tmp = value.firstname + ' ' + value.lastname;
						out.push(tmp.trim());
					}
					else if(value.name) {
						out.push(value.name);
					}

					// 2-line addresses. This is the most common.
					if (
						(typeof value.address_1 !== 'undefined') &&
						(typeof value.address_2 !== 'undefined')
					) {
						if (value.address_1) {
							out.push(value.address_1);
						}

						if (value.address_2) {
							out.push(value.address_2);
						}
					}
					// Single-line addresses.
					else if ((typeof value.address !== 'undefined') && value.address) {
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
						if ((value.country === 'US') && value.zip) {
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

					return out.join("\n");
				},

				// Pad a number with zeroes so it is a certain length.
				zeroPad: function(value, length) {
					value = parseInt(value, 10) || 0;
					value = value + '';

					length = parseInt(length, 10) || 0;
					while (length > value.length) {
						value = '0' + value;
					}

					return value;
				},

				// Easily format a date string.
				niceDate: function(value, format) {
					var original = (value + '').trim(),
					inFormat = 'YYYY-MM-DD HH:mm:ss';

					// Default date format.
					if (!format) {
						format = 'MM/DD/YYYY';
					}

					// Parsing.
					if (value.length === 10) {
						inFormat = 'YYYY-MM-DD';
					}

					// Format it.
					try {
						if (typeof value === 'string') {
							value = fecha.format(fecha.parse(value, inFormat), format);
							return value;
						}
					} catch(Ex) {
						return original;
					}
				},

				// Capitalize the first letter of each word.
				ucwords: function(value) {
					return (value + '').replace(/^(.)|\s+(.)/g, function ($1) {
						return $1.toUpperCase();
					});
				}
			},
		});

	};

	if (typeof window !== 'undefined' && window.Vue) {
		window.Vue.use(BBGFilterVue);
	}
})();