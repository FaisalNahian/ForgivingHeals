<?php

/**
 * register post type change log to store user changelog
 */
add_action('init', 'forgivingheals_register_post_type_change_log');
function forgivingheals_register_post_type_change_log() {
    register_post_type('changelog', array(
        'labels' => array(
            'name' => __('change log', ET_DOMAIN) ,
            'singular_name' => __('change log', ET_DOMAIN) ,
            'add_new' => __('Add New', ET_DOMAIN) ,
            'add_new_item' => __('Add New log', ET_DOMAIN) ,
            'edit_item' => __('Edit log', ET_DOMAIN) ,
            'new_item' => __('New change log', ET_DOMAIN) ,
            'all_items' => __('All change logs', ET_DOMAIN) ,
            'view_item' => __('View change log', ET_DOMAIN) ,
            'search_items' => __('Search change logs', ET_DOMAIN) ,
            'not_found' => __('No change log found', ET_DOMAIN) ,
            'not_found_in_trash' => __('Nochange logs found in Trash', ET_DOMAIN) ,
            'parent_item_colon' => '',
            'menu_name' => __('change logs', ET_DOMAIN)
        ) ,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => false,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => 'change-log',
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array(
            'title',
            'editor',
            'author',
            'excerpt',
            'comments',
            'custom-fields'
        )
    ));
}

/**
 * class ForgivingHeals_change log control the way to act with post type change log
 * @author Dakachi
 * @package ForgivingHeals
 * @version 1.0
 */
class ForgivingHeals_Log extends AE_Posts
{
    static $instance;

    /**
     * return class $instance
     */
    public static function get_instance() {
        if (self::$instance == null) {

            self::$instance = new ForgivingHeals_Log();
        }
        return self::$instance;
    }

    /**
     * construct instance, set post_type and meta data
     * @since 1.0
     */
    function __construct() {
        $this->post_type = 'changelog';

        $this->meta = array(
            'forgivingheals_point',
            'forgivingheals_voted_by',
            'forgivingheals_be_voted'
        );

        /**
         * setup convert field of post data
         */
        $this->convert = array(
            'post_title',
            'post_name',
            'post_content',
            'ID',
            'post_author'
        );
    }

    /**
     * static function query user change log
     * @param array $args query params, see more about this on WP_Query
     * @return object WP_Query
     * @since 1.0
     * @author Dakachi
     */
    public static function query($args = array()) {
        global $user_ID;
        $args['post_type'] = 'changelog';
        $args['showposts'] = isset($args['showposts']) ? $args['showposts'] : 10;
        $args['author'] = $user_ID;

        /**
         * construct WP_Query object
         */
        $post_query = new WP_Query($args);
        return $post_query;
    }

    /**
     * convert a post to pack object, which contain meta and tax data
     * @param $object Post
     * @return $object Post with meta data
     * @since 1.0
     * @author Dakachi
     */
    public static function forgivingheals_convert($post) {
        $instance = self::get_instance();
        $result = $instance->convert($post);
        return $result;
    }

    /**
     * prepare data and insert to changlog
     */
    function insert_changelog($args, $post, $point) {

        global $user_ID;

        $args['post_parent'] = $post->ID;
        $args['forgivingheals_point'] = $point;
        $args['forgivingheals_voted_by'] = $user_ID;
        $args['forgivingheals_be_voted'] = $post->post_author;
        $args['post_status'] = 'publish';
        $args['post_author'] = $post->post_author;

        // $args['changelog_type'] = $args['post_title'];

        $result = $this->insert($args);

        /**
         * insert changelog for current user
         */
        if ($post->post_author != $user_ID) {

            $args['post_author'] = $user_ID;
            $this->insert($args);
        }

        delete_transient('forgivingheals_changelog_' . $user_ID);
        delete_transient('forgivingheals_changelog_' . $post->post_author);
    }
}

/**
 # change log action
 * below are the code catch action insert point and data
 * story, reaction vote up
 * story, reaction vote down
 * mark and unmark an reaction to the best
 * insert story, reaction
 */

/**
 * insert changelog when author post is voted up
 * @package ForgivingHeals
 * @since 1.0
 * @author Dakachi
 */
add_action('forgivingheals_point_vote_up', 'forgivingheals_changelog_point_vote_up', 10, 2);
function forgivingheals_changelog_point_vote_up($post, $point) {
    global $user_ID;

    $log = ForgivingHeals_Log::get_instance();
    $args = array();

    // change log when story is voted up
    if ($post->post_type == 'story') {
        $args['post_title'] = 'q_vote_up';
        $args['post_content'] = sprintf(__('Your story "%s" was voted up, earned %d points', ET_DOMAIN) , $post->post_title, $point);
    }

    // change log when reaction is voted up
    if ($post->post_type == 'reaction') {
        $args['post_title'] = 'a_vote_up';
        $args['post_content'] = sprintf(__('Your reaction on story "%s" was voted up, earned %d points', ET_DOMAIN) , get_the_title($post->post_parent) , $point);
    }

    /**
     * insert change log to database
     */
    $result = $log->insert_changelog($args, $post, $point);
}
 // forgivingheals point vote up



/**
 * insert change log when a story is voted down
 * @package QnA
 * @since 1.0
 * @author Dakachi
 */
add_action('forgivingheals_point_vote_down', 'forgivingheals_changelog_point_vote_down', 10, 2);
function forgivingheals_changelog_point_vote_down($post, $point) {
    global $user_ID;

    $log = ForgivingHeals_Log::get_instance();
    $args = array();

    // change log when story is voted down
    if ($post->post_type == 'story') {
        $args['post_title'] = 'q_vote_down';
        $args['post_content'] = sprintf(__('Your story "%s" was voted down, earned %d points', ET_DOMAIN) , $post->post_title, $point);
    }

    // change log when reaction is voted up
    if ($post->post_type == 'reaction') {
        $args['post_title'] = 'a_vote_down';
        $args['post_content'] = sprintf(__('Your reaction on story "%s" was voted down, earned %d points', ET_DOMAIN) , get_the_title($post->post_parent) , $point);
    }

    /**
     * insert change log to database
     */
    $result = $log->insert_changelog($args, $post, $point);
}
 // forgivingheals point vote down



/**
 * add change log when a post is unvoted
 * @package ForgivingHeals
 * @since 1.0
 * @author Dakachi
 */
add_action('forgivingheals_point_unvote', 'forgivingheals_changelog_unvote', 10, 2);
function forgivingheals_changelog_unvote($post, $point) {
    global $user_ID;

    $log = ForgivingHeals_Log::get_instance();
    $args = array();

    if ($post->post_type == 'story') {
        $args['post_title'] = 'q_unvote';
        $args['post_content'] = sprintf(__('Your story "%s" was unvoted, return %d points', ET_DOMAIN) , $post->post_title, $point);
    }

    if ($post->post_type == 'reaction') {
        $args['post_title'] = 'a_unvote';
        $args['post_content'] = sprintf(__('Your reaction on story "%s" was unvoted, return %d points', ET_DOMAIN) , get_the_title($post->post_parent) , $point);
    }

    /**
     * insert change log to database
     */
    $result = $log->insert_changelog($args, $post, $point);
}
 // forgivingheals point unvote



/**
 * add change log when an reaction was mark reacted
 * @package ForgivingHeals
 * @version 1.0
 * @author Dakachi
 */
add_action('forgivingheals_point_reaction_marked', 'forgivingheals_changelog_mark_reactioned', 10, 2);
function forgivingheals_changelog_mark_reactioned($post, $point) {
    global $user_ID;

    $log = ForgivingHeals_Log::get_instance();
    $args = array();

    if ($post->post_type == 'reaction') {
        $args['post_title'] = 'a_marked';
        $args['post_content'] = sprintf(__('Your reaction on story "%s" was marked best reaction, gain %d points', ET_DOMAIN) , get_the_title($post->post_parent) , $point);

        /**
         * insert change log to database
         */
        $result = $log->insert_changelog($args, $post, $point);
    }
}
 // forgivingheals point mark reacted



/**
 * add change log when an reaction was unmark reacted
 * @package ForgivingHeals
 * @version 1.0
 * @author Dakachi
 */
add_action('forgivingheals_point_reaction_unmarked', 'forgivingheals_changelog_reaction_unmark', 10, 2);
function forgivingheals_changelog_reaction_unmark($post, $point) {
    global $user_ID;

    $log = ForgivingHeals_Log::get_instance();
    $args = array();

    if ($post->post_type == 'reaction') {
        $args['post_title'] = 'a_unmarked';
        $args['post_content'] = sprintf(__('Your best reaction on story "%s" was undo, gain %d points', ET_DOMAIN) , get_the_title($post->post_parent) , $point);

        /**
         * insert change log to database
         */
        $result = $log->insert_changelog($args, $post, $point);
    }
}
 // forgivingheals point unmark reacted



/**
 * add change log when a post inserted
 * @package ForgivingHeals
 * @version 1.0
 * @author Dakachi
 */
add_action('forgivingheals_point_insert_post', 'forgivingheals_changelog_insert_post', 10, 2);
function forgivingheals_changelog_insert_post($post, $point) {
    global $user_ID;

    $log = ForgivingHeals_Log::get_instance();
    $args = array();

    if ($post->post_type == 'reaction') {
        $args['post_title'] = 'post_reaction';
        $args['post_content'] = sprintf(__('You reacted on story "%s"', ET_DOMAIN) , get_the_title($post->post_parent));
    }

    if ($post->post_type == 'story') {
        $args['post_title'] = 'post_story';
        $args['post_content'] = sprintf(__('Your posted a story "%s" ', ET_DOMAIN) , get_the_title($post->post_parent) , $point);
    }

    /**
     * insert change log to database
     */
    $result = $log->insert_changelog($args, $post, $point);
}
 // forgivingheals point insert post

/**
 * catch event when user post a story or reaction a story
 *
 */
add_action('wp_insert_post', 'forgivingheals_changelog_update_post', 10, 3);
function forgivingheals_changelog_update_post($post_id, $post, $update) {
    // return if is update post
    if(!$update)  return ;

    global $user_ID;

    $log = ForgivingHeals_Log::get_instance();
    $args = array();

    $point  = 0;

    if($post->post_type == 'story') {
        $args['post_title'] = 'edit_story';
        $args['post_content'] = sprintf(__('You story on story "%s" was edited', ET_DOMAIN) , get_the_title($post->post_parent));
    }

    if($post->post_type == 'reaction') {
        $args['post_title'] = 'edit_reaction';
        $args['post_content'] = sprintf(__('Your reaction on story "%s was edited"', ET_DOMAIN) , get_the_title($post->post_parent));
    }

    /**
     * insert change log to database
     */
    $result = $log->insert_changelog($args, $post, $point);

}

/**
 # end change log action
 * below are the code catch action insert point and data
 * story, reaction vote up
 * story, reaction vote down
 * mark and unmark an reaction to the best
 * insert story, reaction
 */

function forgivingheals_list_changelog($args = array()) {
    global $user_ID;
    $forgivingheals_log     = ForgivingHeals_Log::query($args);
    $change_log = ForgivingHeals_Log::get_instance();
    if ($forgivingheals_log->have_posts()) {
?>

        <ul>
        <?php
        while ($forgivingheals_log->have_posts()) {
            $forgivingheals_log->the_post();
            global $post;
            $log    = $change_log->convert($post);
            $parent = get_post($post->post_parent);

            $i    = '<i class="fa fa-circle"></i>';
            $text = __("This is default text.", ET_DOMAIN);

            switch ($log->post_title) {
                case 'a_vote_up':

                    if ($log->forgivingheals_be_voted == $user_ID) {
                        $text = __("Your reaction on story %s was voted up.", ET_DOMAIN);
                    } else {
                        $text = __("You voted up an reaction on story %s.", ET_DOMAIN);
                    }
                    $i = '<i class="fa fa-thumbs-up"></i>';
                    break;

                case 'a_vote_down':

                    if ($log->forgivingheals_be_voted == $user_ID) {
                        $text = __("Your reaction on story %s was voted down.", ET_DOMAIN);
                    } else {
                        $text = __("Your voted down an reaction on story %s.", ET_DOMAIN);
                    }
                    $i = '<i class="fa fa-thumbs-down"></i>';
                    break;

                case 'q_vote_up':

                    // $text    =   __("Your story %s was voted up.", ET_DOMAIN);
                    if ($log->forgivingheals_be_voted == $user_ID) {
                        $text = __("Your story %s was voted up.", ET_DOMAIN);
                    } else {
                        $text = __("You voted up story %s.", ET_DOMAIN);
                    }
                    $i = '<i class="fa fa-thumbs-up"></i>';
                    break;

                case 'q_vote_down':

                    // $text    =   __("Your story %s was voted down.", ET_DOMAIN);
                    if ($log->forgivingheals_be_voted == $user_ID) {
                        $text = __("Your story %s was voted down.", ET_DOMAIN);
                    } else {
                        $text = __("You voted down story %s.", ET_DOMAIN);
                    }
                    $i = '<i class="fa fa-thumbs-down"></i>';
                    break;

                case 'post_story':
                    $text = __("You asked %s.", ET_DOMAIN);
                    $i  =   '<i class="fa fa-story-circle"></i>';
                    break;

                case 'edit_story':

                    if ( $log->forgivingheals_be_voted == $user_ID && $log->forgivingheals_be_voted != $log->forgivingheals_voted_by ) {
                        $text = __("Your story %s was edited.", ET_DOMAIN);
                    } else {
                        $text = __("You edited story %s.", ET_DOMAIN);
                    }

                    $i  =   '<i class="fa fa-story-circle"></i>';
                    break;

                case 'post_reaction':
                    $text = __("You reacted story %s.", ET_DOMAIN);
                    $i  =   '<i class="fa fa-comments"></i>';
                    break;



                case 'edit_reaction':
                    if ( $log->forgivingheals_be_voted == $user_ID && $log->forgivingheals_be_voted != $log->forgivingheals_voted_by ) {
                        $text = __("Your reaction on story %s was edited.", ET_DOMAIN);
                    } else {
                        $text = __("You edited reaction on story %s.", ET_DOMAIN);
                    }
                    $i  =   '<i class="fa fa-comments"></i>';
                    break;

                case 'q_unvote':
                    if ($log->forgivingheals_be_voted == $user_ID) {
                        $text = __("Your story %s was unvoted.", ET_DOMAIN);
                    } else {
                        $text = __("You unvoted story %s.", ET_DOMAIN);
                    }
                    $i = '<i class="fa fa-undo"></i>';
                    break;

                case 'a_unvote':

                    if ($log->forgivingheals_be_voted == $user_ID) {
                        $text = __("Your reaction on story %s was unvoted.", ET_DOMAIN);
                    } else {
                        $text = __("Your unvoted an reaction on story %s.", ET_DOMAIN);
                    }
                    $i = '<i class="fa fa-undo"></i>';
                    break;

                case 'a_marked':
                    if ($log->forgivingheals_be_voted == $user_ID) {
                        $text = __("Your reaction on story %s was marked as the accepted reaction.", ET_DOMAIN);
                    } else {
                        $text = __("You marked story %s was reacted.", ET_DOMAIN);
                    }
                    $i = '<i class="fa fa-check-circle"></i>';
                    break;

                case 'a_unmarked':
                    if ($log->forgivingheals_be_voted == $user_ID) {
                        $text = __("Your best reaction on story %s was unaccepted.", ET_DOMAIN);
                    } else {
                        $text = __("You changed the best reaction on story %s.", ET_DOMAIN);
                    }
                    $i = '<i class="fa fa-undo"></i>';
                    break;

                default:
                    $text = '';
                    break;
            }

            $link = '';
            if(!empty($parent)){
                if ($parent->post_type == 'reaction') {
                    $permalink = get_permalink($parent->post_parent);
                    $link = '<a href="' . $permalink . '">' . get_the_title($parent->post_parent) . '</a>';
                } else {
                    $permalink = get_permalink($parent->ID);
                    $link = '<a href="' . $permalink . '">' . get_the_title($parent->ID) . '</a>';
                }
            }
?>
                <li>
                    <?php echo $i; ?>
                    <span>
                        <?php printf($text, $link); ?>
                        <br>
                        <span class="time-activity">
                            <?php echo et_the_time(strtotime($post->post_date)); ?>
                        </span>
                    </span>
                </li>
            <?php
        } ?>
        </ul>

    <?php
    }
    wp_reset_query();
}
