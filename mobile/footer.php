	<?php
		if(is_singular( 'story' )){
			forgivingheals_mobile_reaction_template();
			forgivingheals_mobile_comment_template();
		}
		forgivingheals_tag_template();
		echo '<!-- GOOGLE ANALYTICS CODE -->';
        $google = ae_get_option('google_analytics');
        $google = implode("",explode("\\",$google));
        echo stripslashes(trim($google));
		echo '<!-- END GOOGLE ANALYTICS CODE -->';
	?>
    <?php wp_footer(); ?>
	</body>
</html>