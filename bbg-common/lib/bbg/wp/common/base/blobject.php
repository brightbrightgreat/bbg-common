<?php
/**
 * Blobject
 *
 * This is a generic class that provides basic construct and magic
 * functionality. A lot of code can be reduced by extending this.
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common\base;

use \blobfolio\common\data;
use \blobfolio\common\format as v_format;
use \blobfolio\common\ref\cast as r_cast;

abstract class blobject {

	// The key name for the object's main ID entry.
	const ITEM_ID = '';

	// Each extension needs its own template for main data. Data stored
	// elsewhere needs additional handling.
	const TEMPLATE = array();

	// The generic ::get_array() method can optionally remove pieces we
	// don't want to share like passwords, etc.
	const SENSITIVE_FIELDS = array();

	// Fields containing currency can be auto-formatted.
	const MONEY_FIELDS = array();

	protected static $_instances = array();
	protected $data;



	// -----------------------------------------------------------------
	// Init/Setup
	// -----------------------------------------------------------------

	/**
	 * Pre-Construct
	 *
	 * Cache static objects locally for better performance.
	 *
	 * @param mixed $item_id Item ID.
	 * @param bool $refresh Refresh.
	 * @return object Instance.
	 */
	public static function get($item_id=null, bool $refresh=false) {
		// Figure out whether we're making a new instance or not.
		$class = get_called_class();
		if (!isset(self::$_instances[$class])) {
			self::$_instances[$class] = array();
		}

		static::translate_id($item_id);

		if (!$item_id) {
			return new static();
		}

		// Get the right object.
		if ($refresh || !isset(self::$_instances[$class][$item_id])) {
			self::$_instances[$class][$item_id] = new static($item_id);
			if (!self::$_instances[$class][$item_id]->is_item()) {
				unset(self::$_instances[$class][$item_id]);
				return new static();
			}
		}

		return self::$_instances[$class][$item_id];
	}

	/**
	 * Translate Item ID
	 *
	 * Some classes can be initialized with different types of keys.
	 * Ultimately, we need one kind of ID, so this translates if needed.
	 *
	 * @param mixed $item_id Item ID.
	 * @return mixed Item ID.
	 */
	protected static function translate_id(&$item_id='') {
		return true;
	}

	/**
	 * Is Item
	 *
	 * @return bool True/false
	 */
	protected function is_item() {
		return is_array($this->data) && $this->data[static::ITEM_ID];
	}

	// ----------------------------------------------------------------- end setup



	// -----------------------------------------------------------------
	// Get Data
	// -----------------------------------------------------------------

	/**
	 * Magic Getter
	 *
	 * @param string $method Method name.
	 * @param mixed $args Arguments.
	 * @return mixed Variable.
	 * @throws \Exception Invalid method.
	 */
	public function __call($method, $args) {
		preg_match_all('/^get_(.+)$/', $method, $matches);
		if (
			count($matches[0]) &&
			(isset($this->data[$matches[1][0]]) || isset(static::TEMPLATE[$matches[1][0]]))
		) {
			$variable = $matches[1][0];
			$value = isset($this->data[$variable]) ? $this->data[$variable] : static::TEMPLATE[$variable];

			// Dates.
			if (0 === strpos($variable, 'date')) {
				if (is_array($args) && count($args)) {
					$args = data::array_pop_top($args);
					r_cast::string($args, true);
				}
				else {
					$args = 'Y-m-d H:i:s';
				}
				return date($args, strtotime($value));
			}
			// Money.
			elseif (in_array($variable, static::MONEY_FIELDS, true)) {
				if (is_array($args) && count($args)) {
					$args = data::array_pop_top($args);
					r_cast::bool($args, true);

					if ($args) {
						return v_format::money($value, false, ',', false);
					}
				}

				return round($value, 2);
			}

			// Everything else.
			return $value;
		}

		throw new \Exception(
			sprintf(
				_('The required method "%s" does not exist for %s.'),
				$method,
				get_called_class()
			)
		);
	}

	/**
	 * Get Array
	 *
	 * Return object in array format.
	 *
	 * @param bool $sensitive Include sensitive data.
	 * @param bool $extra Include extra data.
	 * @return array Data.
	 */
	public function get_array(bool $sensitive=false, bool $extra=true) {
		$data = $this->data;
		if (!is_array($data)) {
			$data = static::TEMPLATE;
		}

		// Remove a few authenticationy bits.
		if (!$sensitive) {
			foreach (static::SENSITIVE_FIELDS as $field) {
				if (isset($data[$field])) {
					unset($data[$field]);
				}
			}
		}

		// Optionally filter results.
		if ($extra) {
			static::filter_get_array($data, $sensitive);
		}

		return $data;
	}

	/**
	 * Get Array Filter
	 *
	 * Just in case any class extensions need to alter data beyond the
	 * obvious.
	 *
	 * @param array $data Data.
	 * @param bool $sensitive Include sensitive data.
	 * @return void Nothing.
	 */
	public function filter_get_array(&$data, bool $sensitive=false) {

	}

	// ----------------------------------------------------------------- end data
}
