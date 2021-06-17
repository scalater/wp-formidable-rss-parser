<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FormidableRSSParserAdmin' ) ) {

	class FormidableRSSParserAdmin {
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'create_setting_page' ) );
			add_action( 'admin_init', array( $this, 'settings_init' ) );
		}

		public function create_setting_page() {
			add_options_page( __( 'Formidable RSS Parser', 'formidable-rss-parser' ), __( 'Formidable RSS Parser', 'formidable-rss-parser' ), 'manage_options', FormidableRSSParser::get_slug(), array( $this, 'formidable_rss_parser_page' ) );
		}

		public function formidable_rss_parser_page() {
			?>
			<div class="wrap">

				<div id="icon-options-general" class="icon32"><br></div>
				<h2> <?php _e( 'WpHtmlCssToImage', 'formidable-rss-parser' ); ?></h2>
				<div style="overflow: auto;">
					<span style="font-size: 13px; float:right;"><?php _e( 'Proudly brought to you by ', 'formidable-rss-parser' ); ?><a href="https://www.scalater.com/" target="_new">Scalater</a>.</span>
				</div>

				<form method="post" action="options.php">
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( 'wp_formidable_rss_parser_option' ); ?>
					<?php do_settings_sections( 'wp_formidable_rss_parser_option' ); ?>
					<?php submit_button(); ?>
				</form>

			</div>
			<?php
		}

		public function settings_init() {
			add_settings_section( 'wp_formidable_rss_parser_section', '', '', 'wp_formidable_rss_parser_option' );

			add_settings_field( 'wp_formidable_rss_parser_user_id', __( 'User Id', 'formidable-rss-parser' ), array( $this, 'wp_formidable_rss_parser_user_id_cb' ), 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_section' );
			add_settings_field( 'wp_formidable_rss_parser_api_key', __( 'API key', 'formidable-rss-parser' ), array( $this, 'wp_formidable_rss_parser_api_key_cb' ), 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_section' );
			add_settings_field( 'wp_formidable_rss_parser_header', __( 'Header', 'formidable-rss-parser' ), array( $this, 'wp_formidable_rss_parser_header_cb' ), 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_section' );

			register_setting( 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_user_id' );
			register_setting( 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_api_key' );
			register_setting( 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_header' );
		}

		public function wp_formidable_rss_parser_user_id_cb() {
			$value = get_option( 'wp_formidable_rss_parser_user_id' );
			?>
			<p>
				<input type="password" name="wp_formidable_rss_parser_user_id" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>" style="width: 350px;">
			</p>
			<?php
		}

		public function wp_formidable_rss_parser_api_key_cb() {
			$value = get_option( 'wp_formidable_rss_parser_api_key' );
			?>
			<p>
				<input type="password" name="wp_formidable_rss_parser_api_key" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>" style="width: 350px;">
			</p>
			<?php
		}

		public function wp_formidable_rss_parser_header_cb() {
			$value = get_option( 'wp_formidable_rss_parser_header' );
			?>
			<p>
				<textarea style="width: 550px;" rows="20" name="wp_formidable_rss_parser_header"><?php echo esc_textarea( $value ) ?></textarea>
			</p>
			<?php
		}
	}
}
