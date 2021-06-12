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

	public function __construct() {
		$this->load_plugin_textdomain();
		include_once 'vendor/autoload.php';

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

	public static function assets_path( $name, $extension = 'js' ): string {
		$url    = plugin_dir_url( __FILE__ ) . 'assets/';
		$url    .= ( $extension == 'js' ) ? 'js/' : 'css/';
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		return $url . $name . $suffix . '.' . $extension;
	}
}

add_action( 'plugins_loaded', function () {
	FormidableRSSParser::get_instance();
}, 999 );

