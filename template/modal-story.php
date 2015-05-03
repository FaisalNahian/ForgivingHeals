<?php
$privi  =   forgivingheals_get_privileges();
?>
<!-- MODAL SUBMIT STORIES -->
<div class="modal fade modal-submit-stories" id="modal_submit_stories" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					<i class="fa fa-times"></i>
				</button>
				<h4 class="modal-title" id="myModalLabel"><?php _e('Share a Story',ET_DOMAIN) ?></h4>
			</div>
			<div class="modal-body">
				<form id="submit_story">
					<input type="hidden" id="forgivingheals_nonce" name="forgivingheals_nonce" value="<?php echo wp_create_nonce( 'insert_story' ); ?>">
					<?php do_action( 'before_insert_story_form' ); ?>
					<input type="text" class="submit-input" id="story_title" name="post_title" placeholder="<?php _e('Your Story',ET_DOMAIN) ?>" />
					<?php forgivingheals_select_categories() ?>
					<div class="wp-editor-container">
						<textarea name="post_content" id="insert_story"></textarea>
					</div>

					<div id="story-tags-container">
						<input data-provide="typeahead" type="text" class="submit-input tags-input" id="story_tags" name="story_tags" placeholder="<?php _e('Tag(max 5 tags)',ET_DOMAIN) ?>" />
						<span class="tip-add-tag"><?php _e("Press enter to add new tag", ET_DOMAIN) ?></span>
						<ul class="tags-list" id="tag_list"></ul>
					</div>

					<input id="add_tag_text" type="hidden" value="<?php printf(__("You must have %d points to add tag. Current, you have to select existed tags.", ET_DOMAIN), $privi->create_tag  ); ?>" />

					<?php do_action( 'after_insert_story_form' ); ?>
					<button id="btn_submit_story" class="btn-submit-story"><?php _e('SUBMIT STORY',ET_DOMAIN) ?></button>
					<p class="term-texts">
						<?php forgivingheals_tos("story"); ?>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- MODAL SUBMIT STORIES -->		
