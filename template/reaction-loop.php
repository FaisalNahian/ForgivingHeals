<?php
    global $post;
    $reaction          = ForgivingHeals_Reactions::convert($post);
    $story        = ForgivingHeals_Stories::convert(get_post($reaction->post_parent));
    $et_post_date    = et_the_time(strtotime($reaction->post_date));
    $badge_points    = forgivingheals_get_badge_point();
    $category        = !empty($story->story_category[0]) ? $story->story_category[0]->name : __('No Category',ET_DOMAIN);
    $category_link   = !empty($story->story_category[0]) ? get_term_link( $story->story_category[0]->term_id, 'story_category' ) : '#';    
?>
<li <?php post_class( 'reaction-item story-item' ); ?> data-id="<?php echo $post->ID ?>">
    <div class="col-md-8 q-left-content">
        <div class="q-ltop-content title-reaction-style">
            <a href="<?php echo get_permalink($story->ID); ?>" class="story-title">
                <?php the_title() ?>
            </a>
        </div>
        <div class="q-lbtm-content">
            <div class="story-cat">
                <span class="author-avatar">
                <?php echo et_get_avatar( $reaction->post_author, 30 ); ?>
                </span>
                <?php  forgivingheals_user_badge( $reaction->post_author ); ?>
                <span class="story-time">
                    <?php printf( __( 'Submitted %s in', ET_DOMAIN ),$et_post_date); ?>
                </span>
                <span class="story-category">
                    <a href="<?php echo $category_link ?>"><?php echo $category ?>.</a>
                </span>
                <ul class="story-tags">
                    <?php
                        foreach ($story->forgivingheals_tag as $tag) {
                    ?>
                    <li>
                        <a class="q-tag" href="<?php echo get_term_link($tag->term_id, 'forgivingheals_tag'); ?> ">
                            <?php echo $tag->name; ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>   
        </div>
        <div class="quote-reaction-style">
            <div>
                <span class="icon-quote"></span><?php the_content() ?>
            </div>
            <?php if( $reaction->ID == $story->et_best_reaction) {?>
            <p class="alert-stt-reaction-style">
                <i class="fa fa-check-circle"></i>
                <?php _e("This reaction accepted by", ET_DOMAIN) ?> <a href="javascript:void(0)"><?php the_author_meta( 'display_name', $story->post_author ); ?></a>. 
                <?php echo et_the_time( strtotime( get_post_meta( $reaction->ID, 'et_is_best_reaction', true ) ) );  ?>
                <?php printf(__("Earned %d points.", ET_DOMAIN), $badge_points->a_accepted) ?>
            </p>
            <?php } ?>
        </div>
    </div><!-- end left content -->
    <div class="col-md-4 q-right-content">
        <ul class="story-statistic">
            <li>
                <span class="story-views">
                    <?php echo $story->et_view_count ?>
                </span>
                <?php _e("views",ET_DOMAIN) ?>
            </li>
            <li class="<?php if($story->et_best_reaction) echo 'active'; ?>">
                <span class="story-reactions">
                    <?php echo $story->et_reactions_count ?> 
                </span>
                <?php _e("reactions",ET_DOMAIN) ?>
            </li>
            <li>
                <span class="story-votes">
                    <?php echo $story->et_vote_count ?> 
                </span>
                <?php _e("votes",ET_DOMAIN) ?>
            </li>
        </ul>
    </div><!-- end right content -->                    
</li>