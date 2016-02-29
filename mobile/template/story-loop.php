<?php
    global $post;
    $story        = ForgivingHeals_Stories::convert($post);
    $et_post_date    = et_the_time(strtotime($story->post_date));
    $category        = !empty($story->story_category[0]) ? $story->story_category[0]->name : __('No Category',ET_DOMAIN);
    $category_link   = !empty($story->story_category[0]) ? get_term_link( $story->story_category[0]->term_id, 'story_category' ) : '#';
?>
<li <?php post_class( 'story-item' );?> data-id="<?php echo $post->ID ?>">
    <!--<div class="avatar-user">
        <a href="<?php the_permalink(); ?>">
            <?php echo et_get_avatar($post->post_author, 55) ?>
        </a>
    </div>-->
    <div class="info-user">
        <?php forgivingheals_user_badge($post->post_author,true,true) ?>
        <ul class="info-review-story">
            <li>
                <?php echo $story->et_view_count ?><i class="fa fa-eye"></i>
            </li>
            <?php if($story->et_best_reaction){ ?>
            <li class="active">
                <?php echo $story->et_reactions_count ?><i class="fa fa-check-circle-o"></i>
            </li>
            <?php } else { ?>
            <li>
                <?php echo $story->et_reactions_count ?><i class="fa fa-comments"></i>
            </li>
            <?php } ?>
            <li>
                <?php echo $story->et_vote_count ?><i class="fa fa-chevron-circle-up"></i>
            </li>
        </ul>
    </div>
    <div class="content-story">
        <h2 class="title-story">
            <a href="<?php the_permalink(); ?>"><?php the_title() ?></a>
        </h2>
        <div class="info-tag-time">
            <ul class="list-tag collapse">
                <?php
                    foreach ($story->forgivingheals_tag as $tag) {
                ?>
                <li>
                    <a href="<?php echo get_term_link($tag->term_id, 'forgivingheals_tag'); ?> ">
                        <?php echo $tag->name; ?>
                    </a>
                </li>
                <?php } ?>
            </ul>
            <span class="time-categories">
                <?php
                    $author = '<a href="'.get_author_posts_url( $story->post_author ).'">'.$story->author_name.'</a>';
                    printf( __( 'Submitted by %s %s in', ET_DOMAIN ), $author, $et_post_date);
                ?>
                <a href="<?php echo $category_link ?>"><?php echo $category ?></a>.
            </span>
        </div>
    </div>
</li>