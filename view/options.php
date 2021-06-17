<tr class="frm_options_heading">
	<td colspan="2">
		<div class="menu-settings">
			<h3 class="frm_no_bg"><?php _e( 'RSS Field', 'formidable-rss-parser' ); ?></h3>
		</div>
	</td>
</tr>
<tr>
	<td>
		<label for="field_options[formidable_rss_option_<?php echo esc_attr( $field['id'] ) ?>]"><?php _e( "Option 1", 'formidable-rss-parser' ); ?></label>
	</td>
	<td>
		<input type="checkbox" <?php echo esc_attr( $show_filter_group ) ?> name="field_options[formidable_rss_option_<?php echo esc_attr( $field['id'] ) ?>]" id="field_options[formidable_rss_option_<?php echo esc_attr( $field['id'] ) ?>]" value="1"/>
	</td>
</tr>
