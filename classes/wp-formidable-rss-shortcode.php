<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FormidableRSSShortCode' ) ) {

	class FormidableRSSShortCode {
		public function __construct() {
			add_shortcode( 'formidable_rss', array( $this, 'formidable_rss_callback' ) );
		}

		public function formidable_rss_callback( $attr, $content = null ) {
			$params = shortcode_atts( array(
				'form_id_show'    => '',
				'form_id_episode' => '',
				'type'            => 'full',
				'redirect'        => '',
				'label'           => __( 'RSS', 'formidable-rss-parser' ),
			), $attr );

			$form_id_show = '';
			if ( ! empty( $params['form_id_show'] ) ) {
				$form_id_show = sprintf( 'data-form-id-show=%d', $params['form_id_show'] );
			}

			$html_id = FormidableRSSParser::random_string();

			$form_id_episode = '';
			if ( ! empty( $params['form_id_episode'] ) ) {
				$form_id_episode = sprintf( 'data-form-id-episode=%d', $params['form_id_episode'] );
			}

			$label = '';
			if ( ! empty( $params['label'] ) ) {
				$label = $params['label'];
			}

			$redirect = '';
			if ( ! empty( $params['redirect'] ) ) {
				$redirect = sprintf( 'data-redirect=%s', esc_url($params['redirect']) );
			}

			$type = '';
			if ( ! empty( $params['type'] ) ) {
				$type = sprintf( 'data-type=%s', $params['type'] );
			}

			FormidableRSSParserAdmin::include_assets();
			ob_start();
			include( FormidableRSSParser::get_view() . 'shortcode.php' );
			return ob_get_clean();
		}
	}
}
