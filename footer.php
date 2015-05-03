			</div><!-- END ROW -->
		</div><!-- END CONTAINER-FLUID -->
	<div style="display:none;">
		<?php wp_editor( '', 'temp_id', editor_settings() ); ?>
	</div>

    <!-- MODAL LOGIN / REGISTER -->
    <?php forgivingheals_login_register_modal() ?>
	<!-- MODAL LOGIN / REGISTER -->

	<!-- MODAL RESET PASSWORD -->
    <?php forgivingheals_reset_password_modal() ?>
	<!-- MODAL RESET PASSWORD -->

    <!-- MODAL EDIT PROFILE / CHANGE PASS -->
	<?php
		forgivingheals_edit_profile_modal();
	?>
	<!-- MODAL EDIT PROFILE / CHANGE PASS -->

	<!-- MODAL INSERT NEW STORY -->
	<?php forgivingheals_insert_story_modal() ?>
	<!-- MODAL INSERT NEW STORY -->

	<!-- MODAL UPLOAD IMAGE -->
	<?php get_template_part( 'template/modal', 'upload-images' ); ?>
	<!-- MODAL UPLOAD IMAGE -->

	<!-- TAG TEMPLATE -->
	<?php forgivingheals_tag_template() ?>
	<!-- TAG TEMPLATE -->
	<!-- MODAL REPORT -->
    <?php forgivingheals_report_modal() ?>
	<!-- END MODAL REPORT -->
	<!-- CONTACT REPORT -->
    <?php forgivingheals_contact_modal() ?>
	<!-- END CONTACT REPORT -->
	<?php
		if( is_singular( 'story' ) || is_singular( 'reaction' ) ){
			forgivingheals_reaction_template();
			forgivingheals_comment_template();
		}
	?>
	<!-- SEARCH PREVIEW TEMPLATE -->
	<?php get_template_part( 'template-js/search', 'preview' ); ?>
	<!-- SEARCH PREVIEW TEMPLATE -->
	<?php wp_footer(); ?>
	</body><!-- END BODY -->
</html>
