<?php
/**
 * Postmeta Datastore: Serialize Complex Fields
 *
 * Store "Complex" field data as a single, serialized row in the
 * database rather than a hundred separate entries.
 *
 * To use, add the following to a Field::make():
 * ->set_datastore(new bbg\wp\common\fields\serialized());
 *
 * @package bbg-common
 * @author  Bright Bright Great <sayhello@brightbrightgreat.com>
 */

namespace bbg\wp\common\fields;

use \Carbon_Fields\Field\Field;
use \Carbon_Fields\Datastore\Datastore;

/**
 * Stores serialized values in the database
 */
class serialized_post_meta extends Datastore {

	/**
	 * Init
	 *
	 * @return void Nothing.
	 */
	public function init() {

	}

	/**
	 * Retrieve the type of meta data.
	 *
	 * @return string
	 */
	public function get_meta_type() {
		return 'post';
	}

	/**
	 * Retrieve the meta table name to query.
	 *
	 * @return string
	 */
	public function get_table_name() {
		global $wpdb;
		return $wpdb->postmeta;
	}

	/**
	 * Retrieve the meta table field name to query by.
	 *
	 * @return string
	 */
	public function get_table_field_name() {
		return 'post_id';
	}

	/**
	 * Get Field Key
	 *
	 * @param Field $field Field.
	 * @return string Key.
	 */
	protected function get_key_for_field(Field $field) {
		$key = '_' . $field->get_base_name();
		return $key;
	}

	/**
	 * Merge Save
	 *
	 * @param string $key Key.
	 * @param string $value Value.
	 * @return void Nothing.
	 */
	protected function save_key_value_pair($key, $value) {
		if (!update_metadata(
			$this->get_meta_type(),
			$this->get_object_id(),
			$key,
			$value
		)) {
			add_metadata($this->get_meta_type(), $this->get_object_id(), $key, $value, true);
		}
	}

	/**
	 * Load the Field Value(s)
	 *
	 * @param Field $field Field.
	 * @return array Value(s).
	 */
	public function load(Field $field) {
		$key = $this->get_key_for_field($field);
		$value = get_metadata($this->get_meta_type(), $this->get_object_id(), $key, true);

		if (!is_array($value)) {
			$value = array();
		}

		return $value;
	}

	/**
	 * Save the Field Value(s)
	 *
	 * @param Field $field Field.
	 * @return void Nothing.
	 */
	public function save(Field $field) {
		// Only applies to root-level fields.
		if (!empty($field->get_hierarchy())) {
			return;
		}

		$key = $this->get_key_for_field($field);
		$value = $field->get_full_value();
		if (is_a($field, '\\Carbon_Fields\\Field\\Complex_Field')) {
			$value = $field->get_value_tree();
		}
		$this->save_key_value_pair($key, $value);
	}

	/**
	 * Delete the Field Value(s)
	 *
	 * @param Field $field Field.
	 * @return void Nothing.
	 */
	public function delete(Field $field) {
		// Only applies to root-level fields.
		if (!empty($field->get_hierarchy())) {
			return;
		}

		$key = $this->get_key_for_field($field);
		delete_metadata($this->get_meta_type(), $this->get_object_id(), $key);
	}
}
