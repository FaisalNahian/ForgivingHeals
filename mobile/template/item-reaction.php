<?php
    global $post,$current_user;
    $reaction          = ForgivingHeals_Reactions::convert($post);
    $story        = ForgivingHeals_Stories::convert(get_post($reaction->post_parent));
    $et_post_date    = et_the_time(strtotime($reaction->post_date));
    $badge_points    = forgivingheals_get_badge_point();
    $category        = !empty($story->story_category[0]) ? $story->story_category[0]->name : __('No Category',ET_DOMAIN);
    $category_link   = !empty($story->story_category[0]) ? get_term_link( $story->story_category[0]->term_id, 'story_category' ) : '#'; 

    $vote_up_class  =  'action vote vote-up ' ;
    $vote_up_class  .= ($reaction->voted_up) ? 'active' : '';
    $vote_up_class  .= ($reaction->voted_down) ? 'disabled' : ''; 

    $vote_down_class = 'action vote vote-down ';
    $vote_down_class .= ($reaction->voted_down) ? 'active' : '';
    $vote_down_class .= ($reaction->voted_up) ? 'disabled' : '';

    $forgivingheals_reaction_comments = get_comments( array( 
            'post_id'       => $reaction->ID,
            'parent'        => 0,
            'status'        => 'approve',
            'post_status'   => 'publish',
            'order'         => 'ASC',
            'type'          => 'reaction'
        ) );           
?>
<!-- CONTENT REACTIONS -->
<section class="list-reactions-wrapper reaction-item">
	<div class="container">
        <div class="row">
        	<div class="col-md-12">
            	<div class="content-qna-wrapper">
                    <!--<div class="avatar-user">
                        <a href="<?php echo get_author_posts_url( $story->post_author ); ?>">
                            <?php echo et_get_avatar($reaction->post_author, 55) ?>
                        </a>
                    </div>-->
                    <div class="info-user">
                        <?php forgivingheals_user_badge($reaction->post_author, true, true) ?>
                    </div>
                    <div class="content-story">
                        <?php if($reaction->post_status == "pending"){ ?>
                        <span class="pending-ans"><?php _e("Pending Reaction", ET_DOMAIN) ?></span>
                        <?php } ?>                        
                        <div class="details">
                        	<?php the_content(); ?>
                        </div>
                        <div class="info-tag-time">
                        	<span class="time-categories">
                                <?php 
                                    $author = '<a href="'.get_author_posts_url( $reaction->post_author ).'">'.$reaction->author_name.'</a>';
                                    printf(__("Answered by %s %s.", ET_DOMAIN), $author, $et_post_date)
                                ?>.
                            </span>
                        </div>
                        <div class="vote-wrapper">

                        	<a href="javascript:void(0)" data-name="vote_up" class="<?php echo $vote_up_class ?>">
                        		<i class="fa fa-angle-up"></i>
                        	</a>

                            <span class="number-vote"><?php echo $reaction->et_vote_count ?></span>

                            <a href="javascript:void(0)" data-name="vote_down" class="<?php echo $vote_down_class ?>">
                            	<i class="fa fa-angle-down"></i>
                            </a>
                            
                            <?php if($reaction->ID == $story->et_best_reaction) {?>
                            <a href="javascript:void(0)" data-name="un-accept-reaction" class="action reaction-active-label best-reactions">
                                <i class="fa fa-check"></i><?php _e("Best reaction", ET_DOMAIN) ?>
                            </a>
                            <?php } elseif($current_user->ID == $story->post_author) {?>
                            <a href="javascript:void(0)" data-name="accept-reaction" class="action reaction-active-label pending-reactions">
                                <?php _e("Accept", ET_DOMAIN) ?>
                            </a>
                            <?php } ?>

                            <?php if($reaction->post_status == "pending" && current_user_can( 'manage_options' )) {?>
                            <a href="javascript:void(0)" data-name="approve" class="action reaction-active-label pending-reactions">
                                <?php _e("Approve", ET_DOMAIN) ?>
                            </a>
                            <?php } ?>
                            
                        </div>
                    </div>
                </div>
                <!-- SHARE -->
                <div class="share">
                    <ul class="list-share">
                        <li>
                            <a class="share-social" href="javascript:void(0)" rel="popover" data-container="body" data-content='<?php echo forgivingheals_template_share($reaction->ID); ?>' data-html="true">
                                <?php _e("Share",ET_DOMAIN) ?> <i class="fa fa-share"></i>
                            </a>                            
                        </li>
                        <!-- <li>
                            <a href="javascript:void(0)"><?php _e("Report", ET_DOMAIN) ?><i class="fa fa-flag"></i></a>
                        </li> -->
                        <li>
                            <a href="javascript:void(0)" class="mb-show-comments"><?php _e("Comment", ET_DOMAIN) ?>(<?php echo count($forgivingheals_reaction_comments) ?>)&nbsp;<i class="fa fa-comment"></i></a>
                        </li>
                    </ul>
                </div>
                <!-- SHARE / END -->
                <!-- COMMENT IN COMMENT -->
                <div class="cmt-in-cmt-wrapper">
                	<ul class="mobile-comments-list">
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
                    </ul>
                    <?php forgivingheals_mobile_comment_form($reaction, 'reaction') ?>
                    <a href="javascript:void(0)" class="add-cmt-in-cmt"><?php _e("Add comment", ET_DOMAIN) ?></a>
                </div>
                <!-- COMMENT IN COMMENT / END -->
            </div>
        </div>
    </div>
</section>
<!-- CONTENT REACTIONS / END -->