<?php
/**
 * Plugin Name: Formidable RSS Parser
 * Plugin URI: https://castocity.com/
 * Description: WP plugin to Parse RSS feed into Formidable Fields.
 * Version: 1.0.0
 * Author: Scalater Team
 * Author URI: https://scalater.com/
 * License: GPLv2 or later
 * Network: false
 * Text Domain: formidable-rss-parser
 * Domain Path: /languages
 *
 *****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ****************************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FormidableRSSParser {
	public static $version = '1.0.0';
	public static $slug = 'formidable-rss-parser';
	private static $instance;
	private static $view;

	public function __construct() {
		$this->load_plugin_textdomain();
		self::$view = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
		include_once 'vendor/autoload.php';
		include_once 'classes/wp-formidable-rss-feed.php';
		include_once 'classes/wp-formidable-rss-parse-save.php';
		include_once 'classes/wp-formidable-rss-parser-admin.php';
		new FormidableRSSParserAdmin();
//		include_once 'classes/wp-formidable-rss-parser-field-base.php';
//		include_once 'classes/wp-formidable-rss-parser-field.php';
//		new FormidableRSSParserField();
		include_once 'classes/wp-formidable-rss-parser-options.php';
		new FormidableRSSParserFieldOptions();
		include 'classes/wp-formidable-rss-shortcode.php';
		new FormidableRSSShortCode();
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	static function get_version(): string {
		return self::$version;
	}

	/**
	 * Get plugins slug
	 *
	 * @return string
	 */
	static function get_slug(): string {
		return self::$slug;
	}

	/**
	 * @param string $message
	 */
	public static function error_log( string $message ) {
		if ( ! empty( $message ) ) {
			error_log( self::get_slug() . ' -- ' . $message );
		}
	}

	/**
	 * Load the textdomain for the plugin
	 *
	 * @since 1.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'formidable-rss-parser', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Generate a random string with the length given
	 * @param int $length
	 *
	 * @return string
	 */
	public static function random_string( $length = 10 ): string {
		$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		$randomString     = '';

		for ( $i = 0; $i < $length; $i ++ ) {
			$randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
		}

		return $randomString;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return FormidableRSSParser A single instance of this class.
	 */
	public static function get_instance(): FormidableRSSParser {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function assets_path( $name, $extension = 'js'): string {
		$url    = plugin_dir_url( __FILE__ ) . 'assets/';
		$url    .= ( $extension == 'js' ) ? 'js/' : 'css/';

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		return $url . $name . $suffix . '.' . $extension;
	}

	/**
	 * @return string
	 */
	public static function get_view(): string {
		return self::$view;
	}
}

add_action( 'plugins_loaded', function () {
	FormidableRSSParser::get_instance();
}, 999 );

