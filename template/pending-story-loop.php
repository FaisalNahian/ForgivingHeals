<?php
    global $post;
    $story        = ForgivingHeals_Stories::convert($post);
    $et_post_date    = et_the_time(strtotime($story->post_date));
    $category        = !empty($story->story_category[0]) ? $story->story_category[0]->name : __('No Category',ET_DOMAIN);
    $category_link   = !empty($story->story_category[0]) ? get_term_link( $story->story_category[0]->term_id, 'story_category' ) : '#';
?>
<li <?php post_class( 'story-item pending-story' );?> data-id="<?php echo $post->ID ?>">
    <div class="col-md-8 col-xs-8 q-left-content">
        <div class="q-ltop-content">
            <a href="<?php the_permalink(); ?>" class="story-title">
                <?php the_title() ?>
            </a>
        </div>
        <div class="q-lbtm-content">
            <div class="story-excerpt">
                <?php the_excerpt(); ?>
            </div>
            <div class="story-cat">
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
                <div class="clearfix"></div>                
                <a href="<?php echo get_author_posts_url($story->post_author); ?>">
                    <span class="author-avatar">
                        <?php echo et_get_avatar( $story->post_author, 30 ); ?>
                    </span>
                    <span class="author-name"><?php echo $story->author_name; ?></span>
                </a>                    
                    <?php  forgivingheals_user_badge( $story->post_author ); ?>
                    <span class="story-time">
                        <?php printf( __( 'Submitted %s in', ET_DOMAIN ),$et_post_date); ?>
                    </span>

                <span class="story-category">
                    <a href="<?php echo $category_link ?>"><?php echo $category ?>.</a>
                </span>
            </div>   
        </div>
    </div><!-- end left content -->
    <div class="col-md-4 col-xs-4 q-right-content">
        <ul class="story-statistic">
            <li>
                <a href="javascript:void(0)" class="action" data-name="approve">
                    <span class="story-views">
                        <?php //echo $story->et_view_count ?>
                        <i class="fa fa-check"></i>
                    </span>
                </a>
                <?php _e("Approve",ET_DOMAIN) ?>
            </li>
            <!-- <li>
                <a href="javascript:void(0)" class="action" data-name="delete">
                    <span class="story-reactions">
                        <?php //echo $story->et_reactions_count ?> 
                        <i class="fa fa-times"></i>
                    </span>
                </a>
                <?php _e("Reject",ET_DOMAIN) ?>
            </li> -->
            <li>
                <a href="javascript:void(0)" class="action" data-name="delete">
                    <span class="story-votes">
                        <?php //echo $story->et_vote_count ?> 
                        <i class="fa fa-trash-o"></i>
                    </span>
                </a>
                <?php _e("Delete",ET_DOMAIN) ?>
            </li>
        </ul>
    </div><!-- end right content -->                    
</li> 