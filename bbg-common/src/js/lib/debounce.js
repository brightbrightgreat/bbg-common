/**
 * Debounce
 *
 * @param {function} fn Function.
 * @param {int} wait Timeout.
 * @param {bool} no_postpone Start now.
 * @returns {void} Nothing.
 */
var blobDebounce = function(fn, wait, no_postpone){
	var args, context, result, timeout;
	var executed = true;

	// Execute the callback function.
	function ping(){
		result = fn.apply(context || this, args || []);
		context = args = null;
		executed = true;
	}

	// Cancel the timeout.
	function cancel() {
		if(timeout){
			clearTimeout(timeout);
			timeout = null;
		}
	}

	// Generate a wrapper function to return.
	function wrapper() {
		context = this;
		args = arguments;
		if (!no_postpone) {
			cancel();
			timeout = setTimeout(ping, wait);
		}
		else if (executed) {
			executed = false;
			timeout = setTimeout(ping, wait);
		}
	}

	// Reset.
	wrapper.cancel = cancel;
	return wrapper;
};

/**
 * Throttle
 *
 * @param {function} fn Function.
 * @param {int} wait Timeout.
 * @returns {void} Nothing.
 */
var blobThrottle = function(fn, wait){
	var args, context, result, timeout, last;

	// Execute the callback function.
	function ping(){
		result = fn.apply(context || this, args || []);
		context = args = null;
		last = new Date();
	}

	// Cancel the timeout.
	function cancel() {
		if(timeout){
			clearTimeout(timeout);
			timeout = null;
		}
	}

	// Generate a wrapper function to return.
	function wrapper() {
		context = this;
		args = arguments;

		//run it now?
		if(!last || +new Date() - wait > last){
			cancel();
			ping();
		}
		else {
			cancel();
			timeout = setTimeout(ping, wait);
		}
	}

	// Reset.
	wrapper.cancel = cancel;
	return wrapper;
};
