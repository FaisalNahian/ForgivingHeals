<?php
/**
 * The Template for displaying all single stories
 *
 * @package: ForgivingHeals
 * @since: ForgivingHeals 1.0
 * @author: MaxWeb
 */
global $post,$wp_rewrite,$current_user, $forgivingheals_story, $forgivingheals_reaction;
the_post();
$reaction          = ForgivingHeals_Stories::convert($post);
$story        = ForgivingHeals_Stories::convert(get_post($post->post_parent));
$et_post_date    = et_the_time(strtotime($story->post_date));
$category        = !empty($story->story_category[0]) ? $story->story_category[0]->name : __('No Category',ET_DOMAIN);
$category_link   = !empty($story->story_category[0]) ? get_term_link( $story->story_category[0]->term_id, 'story_category' ) : '#';

/**
 * global forgivingheals_story
*/
$forgivingheals_story    =   $story;
$forgivingheals_reaction      =   $reaction;
get_header();

$parent_comments       = get_comments( array( 
    'post_id'       => $story->ID,
    'parent'        => 0,
    'status'        => 'approve',
    'post_status'   => 'publish',
    'order'         => 'ASC',
    'type'          => 'story'
));

$forgivingheals_reaction_comments   = get_comments( array( 
    'post_id'       => $forgivingheals_reaction->ID,
    'parent'        => 0,
    'status'        => 'approve',
    'post_status'   => 'publish',
    'order'         => 'ASC',
    'type'          => 'reaction'
));
$commentsData = array_merge($parent_comments, $forgivingheals_reaction_comments);
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
                    <?php if(forgivingheals_user_can('edit_story')) { ?>
                    <li>
                        <a href="javascript:void(0)" data-name="edit" class="post-edit action">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if( current_user_can( 'manage_options' ) ){ ?>
                    <li>
                        <a href="javascript:void(0)" data-name="delete" class="post-delete action" >
                            <i class="fa fa-trash-o"></i>
                        </a>
                    </li>
                    <?php } ?>
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
                    <?php echo apply_filters('et_the_content', $story->post_content ); ?>
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
                            <?php printf( __( 'Asked %s in', ET_DOMAIN ),$et_post_date); ?>
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
                            <li>
                                <a href="#container_<?php echo $story->ID ?>" class="show-comments <?php if(count($parent_comments) > 0) echo 'active'; ?>">
                                    <?php                                     
                                        printf( __( 'Comment(%d) ', ET_DOMAIN ), count($parent_comments)); 
                                    ?> <i class="fa fa-comment"></i>
                                </a>
                            </li>
                        </ul>
                    </div>                   
                </div>

                <div class="clearfix"></div>
                <div class="comments-container <?php if(count($parent_comments) == 0) echo 'collapse'; ?>" id="container_<?php echo $story->ID ?>">
                    <div class="comments-wrapper">
                        <?php      
                            if(!empty($parent_comments)){         
                                foreach ($parent_comments as $child) {
                                    forgivingheals_comments_loop($child) ;
                                }
                            }
                        ?>
                    </div>
                    <?php forgivingheals_comment_form($story); ?>          
                </div><!-- END COMMENTS CONTAINER -->             
            </div>
        </div><!-- END STORY-MAIN-CONTENT -->
        <div class="row reactions-filter" id="reactions_filter">
            <div class="max-col-md-8">
                <div class="col-md-6 col-xs-6">
                    <span class="reactions-count"><span class="number"><?php echo et_count_reaction($story->ID) ?></span> <?php _e("Reactions",ET_DOMAIN) ?></span>
                </div>
                <div class="col-md-6 col-xs-6 sort-stories">
                    <ul>
                        <li>
                            <a class="<?php echo !isset($_GET['sort']) ? 'active' : ''; ?>" href="<?php echo get_permalink( $story->ID ); ?>"><?php _e("Votes",ET_DOMAIN) ?></a>
                        </li>
                        <li>
                            <a class="<?php echo isset($_GET['sort']) && $_GET['sort'] == 'oldest' ? 'active' : ''; ?>" href="<?php echo add_query_arg(array('sort' => 'oldest')); ?>"><?php _e("Oldest",ET_DOMAIN) ?></a>
                        </li>
                    </ul>
                </div>
            </div>           
        </div>

        
        <div id="reactions_main_list">
            <div class="row story-main-content story-item reaction-item" id="<?php echo $reaction->ID ?>">
                <?php get_template_part( 'template/item', 'reaction' ); ?>
            </div><!-- END REPLY-ITEM -->
        </div>

        <div class="row form-reply collapse">
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
    </div>
    <?php get_sidebar( 'right' ); ?>
    <script type="text/javascript">
        var reactionsData     = <?php echo json_encode( array($reaction) ) ?>;
        var currentStory = <?php echo json_encode($story) ?>;
        var commentsData    = <?php echo json_encode($commentsData) ?>;
    </script>
<?php get_footer() ?>