<?php
	global $forgivingheals_reaction, $forgivingheals_story, $forgivingheals_reaction_comments, $current_user ;

	$story	=	$forgivingheals_story;

    get_template_part( 'template/item', 'vote' );
?>

<div class="col-md-9 col-xs-9 q-right-content">
	<!-- control tool for admin, moderate -->
    <ul class="post-controls">
        <?php
        //reaction status is pending & current user is admin
        if( $forgivingheals_reaction->post_status == "pending" && ( current_user_can( 'manage_options' ) || forgivingheals_user_can('approve_reaction') )  ) {
        ?>
        <li>
            <a href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php _e("Approve", ET_DOMAIN) ?>" data-name="approve" class="post-edit action">
                <i class="fa fa-check"></i>
            </a>
        </li>
        <?php
        }
        // user can control option or have forgivingheals cap edit story/reaction
        if($current_user->ID == $forgivingheals_reaction->post_author || current_user_can( 'manage_options' ) || forgivingheals_user_can('edit_reaction')) {
        ?>
        <li>
            <a href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php _e("Edit", ET_DOMAIN) ?>" data-name="edit" class="post-edit action">
                <i class="fa fa-pencil"></i>
            </a>
        </li>
        <?php } ?>
        <?php if( $current_user->ID == $forgivingheals_reaction->post_author || current_user_can( 'manage_options' ) ){ ?>
        <li>
            <a href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php _e("Delete", ET_DOMAIN) ?>" data-name="delete" class="post-delete action" >
                <i class="fa fa-trash-o"></i>
            </a>
        </li>
        <?php } ?>
         <?php if(is_user_logged_in() && !$forgivingheals_reaction->reported){ ?>
        <li>
         <a href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php _e("Report", ET_DOMAIN) ?>" data-name="report" class="action report" >
              <i class="fa fa-exclamation-triangle"></i>
        </a>
        </li>
         <?php } ?>
    </ul>
    <!--// control tool for admin, moderate -->
    <div class="top-content">
        <?php if($forgivingheals_story->et_best_reaction == $forgivingheals_reaction->ID){ ?>
        <span class="reacted best-reaction">
            <i class="fa fa-check"></i> <?php _e("Best reaction", ET_DOMAIN) ?>
        </span>
        <?php } ?>
        <?php if($forgivingheals_reaction->post_status == "pending"){ ?>
        <span class="reacted best-reaction">
            <?php _e("Pending", ET_DOMAIN) ?>
        </span>
        <?php } ?>
    </div>
    <div class="clearfix"></div>

    <div class="story-content">
        <?php the_content(); ?>
    </div>

    <div class="post-content-edit collapse">
        <form class="edit-post">
            <input type="hidden" name="forgivingheals_nonce" value="<?php echo wp_create_nonce( 'edit_reaction' );?>" />
            <div class="wp-editor-container">
                <textarea name="post_content" id="edit_post_<?php echo $forgivingheals_reaction->ID ?>"></textarea>
            </div>
            <div class="row submit-wrapper">
                <div class="col-md-2 col-xs-2">
                    <button id="submit_reply" class="btn-submit"><?php _e("Update",ET_DOMAIN) ?></button>
                </div>
                <div class="col-md-2 col-xs-2">
                    <a href="javascript:void(0);" data-name="cancel-post-edit" class="action cancel-edit-post">
                        <?php _e("Cancel",ET_DOMAIN) ?>
                    </a>
                </div>
            </div>
        </form>
    </div><!-- END EDIT POST FORM -->

    <div class="row cat-infomation">
    	<!-- Reaction owner infomation -->
        <div class="col-md-8 col-xs-8 story-cat">
            <a href="<?php echo get_author_posts_url($forgivingheals_reaction->post_author); ?>">
                <span class="author-avatar">
                    <?php echo et_get_avatar( $forgivingheals_reaction->post_author , 30 ); ?>
                </span>
                <span class="author-name"><?php echo $forgivingheals_reaction->author_name; ?></span>
            </a>
                <?php  forgivingheals_user_badge( $forgivingheals_reaction->post_author ); ?>
                <span class="story-time">
                    <?php printf( __( 'Answered %s.', ET_DOMAIN ), et_the_time(strtotime($forgivingheals_reaction->post_date))); ?>
                </span>
        </div>
		<!--// Reaction owner infomation -->

        <div class="col-md-4 col-xs-4 story-control">
        	<!-- share comment , report -->
            <ul>
                <li>
                    <a class="share-social" href="javascript:void(0);" data-toggle="popover" data-placement="top" data-container="body" data-content='<?php echo forgivingheals_template_share($forgivingheals_reaction->ID); ?>' data-html="true">
                        <?php _e("Share",ET_DOMAIN) ?> <i class="fa fa-share"></i>
                    </a>
                </li>
                <!-- <li>
                    <a href="javascript:void(0)">
                        <?php _e("Report",ET_DOMAIN) ?> <i class="fa fa-flag"></i>
                    </a>
                </li> -->
                <!-- comment count -->
                <li>
                    <a href="#container_<?php echo $forgivingheals_reaction->ID ?>" class="show-comments <?php if(count($forgivingheals_reaction_comments)>0) echo 'active';?>">
                        <?php
                        	printf( __( 'Comment(%d) ', ET_DOMAIN ), count($forgivingheals_reaction_comments));
                        ?> <i class="fa fa-comment"></i>
                    </a>
                </li>
            </ul>
        </div>
        <!--// share comment , report -->
    </div>
    <div class="clearfix"></div>
    <div class="comments-container <?php if(count($forgivingheals_reaction_comments)==0) echo 'collapse';?>" id="container_<?php echo $forgivingheals_reaction->ID ?>">
		<div class="comments-wrapper">
		<?php
			/**
			 * render comment loop
			*/
            if(!empty($forgivingheals_reaction_comments)){
				foreach ($forgivingheals_reaction_comments as $comment) {
					forgivingheals_comments_loop( $comment );
				}
			}
		 ?>
		</div>

        <?php forgivingheals_comment_form( $forgivingheals_reaction, 'reaction' ); ?>

    </div>
</div>