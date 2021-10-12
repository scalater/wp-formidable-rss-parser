<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FormidableRSSFeed' ) ) {

	class FormidableRSSFeed {
		/** @var int */
		public static $cache_expire = '1 day';

		/** @var string */
		public static $cache_dir;

		/** @var string */
		public static $user_agent = 'FormidableRSSFeed-Google';

		/** @var SimpleXMLElement */
		protected $xml;


		/**
		 * @param string  RSS feed URL
		 * @param string  optional user name
		 * @param string  optional password
		 *
		 * @return FormidableRSSFeed
		 * @throws Exception
		 */
		public static function load_rss( $url, $user = null, $pass = null ) {
			return self::from_rss( self::load_xml( $url, $user, $pass ) );
		}

		public function get_channel() {
			if ( ! $this->xml ) {
				throw new FeedException( 'Invalid feed.' );
			}

			return $this->xml;
		}

		public static function channel_for_parce( $xml, $force = false, $include_items = false ) {

			$obj = new StdClass();

			$obj->name = $xml->getName();

			$text       = trim( (string) $xml );
			$attributes = array();
			$children   = array();

			foreach ( $xml->attributes() as $k => $v ) {
				$attributes[ $k ] = (string) $v;
			}

			foreach ( $xml->children() as $k => $v ) {
				if ( ! $include_items ) {
					if ( $k !== 'item' ) {
						$children[] = self::channel_for_parce( $v, $force );
					}
				} else {
					$children[] = self::channel_for_parce( $v, $force );
				}
			}


			if ( $force or ! empty( $text ) ) {
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


		private static function from_rss( SimpleXMLElement $xml ) {
			if ( ! $xml->channel ) {
				throw new FeedException( 'Invalid feed.' );
			}

			self::adjust_namespaces( $xml->channel );

			foreach ( $xml->channel->item as $item ) {
				// converts namespaces to dotted tags
				self::adjust_namespaces( $item );

				// generate 'url' & 'timestamp' tags
				$item->url = (string) $item->link;
				if ( isset( $item->{'dc:date'} ) ) {
					$item->timestamp = strtotime( $item->{'dc:date'} );
				} elseif ( isset( $item->pubDate ) ) {
					$item->timestamp = strtotime( $item->pubDate );
				}
			}
			$feed      = new self;
			$feed->xml = $xml->channel;

			return $feed;
		}

		/**
		 * @param string  tag name
		 *
		 * @return SimpleXMLElement
		 */
		public function __get( $name ) {
			return $this->xml->{$name};
		}


		/**
		 * @param string  property name
		 * @param mixed   property value
		 *
		 * @return void
		 */
		public function __set( $name, $value ) {
			throw new Exception( "Cannot assign to a read-only property '$name'." );
		}


		/**
		 * @param SimpleXMLElement
		 *
		 * @return array
		 */
		public function to_array( SimpleXMLElement $xml = null ) {
			if ( $xml === null ) {
				$xml = $this->xml;
			}

			if ( ! $xml->children() ) {
				return (string) $xml;
			}

			$arr = [];
			foreach ( $xml->children() as $tag => $child ) {
				if ( count( $xml->$tag ) === 1 ) {
					$arr[ $tag ] = $this->to_array( $child );
				} else {
					$arr[ $tag ][] = $this->to_array( $child );
				}
			}

			return $arr;
		}


		/**
		 * @param string
		 * @param string
		 * @param string
		 *
		 * @return SimpleXMLElement
		 * @throws Exception
		 */
		private static function load_xml( $url, $user, $pass ) {
			$e         = self::$cache_expire;
			$cacheFile = self::$cache_dir . '/feed.' . md5( serialize( func_get_args() ) ) . '.xml';

			if ( self::$cache_dir
			     && ( time() - @filemtime( $cacheFile ) <= ( is_string( $e ) ? strtotime( $e ) - time() : $e ) )
			     && $data = @file_get_contents( $cacheFile )
			) {
				// ok
			} elseif ( $data = trim( self::http_request( $url, $user, $pass ) ) ) {
				if ( self::$cache_dir ) {
					file_put_contents( $cacheFile, $data );
				}
			} elseif ( self::$cache_dir && $data = @file_get_contents( $cacheFile ) ) {
				// ok
			} else {
				throw new FeedException( 'Cannot load feed.' );
			}

			$data = str_replace( array( "\n", "\r", "\t" ), '', $data );

			$data = trim( str_replace( '"', "'", $data ) );
			$data = preg_replace( '/([a-z]{1})(:)([a-z]{1})/', '$1_$3', $data );

			return new SimpleXMLElement( $data, LIBXML_PARSEHUGE | LIBXML_NOEMPTYTAG | LIBXML_BIGLINES | LIBXML_NOCDATA | LIBXML_NOEMPTYTAG );
		}


		/**
		 * @param string
		 * @param string
		 * @param string
		 *
		 * @return string|false
		 * @throws Exception
		 */
		private static function http_request( $url, $user, $pass ) {
			if ( extension_loaded( 'curl' ) ) {
				$curl = curl_init();
				curl_setopt( $curl, CURLOPT_URL, $url );
				if ( $user !== null || $pass !== null ) {
					curl_setopt( $curl, CURLOPT_USERPWD, "$user:$pass" );
				}
				curl_setopt( $curl, CURLOPT_USERAGENT, self::$user_agent ); // some feeds require a user agent
				curl_setopt( $curl, CURLOPT_HEADER, false );
				curl_setopt( $curl, CURLOPT_TIMEOUT, 20 );
				curl_setopt( $curl, CURLOPT_ENCODING, '' );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); // no echo, just return result
				if ( ! ini_get( 'open_basedir' ) ) {
					curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true ); // sometime is useful :)
				}

				if(wp_get_environment_type() == 'local'){
					curl_setopt($curl, CURLOPT_SSL_OPTIONS, false);
				}

				$result = curl_exec( $curl );

				return curl_errno( $curl ) === 0 && curl_getinfo( $curl, CURLINFO_HTTP_CODE ) === 200
					? $result
					: false;

			} else {
				$context = null;
				if ( $user !== null && $pass !== null ) {
					$options = [
						'http' => [
							'method' => 'GET',
							'header' => 'Authorization: Basic ' . base64_encode( $user . ':' . $pass ) . "\r\n",
						],
					];
					$context = stream_context_create( $options );
				}

				return file_get_contents( $url, false, $context );
			}
		}


		/**
		 * @param SimpleXMLElement
		 *
		 * @return void
		 */
		private static function adjust_namespaces( $el ) {
			foreach ( $el->getNamespaces( true ) as $prefix => $ns ) {
				$children = $el->children( $ns );
				foreach ( $children as $tag => $content ) {
					$el->{$prefix . ':' . $tag} = $content;
				}
			}
		}

	}
}
