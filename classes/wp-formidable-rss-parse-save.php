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
				$option = FrmField::get_option( $field, 'rss_parser_match' );
				if ( ! empty( $option ) ) {
					if ( $option !== 'show_relation' ) {
						$mapping[ $field->id ] = $this->option_to_array( $option );
					} else {
						$mapping[ $field->id ] = 'show_relation';
					}
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

		public function get_episode_metas( $mapping, $selections ) {
			if ( ! isset( $this->channel ) ) {
				throw new Exception( 'Missing form channel data' );
			}
			$result = array();
			foreach ( $selections as $selection ) {
				$result[] = $this->get_mapped_metas( $mapping, $selection );
			}

			return $result;
		}

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
			foreach ( $map_options as $field_id => $options ) {
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
												$result[ $field_id ] = $attribute_val;
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
															$result[ $field_id ] = $attribute_val;
															$option_founded      = true;
															break 3;
														}
													}
												}
												$result[ $field_id ] = $sub_child->text;
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
							$result[ $field_id ] = $show_id;
						} else {
							if ( $child->name === $options ) {
								if ( ! empty( $child->attributes ) ) {
									foreach ( $child->attributes as $attribute_tag => $attribute_val ) {
										if ( $attribute_tag === $options ) {
											$result[ $field_id ] = $attribute_val;
											break;
										}
									}
								}
								$result[ $field_id ] = $child->text;
								break;
							}
						}
					}
				}
			}

			return $result;
		}

		public function insert_into_form( $form_id, $metas ) {
			$data = array(
				'form_id'                      => $form_id,//update the form id
				'frm_user_id'                  => get_current_user_id(),
				'frm_submit_entry_' . $form_id => wp_create_nonce( 'frm_submit_entry_nonce' ),//update the form id
				'item_meta'                    => $metas,
			);

			return FrmEntry::create( $data );
		}

	}
}
