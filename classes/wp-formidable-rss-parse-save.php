<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FormidableRSSSave' ) ) {

	class FormidableRSSSave {
		private $target_form_id_show;
		private $target_form_id_episodes;
		private $channel;

		public function __construct( $target_form_id_show, $target_form_id_episodes, $channel ) {
			$this->target_form_id_show     = $target_form_id_show;
			$this->target_form_id_episodes = $target_form_id_episodes;
			$this->channel                 = $channel;
		}

		public function get_fields_mapping( $target = 'target_form_id_show' ) {
			if ( ! isset( $this->$target ) ) {
				throw new Exception( 'Missing form id' );
			}

			$form_fields = FrmField::get_all_for_form( $this->$target );
			$mapping     = array();
			foreach ( $form_fields as $field ) {
				$rss_parser_match_option = FrmField::get_option( $field, 'rss_parser_match' );
				if ( ! empty( $rss_parser_match_option ) ) {
					if ( $rss_parser_match_option !== 'show_relation' ) {
						$mapping[ $field->id ]['map'] = $this->option_to_array( $rss_parser_match_option );
					} else {
						$mapping[ $field->id ]['map'] = 'show_relation';
					}
				}

				$rss_parser_rss_key_option = FrmField::get_option( $field, 'rss_parser_rss_key' );
				if ( ! empty( $rss_parser_rss_key_option ) ) {
					$mapping[ $field->id ]['rss_key'] = $this->option_to_array( $rss_parser_rss_key_option );
				}
			}

			return $mapping;
		}

		public function option_to_array( $option ) {
			if ( strpos( $option, '.' ) !== false ) {
				return explode( '.', $option );
			} else {
				return $option;
			}
		}

		//Not used function
//		public function get_episode_metas( $mapping, $selections ) {
//			if ( ! isset( $this->channel ) ) {
//				throw new Exception( 'Missing form channel data' );
//			}
//			$result = array();
//			foreach ( $selections as $selection ) {
//				$result[] = $this->get_mapped_metas( $mapping, $selection );
//			}
//
//			return $result;
//		}

		public function get_mapped_metas( $map_options, $xml = false, $show_id = false ) {
			if ( ! isset( $this->channel ) ) {
				throw new Exception( 'Missing form channel data' );
			}
			$result = array();
			if ( ! $xml ) {
				$map_children = $this->channel->children;
			} else {
				$map_children = $xml->children;
			}
			foreach ( $map_options as $field_id => $map_option ) {

				$options = $map_option['map'];

				if ( is_array( $options ) ) {
					foreach ( $options as $option_position => $option ) {
						$option_founded = false;
						if ( ( $option_position == 0 && ! $option_founded ) || ( $option_position > 0 && $option_founded ) ) {
							foreach ( $map_children as $child ) {
								if ( $child->name === $option ) {
									$option = $options[ $option_position + 1 ];
									if ( ! empty( $child->attributes ) ) {
										foreach ( $child->attributes as $attribute_tag => $attribute_val ) {
											if ( $attribute_tag === $option ) {
												$result[ $field_id ]['map'] = $attribute_val;
												$option_founded      = true;
												break 3;
											}
										}
									} elseif ( ! empty( $child->children ) ) {
										foreach ( $child->children as $sub_child ) {
											if ( $sub_child->name === $option ) {
												if ( ! empty( $sub_child->attributes ) ) {
													foreach ( $sub_child->attributes as $attribute_tag => $attribute_val ) {
														if ( $attribute_tag === $option ) {
															$result[ $field_id ]['map'] = $attribute_val;
															$option_founded      = true;
															break 3;
														}
													}
												}
												$result[ $field_id ]['map'] = $sub_child->text;
												$option_founded      = true;
												break 3;
											}
										}
									}
								}
							}
						}
					}
				} else {
					foreach ( $map_children as $child ) {
						if ( 'show_relation' == $options && ! empty( $show_id ) ) {
							$result[ $field_id ]['map'] = $show_id;
						} else {
							if ( $child->name === $options ) {
								if ( ! empty( $child->attributes ) ) {
									foreach ( $child->attributes as $attribute_tag => $attribute_val ) {
										if ( $attribute_tag === $options ) {
											$result[ $field_id ]['map'] = $attribute_val;
											break;
										}
									}
								}
								$result[ $field_id ]['map'] = $child->text;
								break;
							}
						}
					}
				}

				$result[$field_id]['rss_key'] = isset($map_option['rss_key']) && $map_option['rss_key'] == "on";
			}

			return $result;
		}

		public function insert_into_form( $form_id, $metas ) {

			$entry_id = 0;

			$item_meta = [];
			$unique_key_item_meta = [];

			foreach ($metas as $key => $data){
				$item_meta[$key] = $data['map'];

				if($data['rss_key']){
					$unique_key_item_meta[$key] = $data['map'];
				}
			}

			$user_id = get_current_user_id();

			$data = array(
				'form_id'                      	=> $form_id,//update the form id
				'frm_user_id'                  	=> $user_id,
				'frm_submit_entry_' . $form_id 	=> wp_create_nonce( 'frm_submit_entry_nonce' ),//update the form id
				'item_meta'                    	=> $item_meta,
			);

			$found = [];
			$user_entry_ids = [];

			$user_entries = FrmEntry::getAll([
				'form_id'	=> $form_id,
				'user_id'	=> $user_id,
			]);

			foreach ($user_entries as $user_entry){
				$user_entry_ids[] = $user_entry->id;
			}

			if(!empty($user_entry_ids)) {

				foreach ($unique_key_item_meta as $field_id => $meta_value) {

					$entries_meta_ids = FrmEntryMeta::search_entry_metas($meta_value, $field_id, '=');

					foreach ($entries_meta_ids as $entry_meta_id) {
						if (in_array($entry_meta_id, $user_entry_ids) &&
							!in_array($entry_meta_id, $found[$field_id])) {
							$found[$field_id][] = $entry_meta_id;
						}
					}
				}

				if (!empty($found)) {
					if (count($found) == 1) {
						$entry_id = reset($found)[0];
					} else {
						$intersection = [];
						$first_loop = true;
						foreach ($found as $candidates) {
							if ($first_loop) {
								$intersection = $candidates;
								$first_loop = true;
							} else {
								$intersection = array_intersect($intersection, $candidates);
							}
						}

						if (!empty($intersection)) {
							$entry_id = reset($intersection);
						}
					}
				}
			}

			if($entry_id == 0){
				$entry_id = FrmEntry::create( $data );

				foreach ($item_meta as $field_id => $field_value){
					FrmEntryMeta::add_entry_meta( $entry_id, $field_id, $field_id, $field_value );
				}
			}else{
				foreach ($item_meta as $field_id => $field_value){
					FrmEntryMeta::update_entry_meta( $entry_id, $field_id, $field_id, $field_value );
				}
			}

			return $entry_id;

		}

	}
}
