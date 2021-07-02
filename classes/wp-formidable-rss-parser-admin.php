<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use FeedIo\Parser\XmlParser as Parser;

if ( ! class_exists( 'FormidableRSSParserAdmin' ) ) {

	class FormidableRSSParserAdmin {
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'create_setting_page' ) );
			add_action( 'admin_init', array( $this, 'settings_init' ) );
			add_action( "wp_ajax_nopriv_formidable_rss_parser", array( $this, "formidable_rss_parser_callback" ) );
			add_action( "wp_ajax_formidable_rss_parse", array( $this, "formidable_rss_parser_callback" ) );
			add_action( "wp_ajax_nopriv_formidable_rss_parser_import", array( $this, "formidable_rss_parser_import_callback" ) );
			add_action( "wp_ajax_formidable_rss_parser_import", array( $this, "formidable_rss_parser_import_callback" ) );
		}

		public function formidable_rss_parser_import_callback() {
			try {
				if ( ! ( is_array( $_POST ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
					die();
				}
				if ( ! isset( $_POST['action'] ) || ! isset( $_POST['nonce'] ) || empty( $_POST['data'] ) || empty( $_POST['selection'] ) ) {
					die();
				}
				if ( ! wp_verify_nonce( $_POST['nonce'], FormidableRSSParser::get_slug() . __DIR__ ) ) {
					die();
				}

				$selections = array_map( 'intval', $_POST['selection'] );
				$data       = json_decode( $_POST['data'] );


			} catch ( Exception $ex ) {
				FormidableRSSParser::error_log( $ex->getMessage() );
				wp_send_json_error( array() );
			}
			die();
		}

		public function get_xml_element( $el ) {
			foreach ( $el->getNamespaces( true ) as $prefix => $ns ) {
				$children = $el->children( $ns );
				foreach ( $children as $tag => $content ) {
					$el->{$prefix . ':' . $tag} = $content;
				}
			}
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

				$url = sanitize_text_field( $_POST['url'] );

				$xml = $this->get_xml_content( $url );
				$channel = $xml->channel;
				$itunes = $channel->children('http://www.itunes.com/dtds/podcast-1.0.dtd');
				$category = $itunes->category;
				$categories = array();
				foreach ( $category->children() as $item ) {
					$categories[] = $item->getName();
				}
				$this->get_xml_element( $channel );
//$feed_response = FormidableRSSFeed::loadRss( $url );
				$result = array( 'count' => 1 );

				if ( ! empty( $feed_response ) ) {
//					$result['shows'] = $feed_response->toArray();
					wp_send_json_success( $result );
				}
			} catch ( Exception $ex ) {
				FormidableRSSParser::error_log( $ex->getMessage() );
				wp_send_json_error( array() );
			}
			die();
		}

		public function xml2obj( $xml, $force = false ) {

			$obj = new StdClass();

			$obj->name = $xml->getName();

			$text       = trim( (string) $xml );
			$attributes = array();
			$children   = array();

			foreach ( $xml->attributes() as $k => $v ) {
				$attributes[ $k ] = (string) $v;
			}

			foreach ( $xml->children() as $k => $v ) {
				$children[] = $this->xml2obj( $v, $force );
			}


			if ( $force or $text !== '' ) {
				$obj->text = $text;
			}

			if ( $force or count( $attributes ) > 0 ) {
				$obj->attributes = $attributes;
			}

			if ( $force or count( $children ) > 0 ) {
				$obj->children = $children;
			}


			return $obj;
		}

		public function get_xml_content( $url ) {
			$fileContents = file_get_contents( $url );
//			$fileContents = str_replace( array( "\n", "\r", "\t" ), '', $fileContents );

//			$fileContents = trim( str_replace( '"', "'", $fileContents ) );
//			$fileContents = preg_replace( '/([a-z]{1})(:)([a-z]{1})/', '$1_$3', $fileContents );

			return simplexml_load_string( $fileContents, 'SimpleXMLElement', LIBXML_PARSEHUGE | LIBXML_NOEMPTYTAG | LIBXML_BIGLINES );
		}

		public function parse_feed( $url ) {
			$simpleXml = $this->get_xml_content( $url );
			$json      = json_encode( $simpleXml );

			return $json;
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

		public static function include_assets() {
			wp_enqueue_script( 'formidableRSSParserLodash', FormidableRSSParser::assets_path( 'lodash' ), array( 'jquery' ), FormidableRSSParser::get_version(), true );
			wp_enqueue_script( 'formidableRSSParser', FormidableRSSParser::assets_path( 'script' ), array( 'jquery', 'formidableRSSParserLodash' ), FormidableRSSParser::get_version(), true );
			wp_enqueue_style( 'formidableRSSParser', FormidableRSSParser::assets_path( 'style', 'css' ), array(), FormidableRSSParser::get_version() );
			$args = array(
					'admin_url' => admin_url( 'admin-ajax.php' ),
					'nonce'     => wp_create_nonce( FormidableRSSParser::get_slug() . __DIR__ ),
			);
			wp_localize_script( 'formidableRSSParser', 'formidableRSSParserObj', $args );
		}
	}
}
