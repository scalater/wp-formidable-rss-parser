<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FormidableRSSParserFieldOptions' ) ) {
	class FormidableRSSParserFieldOptions {
		public function __construct() {
			add_action( 'frm_field_options_form', array( $this, 'field_field_option_form' ), 10, 3 );
			add_action( 'frm_update_field_options', array( $this, 'update_field_options' ), 10, 3 );
			add_action( 'frm_field_input_html', array( $this, 'field_input_html' ), 10, 1 );
		}

		public function field_input_html( $field, $echo = true ) {
			$html = '';
			if ( ! empty( $field ) ) {
				$have_parser_set = ! empty( $field['rss_parser_match'] );
				if ( $have_parser_set ) {
					$html = sprintf( ' data-parser-path="%s" ', esc_attr( $field['rss_parser_match'] ) );
				}
			}
			if ( $echo ) {
				echo $html;
			}

			return $html;
		}

		/**
		 * Display the additional options for the new field
		 *
		 * @param $field
		 * @param $display
		 * @param $values
		 */
		public function field_field_option_form( $field, $display, $values ) {

			require( FormidableRSSParser::get_view() . 'parser-options.php' );
		}

		/**
		 * Update the field options from the admin area
		 *
		 * @param $field_options
		 * @param $field
		 * @param $values
		 *
		 * @return mixed
		 */
		public function update_field_options( $field_options, $field, $values ) {

			$defaults = array(
				'rss_parser_match' => '',
			);

			foreach ( $defaults as $opt => $default ) {
				$field_options[ $opt ] = isset( $values['field_options'][ $opt . '_' . $field->id ] ) ? $values['field_options'][ $opt . '_' . $field->id ] : $default;
			}

			return $field_options;
		}

	}
}
