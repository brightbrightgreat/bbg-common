<?php
/**
 * BBG Common: Debug
 *
 * This provides debug functionality primarily aimed at informing
 * developers of deprecated or incorrectly used functions.
 *
 * Results will be written to wp-content/bbg-debug.log.
 * If WP_DEBUG_LOG is enabled, results will be written there too.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common;

use \blobfolio\common\data;
use \blobfolio\common\ref\sanitize as r_sanitize;

class debug {
	const DOING_IT_WRONG = 'DOING IT WRONG';
	const DEPRECATED = 'DEPRECATED METHOD';

	// Trace example.
	const TRACE = array(
		'callback'=>'',
		'file'=>'',
		'line'=>0,
	);

	const TEMPLATE = '%s: %s';
	const TEMPLATE_TRACE = "\n# %d\n# %s() on line %d in %s";



	// -----------------------------------------------------------------
	// Public Methods
	// -----------------------------------------------------------------

	/**
	 * Doing It Wrong
	 *
	 * Record a notification that a function was used incorrectly. For
	 * example, perhaps an argument is expected to be a WP_Post object,
	 * but a post ID was passed instead.
	 *
	 * @param string $reason Reason.
	 * @param bool $die Terminate script.
	 * @return void Nothing.
	 */
	public static function wrong(string $reason='', bool $die=false) {
		// Provide a default message if none is sent.
		$message = "This method was used incorrectly. $reason";

		// Log it.
		static::log(static::DOING_IT_WRONG, $message, $die);
	}

	/**
	 * Deprecated Function
	 *
	 * Record a notification that the current function is deprecated.
	 *
	 * If this function has been replaced by another, pass it via the
	 * $replacement argument. This should be a callable value, either a
	 * string like 'my_function' or '\\namespace\\class::my_function' or
	 * a parseable object or array.
	 *
	 * @param mixed $replacement Preferred callback.
	 * @param bool $die Terminate script.
	 * @return void Nothing.
	 */
	public static function deprecated($replacement=null, bool $die=false) {
		$message = 'This method is deprecated and might be removed from a future release.';

		// Parse the replacement.
		if (!is_null($replacement)) {
			is_callable($replacement, true, $callback);
			if ($callback) {
				$message .= sprintf(
					' Use %s() instead.',
					$callback
				);
			}
		}

		// Log it.
		static::log(static::DEPRECATED, $message, $die);
	}

	// ----------------------------------------------------------------- end public



	// -----------------------------------------------------------------
	// Internal
	// -----------------------------------------------------------------

	/**
	 * Parse Trace
	 *
	 * Do a debug trace to gather information about the origination of
	 * the message.
	 *
	 * @return bool|array Trace.
	 */
	protected static function trace() {
		// First of all, run a brief trace. Limit the depth as WP can
		// get out of control sometimes.
		$trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 10);

		// The first two entries belong to debugging functions and
		// should be ignored.
		if (count($trace) < 4) {
			return false;
		}
		array_splice($trace, 0, 3);

		// Clean up the formatting.
		$out = array();
		foreach ($trace as $v) {
			// We only want function calls.
			if (!isset($v['function']) || !$v['function']) {
				continue;
			}

			$line = static::TRACE;

			// Clean up the function name.
			$line['callback'] = $v['function'];
			if (isset($v['class'], $v['type'])) {
				$line['callback'] = "{$v['class']}{$v['type']}{$line['callback']}";
			}

			$line['file'] = isset($v['file']) ? $v['file'] : '';
			$line['line'] = isset($v['line']) ? $v['line'] : 0;

			// Save it.
			$out[] = data::parse_args($line, static::TRACE);
		}

		return count($out) ? $out : false;
	}

	/**
	 * Log Gateway
	 *
	 * This will compile the main log message and send it to the
	 * specialized methods for actual reporting.
	 *
	 * @param string $type Type.
	 * @param string $message Message.
	 * @param bool $die Terminate script.
	 * @return void Nothing.
	 */
	protected static function log(string $type, string $message, bool $die=false) {
		r_sanitize::whitespace($message);

		// If either the trace or message is empty, we're done.
		if (!$message || (false === ($trace = static::trace()))) {
			// Abort?
			if ($die) {
				exit;
			}

			return;
		}

		// Insert the correct function name into the message.
		$message = preg_replace('/this (method|function)/i', "{$trace[0]['callback']}()", $message);

		// The main message.
		$out = sprintf(
			static::TEMPLATE,
			$type,
			$message
		);

		if (count($trace)) {
			$out .= "\nStack trace:";

			$num = -1;
			foreach ($trace as $v) {
				$num++;

				$out .= "\n" . trim(sprintf(
					static::TEMPLATE_TRACE,
					$num,
					$v['callback'],
					$v['line'],
					$v['file']
				));
			}
		}

		// Log to bbg-debug.log.
		static::log_bbg($out);

		// Send to debug.log.
		static::log_wp($out);

		// Abort?
		if ($die) {
			exit;
		}

		return;
	}

	/**
	 * Log: BBG
	 *
	 * Save an entry to bbg-debug.log.
	 *
	 * @param string $message Message.
	 * @return bool True/false.
	 */
	protected static function log_bbg(string $message) {
		$message = trim($message);
		if (!$message) {
			return false;
		}

		// Create the log file if it doesn't exist.
		$log = trailingslashit(WP_CONTENT_DIR) . '/bbg-debug.log';
		if (!file_exists($log)) {
			touch($log);
			chmod($log, 0644);
		}
		if (!file_exists($log)) {
			return false;
		}

		// Write it!
		file_put_contents($log, '[' . date('d-M-Y H:i:s') . " UTC] $message\n\n", FILE_APPEND);
		return true;
	}

	/**
	 * Log: WP
	 *
	 * This only applies if WP_DEBUG and WP_DEBUG_LOG are enabled.
	 *
	 * @param string $message Message.
	 * @return bool True/false.
	 */
	protected static function log_wp(string $message) {
		$message = trim($message);
		if (!$message) {
			return false;
		}

		if (
			defined('WP_DEBUG') &&
			defined('WP_DEBUG_LOG') &&
			WP_DEBUG &&
			WP_DEBUG_LOG
		) {
			error_log($message, 0);
			return true;
		}

		return false;
	}

	// ----------------------------------------------------------------- end internal
}
