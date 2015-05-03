<?php
    global $forgivingheals_comment,$current_user;
 ?>

<div class="row comment-item" data-id="<?php echo $forgivingheals_comment->comment_ID ?>">
    <div class="col-md-2 comment-avatar">
        <?php echo $forgivingheals_comment->avatar; ?>
        <p class="cmt-author">
            <a href="<?php echo $forgivingheals_comment->author_url ?>"  title="<?php echo $forgivingheals_comment->author ?>">
                <?php echo $forgivingheals_comment->author ?>                
            </a>
        </p>
    </div>
    <div class="col-md-10 comment-content">
        <div class="cm-content-wrap">
            <div class="cm-wrap"><?php echo $forgivingheals_comment->content_filter ?></div>
            <span class="comment-time"><?php echo $forgivingheals_comment->human_date ?>. </span> <span class="comment-edit">
                <?php if( $forgivingheals_comment->user_id == $current_user->ID || current_user_can( 'manage_options' )){ ?>
                <a class="edit-comment" href="javascript:void(0)">
                    <?php _e("Edit",ET_DOMAIN) ?> <i class="fa fa-pencil"></i>
                </a>
                <a class="delete-comment" href="javascript:void(0)">
                    <?php _e("Delete",ET_DOMAIN) ?> <i class="fa fa-times"></i>
                </a>
                <?php } ?>
            </span>                            
        </div><!-- END COMMENT CONTENT -->
        <div class="cm-content-edit collapse">
            <form class="edit-comment">
                <input type="hidden" name="forgivingheals_nonce" value="<?php echo wp_create_nonce( 'edit_comment' );?>" />
                <div class="wp-editor-container">
                    <textarea name="comment_content" id="edit_comment_<?php echo $forgivingheals_comment->comment_ID ?>"></textarea>
                </div>
                <div class="row submit-wrapper">
                    <div class="col-md-3">
                        <button id="submit_reply" class="btn-submit"><?php _e("Update",ET_DOMAIN) ?></button>
                    </div>
                    <div class="col-md-3">
                        <a href="javascript:void(0)" class="cancel-comment"><?php _e("Cancel",ET_DOMAIN) ?></a>
                    </div>                                        
                </div>                                    
            </form>
        </div><!-- END EDIT COMMENT FORM -->                           
    </div>
</div>