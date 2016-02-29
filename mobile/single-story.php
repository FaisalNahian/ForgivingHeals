<?php
/**
 * Template: STORIES LISTING
 * version 1.0
 * @author: ThaiNT
 **/
et_get_mobile_header();
global $post,$wp_rewrite,$current_user, $forgivingheals_story;
the_post();

$story        = ForgivingHeals_Stories::convert($post);
$et_post_date    = et_the_time(strtotime($story->post_date));
$category        = !empty($story->story_category[0]) ? $story->story_category[0]->name : __('No Category',ET_DOMAIN);
$category_link   = !empty($story->story_category[0]) ? get_term_link( $story->story_category[0]->term_id, 'story_category' ) : '#';
$forgivingheals_story    =   $story;

$vote_up_class  =  'action vote vote-up ' ;
$vote_up_class  .= ($story->voted_up) ? 'active' : '';
$vote_up_class  .= ($story->voted_down) ? 'disabled' : '';

$vote_down_class = 'action vote vote-down ';
$vote_down_class .= ($story->voted_down) ? 'active' : '';
$vote_down_class .= ($story->voted_up) ? 'disabled' : '';

$parent_comments    = get_comments( array(
    'post_id'       => $post->ID,
    'parent'        => 0,
    'status'        => 'approve',
    'post_status'   => 'publish',
    'order'         => 'ASC',
    'type'          => 'story'
) );
?>
<!-- CONTAINER -->
<div class="wrapper-mobile">
    <!-- CONTENT STORY -->
    <section class="list-story-wrapper" id="story_content">
    	<div class="container">
            <div class="row">
            	<div class="col-md-12">
                	<div class="content-qna-wrapper">
                        <!--
                        <div class="avatar-user">
                            <a href="<?php echo get_author_posts_url( $story->post_author ); ?>">
                                <?php echo et_get_avatar($story->post_author, 55) ?>
                            </a>
                        </div>-->
                        <div class="info-user">
                            <!-- <span title="1" class="user-badge">Newbie</span> -->
                            <?php forgivingheals_user_badge($story->post_author, true, true) ?>
                        </div>
                        <div class="content-story">
                            <h2 class="title-story">
                                <a href="javascript:void(0)"><?php the_title() ?></a>
                            </h2>
                            <div class="details">
                            	<?php the_content(); ?>
                            </div>
                            <div class="info-tag-time">
                            	<ul class="list-tag">
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
                                        printf( __( 'Submitted by %s %s in', ET_DOMAIN ), $author, $et_post_date );
                                    ?>
                                     <a href="<?php echo $category_link ?>"><?php echo $category ?></a>.
                                </span>
                            </div>
                            <div class="vote-wrapper">

                            	<a href="javascript:void(0)" data-name="vote_up" class="<?php echo $vote_up_class ?>">
                                    <i class="fa fa-angle-up"></i>
                                </a>

                                <span class="number-vote"><?php echo $story->et_vote_count ?></span>

                                <a href="javascript:void(0)" data-name="vote_down" class="<?php echo $vote_down_class ?>">
                                    <i class="fa fa-angle-down"></i>
                                </a>

                                <?php if($forgivingheals_story->et_best_reaction) {?>
                                <a href="javascript:void(0)" class="reaction-active-label has-best-reaction">
                                    <i class="fa fa-check"></i><?php _e("Answered", ET_DOMAIN) ?>
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <!-- SHARE -->
                    <div class="share">
                        <ul class="list-share">
                            <li>
                                <a class="share-social" href="javascript:void(0)" rel="popover" data-container="body" data-content='<?php echo forgivingheals_template_share($story->ID); ?>' data-html="true">
                                    <?php _e("Share",ET_DOMAIN) ?> <i class="fa fa-share"></i>
                                </a>
                            </li>
                            <!-- <li class="collapse">
                                <a href="javascript:void(0)"><?php _e("Report", ET_DOMAIN) ?><i class="fa fa-flag"></i></a>
                            </li> -->
                            <li>
                                <a href="javascript:void(0)" class="mb-show-comments">
                                    <?php _e("Comment", ET_DOMAIN) ?>(<?php echo count($parent_comments) ?>)&nbsp;<i class="fa fa-comment"></i>
                                </a>
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
                                if(!empty($parent_comments)){
                                    foreach ($parent_comments as $comment) {
                                        forgivingheals_mobile_comments_loop( $comment );
                                    }
                                }
                             ?>
                        </ul>
                        <?php forgivingheals_mobile_comment_form($post) ?>
                        <a href="javascript:void(0)" class="add-cmt-in-cmt"><?php _e("Add comment", ET_DOMAIN) ?></a>
                    </div>
                    <!-- COMMENT IN COMMENT / END -->
                </div>
            </div>
        </div>
    </section>
    <!-- CONTENT STORY / END -->

    <!-- LABEL -->
    <section class="label-vote-wrapper">
    	<div class="container">
            <div class="row">
            	<div class="col-md-12">
                    <span><span class="number"><?php echo et_count_reaction($story->ID) ?></span> <?php _e("Reactions",ET_DOMAIN) ?></span>
                    <div class="select-categories-wrapper">
                        <div class="select-categories">
                            <select class="select-grey-bg" id="move_to_order">
                                <option value="<?php echo get_permalink( $story->ID ); ?>"><?php _e("Vote",ET_DOMAIN) ?></option>
                                <option <?php if( isset($_GET['order']) && $_GET['order'] == "oldest") echo 'selected'; ?> value="<?php echo add_query_arg(array('order' => 'oldest'), get_permalink( $story->ID )); ?>"><?php _e("Oldest",ET_DOMAIN) ?></option>
                            </select>
                        </div>
                    </div>
                </div>
             </div>
         </div>
    </section>
    <!-- LABEL / END -->
    <!-- CONTENT REACTIONS LOOP -->
    <div id="reactions_main_list">
    <?php
        $paged = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 ;

        $reply_args = array(
                'post_type'     => 'reaction',
                'post_status'   => 'publish',
                'post_parent'   => $post->ID,
                'paged'         => $paged,
            );

        //if current user is admin show pending reactions
        if( is_user_logged_in() && current_user_can( 'manage_options' ) )
            $reply_args['post_status'] = array('publish','pending');

        if( isset($_GET['sort']) && $_GET['sort'] == "oldest" ){
            $reply_args['order'] = 'ASC';
        } else {
            add_filter("posts_join"     , array("ForgivingHeals_Front", "_post_vote_join") );
            add_filter("posts_orderby"  , array("ForgivingHeals_Front", "_post_vote_orderby") );
        }
        $replyQuery = new WP_Query($reply_args);
        $reactionsData = array();
        global $post;
        if($replyQuery->have_posts()){
            while($replyQuery->have_posts()){ $replyQuery->the_post();
                $reactionsData[] = ForgivingHeals_Reactions::convert($post);
                get_template_part( 'mobile/template/item', 'reaction' );
            }
        }
        wp_reset_query();
    ?>
    </div>
    <div class="clearfix" style="height:20px;"></div>
    <!-- CONTENT REACTIONS LOOP / END -->
    <!-- PAGINATIONS REACTION -->
    <section class="list-pagination-wrapper">
        <?php
            echo paginate_links( array(
                'base'      => get_permalink($story->ID) . '%#%',
                'format'    => $wp_rewrite->using_permalinks() ? 'page/%#%' : '?paged=%#%',
                'current'   => max(1, $paged),
                'mid_size'  => 1,
                'total'     => $replyQuery->max_num_pages,
                'prev_text' => '<',
                'next_text' => '>',
                'type'      => 'list'
            ) );
        ?>
    </section>
    <!-- PAGINATIONS REACTION / END -->
    <?php if(is_user_logged_in()){ ?>
    <!-- POST REACTION -->
    <section class="post-reactions-wrapper">
    	<div class="container">
            <div class="row">
            	<div class="col-md-12">
        			<a href="javascript:void(0)" class="btn-post-reactions"><?php _e("Post reaction", ET_DOMAIN) ?></a>
                    <form class="form-post-reactions" id="insert_reaction" action="">
                        <input type="hidden" name="forgivingheals_nonce" value="<?php echo wp_create_nonce( 'insert_reaction' );?>" />
                        <input type="hidden" name="post_parent" value="<?php echo $story->ID ?>" />
                    	<textarea name="post_content" id="post_content" rows="5"  placeholder="<?php _e("Type your reaction", ET_DOMAIN) ?>"></textarea>
                        <input type="submit" class="btn-submit" name="submit" id="" value="<?php _e("Post reaction", ET_DOMAIN) ?>">
                        <a href="javascript:void(0)" id="close_reply_form" class="close-form-post-reactions"><?php _e("Cancel", ET_DOMAIN) ?></a>
                    </form>
                </div>
            </div>
         </div>
    </section>
    <div class="clearfix" style="height:20px;"></div>
    <!-- POST REACTION / END -->
    <?php } ?>

</div>
<!-- CONTAINER / END -->
<script type="text/javascript">
    var currentStory = <?php echo json_encode($story) ?>;
    var reactionsData     = <?php echo json_encode($reactionsData) ?>;
</script>
<?php
	et_get_mobile_footer();
?>