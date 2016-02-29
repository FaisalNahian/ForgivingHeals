<?php
/**
 * The Template for displaying all single stories
 *
 * @package: ForgivingHeals
 * @since: ForgivingHeals 1.0
 * @author: MaxWeb
 */
global $post, $wp_rewrite, $current_user, $forgivingheals_story, $wp_query;
the_post();
$story        = ForgivingHeals_Stories::convert($post);
$et_post_date    = et_the_time(strtotime($story->post_date));
$category        = !empty($story->story_category[0]) ? $story->story_category[0]->name : __('No Category',ET_DOMAIN);
$category_link   = !empty($story->story_category[0]) ? get_term_link( $story->story_category[0]->term_id, 'story_category' ) : '#';
/**
 * global forgivingheals_story
*/
$forgivingheals_story    =   $story;
get_header();

$parent_comments       = get_comments( array(
    'post_id'       => $post->ID,
    'parent'        => 0,
    'status'        => 'approve',
    'post_status'   => 'publish',
    'order'         => 'ASC',
    'type'          => 'story'
) );
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content single-content">
        <div class="row select-category single-head">
            <div class="col-md-2 col-xs-2">
                <span class="back">
                    <i class="fa fa-angle-double-left"></i> <a href="<?php echo home_url(); ?>"><?php _e("Home", ET_DOMAIN); ?></a>
                </span>
            </div>
            <div class="col-md-8 col-xs-8">
                <h3><?php the_title(); ?></h3>
            </div>
        </div><!-- END SELECT-CATEGORY -->
        <div id="story_content" class="row story-main-content story-item" data-id="<?php echo $post->ID; ?>">
            <!-- Vote section -->
            <?php get_template_part( 'template/item', 'vote' ); ?>
            <!--// Vote section -->
            <div class="col-md-9 col-xs-9 q-right-content">

                <!-- admin control -->
                <ul class="post-controls">
                    <?php if($current_user->ID == $forgivingheals_story->post_author || forgivingheals_user_can('edit_story')) { ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Edit", ET_DOMAIN) ?>" data-name="edit" class="post-edit action">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if( current_user_can( 'manage_options' ) ){ ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Delete", ET_DOMAIN) ?>" data-name="delete" class="post-delete action" >
                            <i class="fa fa-trash-o"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <!-- Follow Action -->
                    <?php
                        $user_following = explode(',', $story->et_users_follow);
                        $is_followed    = in_array($current_user->ID, $user_following);
                        if(!$is_followed){
                    ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Follow", ET_DOMAIN) ?>" data-name="follow" class="action follow" >
                            <i class="fa fa-plus-square"></i>
                        </a>
                    </li>
                    <?php } else { ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Unfollow", ET_DOMAIN) ?>" data-name="unfollow" class="action followed" >
                            <i class="fa fa-minus-square"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <!-- // Follow Action -->
                    <!-- report Action -->
                    <?php if(is_user_logged_in() && !$story->reported && $story->post_status != "pending"){ ?>
                     <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Report", ET_DOMAIN) ?>" data-name="report" class="action report" >
                            <i class="fa fa-exclamation-triangle"></i>
                        </a>
                    </li>
                    <?php } else if( current_user_can( 'manage_options' ) ) { ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Approve", ET_DOMAIN) ?>" data-name="approve" class="action approve" >
                            <i class="fa fa-check"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <!--// Report Action -->
                </ul>
                <!--// admin control -->
                <!-- story tag -->
                <div class="top-content">
                    <?php if($story->et_best_reaction){ ?>
                    <span class="reacted"><i class="fa fa-check"></i> <?php _e("Answered", ET_DOMAIN) ?></span>
                    <?php } ?>
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
                <!--// story tag -->
                <div class="clearfix"></div>

                <div class="story-content">
                    <?php the_content() ?>
                </div>

                <div class="row">
                    <div class="col-md-8 col-xs-8 story-cat">
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
                    <div class="col-md-4 col-xs-4 story-control">
                        <ul>
                            <li>
                                <a class="share-social" href="javascript:void(0)" data-toggle="popover" data-placement="top"  data-container="body" data-content='<?php echo forgivingheals_template_share($story->ID); ?>' data-html="true">
                                    <?php _e("Share",ET_DOMAIN) ?> <i class="fa fa-share"></i>
                                </a>
                            </li>
                            <!-- <li class="collapse">
                                <a href="javascript:void(0)">
                                    <?php _e("Report",ET_DOMAIN) ?> <i class="fa fa-flag"></i>
                                </a>
                            </li> -->
                            <li>
                                <a href="#container_<?php echo $post->ID ?>" class="show-comments <?php if(count($parent_comments) > 0) echo 'active'; ?>">
                                    <?php
                                        printf( __( 'Comment(%d) ', ET_DOMAIN ), count($parent_comments));
                                    ?> <i class="fa fa-comment"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="comments-container <?php if(count($parent_comments) == 0) echo 'collapse'; ?>" id="container_<?php echo $post->ID ?>">
                    <div class="comments-wrapper">
                        <?php
                            if(!empty($parent_comments)){
                                foreach ($parent_comments as $child) {
                                    forgivingheals_comments_loop($child) ;
                                }
                            }
                        ?>
                    </div>
                    <?php forgivingheals_comment_form($post); ?>
                </div><!-- END COMMENTS CONTAINER -->
            </div>
        </div><!-- END STORY-MAIN-CONTENT -->

        <?php if( is_active_sidebar( 'forgivingheals-content-story-banner-sidebar' ) ){ ?>
        <div class="row">
            <div class="col-md-12 ads-wrapper">
                <?php dynamic_sidebar( 'forgivingheals-content-story-banner-sidebar' ); ?>
            </div>
        </div><!-- END WIDGET BANNER -->
        <?php } ?>

        <div class="row reactions-filter" id="reactions_filter">
            <div class="max-col-md-8">
                <div class="col-md-6 col-xs-6">
                    <span class="reactions-count"><span class="number">
                        <?php echo $story->et_reactions_count ?></span> <?php _e("Reaction(s)",ET_DOMAIN) ?>
                    </span>
                </div>
                <div class="col-md-6 col-xs-6 sort-stories">
                    <ul>
                        <li>
                            <a class="<?php echo !isset($_GET['sort']) ? 'active' : ''; ?>" href="<?php echo get_permalink( $story->ID ); ?>"><?php _e("Votes",ET_DOMAIN) ?></a>
                        </li>
                        <!-- <li>
                            <a class="<?php echo isset($_GET['sort']) && $_GET['sort'] == 'active' ? 'active' : ''; ?>" href="<?php echo add_query_arg(array('sort' => 'active')); ?>"><?php _e("Active",ET_DOMAIN) ?></a>
                        </li> -->
                        <li>
                            <a class="<?php echo isset($_GET['sort']) && $_GET['sort'] == 'oldest' ? 'active' : ''; ?>" href="<?php echo add_query_arg(array('sort' => 'oldest')); ?>"><?php _e("Oldest",ET_DOMAIN) ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <?php forgivingheals_reactions_loop(); ?>

        <?php if(is_active_sidebar( 'forgivingheals-btm-single-story-banner-sidebar' )){ ?>
        <div class="row">
            <div class="col-md-12 ads-wrapper reactions-ad-wrapper">
                <?php dynamic_sidebar( 'forgivingheals-btm-single-story-banner-sidebar' ); ?>
            </div>
        </div>
        <?php } ?>

        <div class="row form-reply">
            <div class="col-md-12">
                <h3><?php _e("Your Reaction",ET_DOMAIN) ?></h3>
                <form id="form_reply" method="POST">
                    <input type="hidden" name="forgivingheals_nonce" value="<?php echo wp_create_nonce( 'insert_reaction' );?>" />
                    <input type="hidden" name="post_parent" value="<?php echo $post->ID ?>" />
                    <?php wp_editor( '', 'post_content', editor_settings() ); ?>
                    <div class="row submit-wrapper">
                        <div class="col-md-2">
                            <button id="submit_reply" class="btn-submit">
                                <?php _e("Post reaction",ET_DOMAIN) ?>
                            </button>
                        </div>
                        <div class="col-md-10 term-texts">
                            <?php forgivingheals_tos("reaction"); ?>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- END FORM REPLY -->

        <div class="clearfix"></div>

        <?php do_action( 'forgivingheals_btm_stories_listing' ); ?>

    </div>
    <?php get_sidebar( 'right' ); ?>
    <script type="text/javascript">
        currentStory = <?php echo defined('JSON_HEX_QUOT') ? json_encode( $story, JSON_HEX_QUOT ) : json_encode( $story ) ?>;
    </script>
<?php get_footer() ?>