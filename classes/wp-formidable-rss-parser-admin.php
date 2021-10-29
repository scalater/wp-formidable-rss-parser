<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'FormidableRSSParserAdmin' ) ) {

	class FormidableRSSParserAdmin {
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'create_setting_page' ) );
			add_action( 'admin_init', array( $this, 'settings_init' ) );
			add_action( "wp_ajax_nopriv_formidable_rss_parser", array( $this, "formidable_rss_parser_callback" ) );
			add_action( "wp_ajax_formidable_rss_parse", array( $this, "formidable_rss_parser_callback" ) );
			add_action( "wp_ajax_nopriv_formidable_rss_parser_import", array( $this, "formidable_rss_parser_import_callback" ) );
			add_action( "wp_ajax_formidable_rss_parser_import", array( $this, "formidable_rss_parser_import_callback" ) );
			add_action( 'wp_ajax_get_podchase_token', array($this, 'formidable_rss_parser_podchase_token_callback'));
		}

		public function formidable_rss_parser_podchase_token_callback() {
			$podchase_endpoint = get_option( 'wp_formidable_rss_parser_podchase_endpoint' );
			$podchase_token = get_option( 'wp_formidable_rss_parser_podchase_token' );
			$podchase_token_expires_in = get_option( 'wp_formidable_rss_parser_podchase_token_expires_in' );

			if($podchase_endpoint) {
				if (!$podchase_token || $podchase_token_expires_in <= time()) {
					$podchase_client_key = get_option('wp_formidable_rss_parser_podchase_client_key');
					$podchase_secret_key = get_option('wp_formidable_rss_parser_podchase_secret_key');

					$query = "mutation {
						requestAccessToken(
							input: {
								grant_type: CLIENT_CREDENTIALS
								client_id: \"".$podchase_client_key."\"
								client_secret: \"".$podchase_secret_key."\"
							}
						) {
							access_token
							token_type
							expires_in
						}
					}";

					$data = array ('query' => $query);
					$data = http_build_query($data);

					$options = array(
							'http' => array(
									'method'  => 'POST',
									'content' => $data
							)
					);

					$context  = stream_context_create($options);
					$result = file_get_contents($podchase_endpoint, false, $context);

					if ($result === FALSE) {
						print('');
					}else{
						$json = json_decode($result);
						$podchase_token = $json->data->requestAccessToken->access_token;
						$podchase_token_expires_in = $json->data->requestAccessToken->expires_in;

						update_option('wp_formidable_rss_parser_podchase_token', $podchase_token);
						update_option('wp_formidable_rss_parser_podchase_token_expires_in', time() + $podchase_token_expires_in);
					}
				}
			}

			wp_send_json_success(['endpoint' => $podchase_endpoint, 'token' => $podchase_token]);
			die();
		}

		public function formidable_rss_parser_import_callback() {
			try {
				if ( ! ( is_array( $_POST ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
					die();
				}
				if ( ! isset( $_POST['action'] ) || ! isset( $_POST['nonce'] ) || empty( $_POST['target_form_id_episode'] )
						|| empty( $_POST['selection'] ) || empty( $_POST['url'] ) || empty( $_POST['target_form_id_show'] )
				) {
					die();
				}
				if ( ! wp_verify_nonce( $_POST['nonce'], FormidableRSSParser::get_slug() . __DIR__ ) ) {
					die();
				}

				$target_form_id_show    = intval( $_POST['target_form_id_show'] );
				$target_form_id_episode = intval( $_POST['target_form_id_episode'] );
				$selections             = array_map( 'intval', $_POST['selection'] );

				$url           = sanitize_text_field( $_POST['url'] );
				$feed_response = FormidableRSSFeed::load_rss( $url );

				$result = false;
				if ( ! empty( $feed_response ) ) {
					$channel      = $feed_response->get_channel();
					$rss_to_parse = FormidableRSSFeed::channel_for_parce( $channel, true, true );

					$episodes = array();
					foreach ( $selections as $selection ) {
						if ( isset( $channel->item[ $selection ] ) ) {
							$episodes[] = $channel->item[ $selection ];
						}
					}

					$save         = new FormidableRSSSave( $target_form_id_show, $target_form_id_episode, $rss_to_parse );
					$show_mapping = $save->get_fields_mapping();
					$show_id      = 0;
					if ( ! empty( $show_mapping ) ) {
						$show_metas = $save->get_mapped_metas( $show_mapping );
						if ( ! empty( $show_metas ) ) {
							$show_id = $save->insert_into_form( $target_form_id_show, $show_metas );
						}
					}
					$episodes_mapping = $save->get_fields_mapping( 'target_form_id_episodes' );
					if ( ! empty( $episodes_mapping ) ) {
						if ( ! empty( $episodes ) ) {
							foreach ( $episodes as $episode ) {
								$episode       = FormidableRSSFeed::channel_for_parce( $episode, true, true );
								$episode_metas = $save->get_mapped_metas( $episodes_mapping, $episode, $show_id );
								if ( ! empty( $episode_metas ) ) {
									$result[] = $save->insert_into_form( $target_form_id_episode, $episode_metas );
								}
							}
						}
					}

				}

				if ( ! empty( $result ) && ! empty( $show_id ) ) {
					wp_send_json_success( true );
				} else {
					wp_send_json_error();
				}

			} catch ( Exception $ex ) {
				FormidableRSSParser::error_log( $ex->getMessage() );
				wp_send_json_error( array() );
			}
			die();
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
				$feed_response = FormidableRSSFeed::load_rss( $url );

				$result = array( 'count' => 1 );

				if ( ! empty( $feed_response ) ) {
					$result['rss'] = $feed_response->get_channel();
					wp_send_json_success( $result );
				}
			} catch ( Exception $ex ) {
				FormidableRSSParser::error_log( $ex->getMessage() );
				wp_send_json_error( $ex->getMessage(), '403' );
			}
			die();
		}

		public function get_xml_content( $url ) {
			$file_get_contents = file_get_contents( $url );
			$file_get_contents = str_replace( array( "\n", "\r", "\t" ), '', $file_get_contents );

			$file_get_contents = trim( str_replace( '"', "'", $file_get_contents ) );
			$file_get_contents = preg_replace( '/([a-z]{1})(:)([a-z]{1})/', '$1_$3', $file_get_contents );

			$simple_xml = simplexml_load_string( $file_get_contents, 'SimpleXMLElement', LIBXML_PARSEHUGE | LIBXML_NOEMPTYTAG | LIBXML_BIGLINES | LIBXML_NOCDATA | LIBXML_NOEMPTYTAG );

			if ( libxml_get_errors() ) {
				FormidableRSSParser::error_log( implode( ',', libxml_get_errors() ) );
			}

			return $simple_xml;
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

			add_settings_field( 'wp_formidable_rss_parser_podchase_endpoint', __( 'Podchaser Endpoint', 'formidable-rss-parser' ), array( $this, 'wp_formidable_rss_parser_podchase_endpoint_cb' ), 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_section' );
			add_settings_field( 'wp_formidable_rss_parser_podchase_client_key', __( 'Podchaser Client Key', 'formidable-rss-parser' ), array( $this, 'wp_formidable_rss_parser_podchase_client_key_cb' ), 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_section' );
			add_settings_field( 'wp_formidable_rss_parser_podchase_secret_key', __( 'Podchaser Secret Key', 'formidable-rss-parser' ), array( $this, 'wp_formidable_rss_parser_podchase_secret_key_cb' ), 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_section' );

			register_setting( 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_podchase_endpoint' );
			register_setting( 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_podchase_client_key' );
			register_setting( 'wp_formidable_rss_parser_option', 'wp_formidable_rss_parser_podchase_secret_key' );
		}

		public function wp_formidable_rss_parser_podchase_endpoint_cb() {
			$value = get_option( 'wp_formidable_rss_parser_podchase_endpoint' );
			?>
			<p>
				<input type="text" name="wp_formidable_rss_parser_podchase_endpoint" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>" style="width: 350px;">
			</p>
			<?php
		}

		public function wp_formidable_rss_parser_podchase_client_key_cb() {
			$value = get_option( 'wp_formidable_rss_parser_podchase_client_key' );
			?>
			<p>
				<input type="text" name="wp_formidable_rss_parser_podchase_client_key" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>" style="width: 350px;">
			</p>
			<?php
		}

		public function wp_formidable_rss_parser_podchase_secret_key_cb() {
			$value = get_option( 'wp_formidable_rss_parser_podchase_secret_key' );
			?>
			<p>
				<input type="text" name="wp_formidable_rss_parser_podchase_secret_key" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>" style="width: 350px;">
			</p>
			<?php
		}

		public static function include_assets() {
			wp_enqueue_script( 'formidableRSSParserLodash', FormidableRSSParser::assets_path( 'lodash' ), array( 'jquery' ), FormidableRSSParser::get_version(), true );
			wp_enqueue_script( 'formidableRSSParser', FormidableRSSParser::assets_path( 'script' ), array( 'jquery', 'formidableRSSParserLodash' ), FormidableRSSParser::get_version(), true );
			wp_enqueue_script( 'formidableRSSAutoComplete', FormidableRSSParser::assets_path( 'podchase_autocomplete'), [], false, true);
			wp_enqueue_style( 'formidableRSSParser', FormidableRSSParser::assets_path( 'style', 'css' ), array(), FormidableRSSParser::get_version() );
			$args = array(
					'admin_url' => admin_url( 'admin-ajax.php' ),
					'nonce'     => wp_create_nonce( FormidableRSSParser::get_slug() . __DIR__ ),
			);
			wp_localize_script( 'formidableRSSParser', 'formidableRSSParserObj', $args );
		}
	}
}
