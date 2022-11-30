<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FormidableFixes' ) ) {

	class FormidableFixes {

		public static function apply() {

			//Fix color picker bad field_type structure
			//Origin: wp-content/plugins/formidable-pro-add-color-picker-field/formidable-color-picker.php:43
			if ( in_array( 'formidable-pro-add-color-picker-field/formidable-color-picker.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) &&
				has_filter('frm_pro_available_fields')) {
				add_filter('frm_pro_available_fields', 'color_picker_field_fixed', 11);

				function color_picker_field_fixed($fields){
					$fields['color_picker'] = array();
					$fields['color_picker']['name'] = __('Color Picker');
					return $fields;
				}
			}

		}




	}
}
