<?php
/**
 * Cleanup fields data before saving
 *
 * @package PuppyFW\Builder
 */

namespace PuppyFW\Builder;

/**
 * Class Cleanup
 */
class Cleanup {

	/**
	 * Cleans up field data.
	 *
	 * @param array $fields Fields data.
	 * @return array
	 */
	public function clean( $fields ) {
		foreach ( $fields as $index => $field ) {
			$this->clean_nested_fields( $field );
			$this->clean_key_value_data( $field, 'attrs' );
			$this->clean_key_value_data( $field, 'options' );
			$this->clean_key_value_data( $field, 'js_options' );
			$this->normalize_image_default( $field );
			$this->normalize_images_default( $field );

			if ( ! empty( $field['fields'] ) ) {
				$field['fields'] = $this->clean( $field['fields'] );
			}

			$fields[ $index ] = $field;
		}

		return $fields;
	}

	/**
	 * Cleans key value parameter.
	 *
	 * @param array  $field Field data.
	 * @param string $name  Parameter name.
	 */
	protected function clean_key_value_data( &$field, $name ) {
		if ( empty( $field[ $name ] ) || ! is_array( $field[ $name ] ) ) {
			$field[ $name ] = array();
			return;
		}

		foreach ( $field[ $name ] as $index => $value ) {
			if ( empty( $value['key'] ) ) {
				unset( $field[ $name ][ $index ] );
			}
		}
	}

	/**
	 * Normalizes default of image field.
	 *
	 * @param array $field Field data.
	 */
	protected function normalize_image_default( &$field ) {
		if ( 'image' !== $field['type'] ) {
			return;
		}

		if ( empty( $field['default'] ) ) {
			return;
		}

		if ( is_numeric( $field['default'] ) ) {
			$field['default'] = array(
				'id'  => intval( $field['default'] ),
				'url' => '',
			);
			return;
		}

		$field['default'] = array(
			'id'  => '',
			'url' => esc_url( $field['default'] ),
		);
	}

	/**
	 * Normalizes default of images field.
	 *
	 * @param array $field Field data.
	 */
	protected function normalize_images_default( &$field ) {
		if ( 'images' !== $field['type'] ) {
			return;
		}

		$field['default'] = array();
	}

	/**
	 * Cleans nested fields.
	 *
	 * @param array $field Field data.
	 */
	protected function clean_nested_fields( &$field ) {
		if ( in_array( $field['type'], $this->get_fields_have_nested() ) ) {
			return;
		}

		if ( isset( $field['fields'] ) ) {
			unset( $field['fields'] );
		}
	}

	/**
	 * Gets field types have nested.
	 *
	 * @return array
	 */
	protected function get_fields_have_nested() {
		/**
		 * Filters field types have nested.
		 *
		 * @since 0.3.0
		 *
		 * @param array $types Field types which have nested.
		 */
		return apply_filters( 'puppyfw_fields_have_nested', array( 'group', 'tab' ) );
	}
}
