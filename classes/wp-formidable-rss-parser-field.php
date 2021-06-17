<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FormidableRSSParserField' ) ) {

	class FormidableRSSParserField extends FormidableRSSParserFieldBase {
		public function __construct() {
			parent::__construct( FormidableRSSParser::get_slug(), __( 'RSS Field', 'formidable-rss-parser' ),
				array(
					'formidable_rss_option' => '0',
				),
				__( 'RSS field to parse information into Form fields.', 'formidable-rss-parser' ),
			);
			add_action( "wp_ajax_nopriv_formidable_rss_parser", array( $this, "formidable_rss_parser_callback" ) );
			add_action( "wp_ajax_formidable_rss_parse", array( $this, "formidable_rss_parser_callback" ) );
		}

		public function formidable_rss_parser_callback() {
			try {
				if ( ! ( is_array( $_POST ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
					die();
				}
				if ( ! isset( $_POST['action'] ) || ! isset( $_POST['nonce'] ) || empty( $_POST['url'] ) ) {
					die();
				}
				if ( ! wp_verify_nonce( $_POST['nonce'], FormidableRSSParser::get_slug() . __DIR__ ) ) {
					die();
				}

				$url           = sanitize_text_field( $_POST['url'] );
				$feed_response = Feed::load( $url );
				if ( ! empty( $feed_response ) ) {
					wp_send_json_success( $feed_response->toArray() );
				}
			} catch ( Exception $ex ) {
				FormidableRSSParser::error_log( $ex->getMessage() );
				wp_send_json_error( array() );
			}
			die();
		}

		protected function inside_field_options( $field, $display, $values ) {

			include( FormidableRSSParser::get_view() . 'options.php' );
		}

		protected function field_front_view( $field, $field_name, $html_id ) {
			$field['value'] = stripslashes_deep( $field['value'] );
			$print_value    = $field['default_value'];
			if ( ! empty( $field['value'] ) ) {
				$print_value = $field['value'];
			}

			$value = ( empty( $field['value'] ) ) ? $field['default_value'] : $field['value'];

			$html_id            = $field['field_key'];
			$form_id            = $field['form_id'];
			$field_id           = $field['id'];
			$field_container_id = sprintf( 'frm_field_%s_container', $field['id'] );
			$file_name          = str_replace( 'item_meta[' . $field['id'] . ']', 'file' . $field['id'], $field_name );

			wp_enqueue_script( 'formidableRSSParserLodash', FormidableRSSParser::assets_path( 'lodash.min' ), array( 'jquery' ), FormidableRSSParser::get_version(), true );
			wp_enqueue_script( 'formidableRSSParser', FormidableRSSParser::assets_path( 'script' ), array( 'jquery', 'formidableRSSParserLodash' ), FormidableRSSParser::get_version(), true );
			$args = array(
				'admin_url' => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( FormidableRSSParser::get_slug() . __DIR__ ),
			);
			wp_localize_script( 'formidableRSSParser', 'formidableRSSParserObj', $args );
			include( FormidableRSSParser::get_view() . 'field.php' );
		}


		protected function display_options( $display ) {
			$display['unique']         = true;
			$display['required']       = true;
			$display['read_only']      = true;
			$display['description']    = true;
			$display['options']        = true;
			$display['label_position'] = true;
			$display['css']            = true;
			$display['conf_field']     = true;
			$display['invalid']        = true;
			$display['default_value']  = true;
			$display['visibility']     = true;
			$display['size']           = true;

			return $display;
		}

		/**
		 * Add class to the field
		 *
		 * @param $classes
		 * @param $field
		 *
		 * @return string
		 */
		public function fields_class( $classes, $field ) {
			$classes .= ' formidable-rss-parser ';

			return $classes;
		}

		protected function validate_frm_entry( $errors, $posted_field, $posted_value ) {
			/*
			 * todo: only execute this validation if the required is enabled
			 * todo: add validation for the case of invalid url, using the same regex from JS
			 */
			$field_error_key = sprintf( 'field%s', $posted_field->id );
			if ( empty( $posted_value ) ) {
				//todo add the current field name, or use the message comming from the field settings
				$errors[ $field_error_key ] = __( 'RSS field cannot be blank.', 'formidable-rss-parser' );
			}

			return $errors;
		}

		/**
		 * Me quede en implementar la llamada de jquery para hacer la peticion desde el servidor de la informacion del RSS,
		 * cuando le pase la url al servidor entonces con php consumir el servivio y despues retornar esa respuesta al frontend en la respuesta del ajax
		 * con la respuesta de ajax entonces hay que iterar sobre todos los campos y leer el atributo donde definen que valor debe ponerse
		 * Hay que implementar las opciones nuevas de los campos donde establecer que campos se van a parsear
		 * Los detalles menores como validaciones mas fuertes y mejoras de codigo pueden quedar para despues.
		 */
	}
}
