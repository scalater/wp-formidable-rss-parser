<div class="formidable-rss-parser-container-shortcode">
	<label class="input-url">
		<?php if ( ! empty( $label ) ): ?><p class="title"><?php echo esc_attr( $label ); ?></p><?php endif; ?>
		<div class="search-container">
			<div class="input-outer">
				<input type="url" class="formidable-rss-parser" <?php echo esc_attr( $form_id_show ) ?> <?php echo esc_attr( $type ) ?> <?php echo esc_attr( $form_id_episode ) ?> id="field_<?php echo esc_attr( $html_id ) ?>" value="https://thenewsworthy.libsyn.com/rss">
				<div class="clear-input">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M13.41,12l6.3-6.29a1,1,0,1,0-1.42-1.42L12,10.59,5.71,4.29A1,1,0,0,0,4.29,5.71L10.59,12l-6.3,6.29a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0L12,13.41l6.29,6.3a1,1,0,0,0,1.42,0,1,1,0,0,0,0-1.42Z"/></svg>
				</div>
			</div>
			<div class="input-error"></div>
			<button class="search-show"><?php _e('Search', 'formidable-rss-parser') ?></button>
		</div>
	</label>

	<div class="formidable-rss-result-show"></div>

	<div class="formidable-rss-result-episodes-container">
		<div class="formidable-rss-result-episodes">
			<div class="episode-image">
				<img src="https://ssl-static.libsyn.com/p/assets/c/f/f/7/cff791c11c1462d4/tnw-artwork-3000x3000.jpg" alt="Lorem Ipsum Dolor">
			</div>
			<div class="episodes-list"></div>
		</div>
		<button class="import-episodes"><?php _e('Import', 'formidable-rss-parser') ?></button>
	</div>
</div>
