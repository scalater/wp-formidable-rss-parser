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
<!--		TODO: style.css seems to be missed -->
		<ul class="rss-parser-option-tips">
			<li>
				<?php _e( "Use \"show_relation\" to refer the owner", 'formidable-rss-parser' ); ?>
			</li>
			<li>
				<?php _e( "Use \".\" in child options, ie: item.title", 'formidable-rss-parser' ); ?>
			</li>
			<li>
				prefix:tag ??????
			</li>
		</ul>
	</td>
	<td>
		<input type="text" name="field_options[rss_parser_match_<?php echo esc_attr( $field['id'] ) ?>]" id="field_options[rss_parser_match_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $value ) ?>"/>
	</td>
</tr>
<tr>
	<td>
		<input type="checkbox" name="field_options[rss_parser_rss_key_<?php echo esc_attr( $field['id'] ) ?>]" id="field_options[rss_parser_rss_key_<?php echo esc_attr( $field['id'] ) ?>]" <?= $is_rss_key ? 'checked="checked"' : '' ?>/>
		<label for="field_options[rss_parser_rss_key_<?php echo esc_attr( $field['id'] ) ?>]"><?php _e( "Is RSS unique value", 'formidable-rss-parser' ); ?></label>
	</td>
</tr>
