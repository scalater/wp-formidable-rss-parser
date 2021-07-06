<tr class="frm_options_heading">
	<td colspan="2">
		<div class="menu-settings">
			<h3 class="frm_no_bg"><?php _e( 'RSS Field Parser Option', 'formidable-rss-parser' ); ?></h3>
		</div>
	</td>
</tr>
<tr>
	<td>
		<label for="field_options[rss_parser_match_<?php echo esc_attr( $field['id'] ) ?>]"><?php _e( "Path to parse from RSS", 'formidable-rss-parser' ); ?></label>
	</td>
	<td>
		<input type="text" name="field_options[rss_parser_match_<?php echo esc_attr( $field['id'] ) ?>]" id="field_options[rss_parser_match_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $value ) ?>"/>
	</td>
</tr>
