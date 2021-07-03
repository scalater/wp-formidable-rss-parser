<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FormidableRSSSave' ) ) {

	class FormidableRSSSave {
		private $target_form_id_show;
		private $target_form_id_episodes;

		public function __construct( $target_form_id_show, $target_form_id_episodes ) {
			$this->target_form_id_show     = $target_form_id_show;
			$this->target_form_id_episodes = $target_form_id_episodes;
		}

		public function get_fields_mapping($target = 'target_form_id_show') {
			if ( ! isset( $this->$target )) {
				throw new Exception( 'Missing form id' );
			}

			$form_fields     = FrmField::get_all_for_form( $this->$target );
			$mapping         = array();
			foreach ( $form_fields as $field ) {
				$option = FrmField::get_option( $field, 'rss_parser_match' );
				if ( ! empty( $option ) ) {
					$mapping[ $field->id ] = $this->option_to_array($option);
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

		public function insert_into_form( $form_id, $metas, $target = 'target_form_id_show' ) {
			$mapping = $this->get_fields_mapping($target);

			$data = array(
				'form_id'                      => $form_id,//update the form id
				'frm_user_id'                  => get_current_user_id(),
				'frm_submit_entry_' . $form_id => wp_create_nonce( 'frm_submit_entry_nonce' ),//update the form id
				'item_meta'                    => $metas,
			);
			FrmEntry::create( $data );
		}

	}
}
