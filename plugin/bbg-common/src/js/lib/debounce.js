//-------------------------------------------------
// simple debounce
//
// @param callback
// @param timeout
var blobDebounce = function(fn, wait, no_postpone){
	var args, context, result, timeout;
	var executed = true;

	//execute the callback function
	function ping(){
		result = fn.apply(context || this, args || []);
		context = args = null;
		executed = true;
	}

	//cancel the timeout
	function cancel() {
		if(timeout){
			clearTimeout(timeout);
			timeout = null;
		}
	}

	//generate a wrapper function to return
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

	//reset
	wrapper.cancel = cancel;
	return wrapper;
};

//-------------------------------------------------
// simple throttle
//
// @param callback
// @param timeout
var blobThrottle = function(fn, wait){
	var args, context, result, timeout, last;

	//execute the callback function
	function ping(){
		result = fn.apply(context || this, args || []);
		context = args = null;
		last = new Date();
	}

	//cancel the timeout
	function cancel() {
		if(timeout){
			clearTimeout(timeout);
			timeout = null;
		}
	}

	//generate a wrapper function to return
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

	//reset
	wrapper.cancel = cancel;
	return wrapper;
};