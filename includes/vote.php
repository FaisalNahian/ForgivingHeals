<?php

/**
 * catch action insert vote comment to add point to story/reaction owner
 * @package ForgivingHeals
 */

add_action('wp_insert_comment', 'forgivingheals_comment_vote', 10, 2);
function forgivingheals_comment_vote($id, $comment) {
    global $user_ID;

    /**
     * get site forgivingheals badge point system
     */
    $point = forgivingheals_get_badge_point();

    /**
     * get comment post
     */
    $post = get_post($comment->comment_post_ID);

    /**
     * user can not vote for him self
     */
    if ($user_ID == $post->post_author) return false;

    // $change_log = ForgivingHeals_Log::get_instance();

    switch ($comment->comment_type) {
        case 'vote_up':

            /*
             * return false if user can not vote up
            */
            if (!forgivingheals_user_can('vote_up')) return false;

            if ( $post->post_type == 'reaction') {
                $vote_up_point = $point->a_vote_up;
            } else {
                $vote_up_point = $point->q_vote_up;
            }

            /**
             * save point to comment, keep it to restore point to user if  the vote be undo
             */
            update_comment_meta($comment->comment_ID, 'forgivingheals_point', $vote_up_point);

            /**
             * updte user point
             */
            forgivingheals_update_user_point($post->post_author, $vote_up_point);

            /**
             * do action forgivingheals point vote up
             * @param $post the post be voted
             * @param $vote_up_point
             */
            do_action('forgivingheals_point_vote_up', $post, $vote_up_point );

            break;

        case 'vote_down':

            // return false if user can not vote down
            if (!forgivingheals_user_can('vote_down')) return false;

            if ($post->post_type == 'reaction') {
                $vote_down_point = $point->a_vote_down;
            } else {
                $vote_down_point = $point->q_vote_down;
            }

            /**
             * save point to comment, keep it to restore point to user if  the vote be undo
             */
            update_comment_meta($comment->comment_ID, 'forgivingheals_point', $vote_down_point);

            /**
             * updte user point
             */
            forgivingheals_update_user_point($post->post_author, $vote_down_point);

            /**
             * update point to current user when he/she vote down a story/reaction
             */
            forgivingheals_update_user_point($user_ID, $point->vote_down);

            /**
             * do action forgivingheals point vote down
             * @param $post the post be voted
             * @param $vote_down_point
             */
            do_action('forgivingheals_point_vote_down', $post, $vote_down_point );

            break;
    }
}

/**
 * delete a vote then should return point to user
 * @package ForgivingHeals
 * @author Dakachi
 * @package ForgivingHeals
 */
add_action('delete_comment', 'forgivingheals_comment_unvote');
function forgivingheals_comment_unvote($id) {
    global $user_ID;

    if (!$comment = get_comment($id)) return false;

    $post = get_post($comment->comment_post_ID);

    if($post->post_author == $user_ID) return ;

    /**
     * get comment forgivingheals_point
     */
    $point = get_comment_meta($id, 'forgivingheals_point', true);

    /**
     * update user point
     */
    forgivingheals_update_user_point($post->post_author, -(int)$point);
    /**
     * do action forgivingheals point unvote
     * @param $post the post be unvoted
     * @param -(int)$point
     */
    do_action('forgivingheals_point_unvote', $post, -(int)$point );
}

/**
 * catch event when user post a story or reaction a story
 *
 */
add_action('wp_insert_post', 'forgivingheals_point_insert_post', 10, 3);
function forgivingheals_point_insert_post($post_id, $post, $update) {
	// return if is update post
	if($update)  return ;
    if($post->post_status != "publish") return;
    /**
     * update point for user if post new post
    */
	global $user_ID;

    /**
     * get site forgivingheals badge point system
     */
    $point = forgivingheals_get_badge_point();

	if($post->post_type == 'story') {
		if( !empty( $point->create_story ) ) {
			/**
             * update user point
             */
            forgivingheals_update_user_point( $user_ID, $point->create_story );
            /**
             * do action forgivingheals point insert story
             * @param $post the post be unvoted
             * @param -(int)$point
             */
            do_action('forgivingheals_point_insert_post', $post, $point->create_story );
		}

	}

	if($post->post_type == 'reaction') {
        if( !empty( $point->post_reaction ) ) {
			/**
             * update user point
             */
            forgivingheals_update_user_point( $user_ID, $point->post_reaction );
            /**
             * do action forgivingheals point insert reaction
             * @param $post the post be unvoted
             * @param $point
             */
            do_action('forgivingheals_point_insert_post', $post, $point->post_reaction );
		}
	}



    return ;
}
/**
 * catch event when user delete story
 *
 */
add_action('wp_trash_post', 'forgivingheals_point_trash_post');
function forgivingheals_point_trash_post($post_id) {
    global $post, $user_ID;
    /**
     * get site forgivingheals badge point system
     */
    $point = forgivingheals_get_badge_point();
    $post  = get_post($post_id);

    if($post->post_type == 'story') {
        if( !empty( $point->create_story ) ) {
            /**
             * update user point
             */
            forgivingheals_update_user_point( $post->post_author, -(int)$point->create_story );
            /**
             * do action forgivingheals point insert story
             * @param $post the post be unvoted
             * @param -(int)$point
             */
            do_action('forgivingheals_point_trash_post', $post, -(int)$point->create_story );
        }

    }

    if($post->post_type == 'reaction') {
        if( !empty( $point->post_reaction ) ) {
            /**
             * update user point
             */
            $best_point = get_post_meta($post->ID, 'et_best_reaction_point', true);
            forgivingheals_update_user_point( $post->post_author, -( (int)$point->post_reaction + (int)$best_point ) );
            /**
             * do action forgivingheals point insert reaction
             * @param $post the post be unvoted
             * @param $point
             */
            do_action('forgivingheals_point_trash_post', $post, -( (int)$point->post_reaction + (int)$best_point ) );
        }
    }

    return $post_id;
}
/**
 * catch action when user sign up, init point for user it should be one.
 * @package ForgivingHeals
 * @author Dakachi
 */
add_action('et_insert_user', 'forgivingheals_init_user_point');
function forgivingheals_init_user_point($user_id) {
    forgivingheals_update_user_point($user_id, 1);
}

/**
 * catch action when an reaction is mark best reaction
 * add point to reaction owner
 * @package ForgivingHeals
 * @author Dakachi
 */
add_action('forgivingheals_mark_reaction', 'forgivingheals_mark_reaction_point', 10, 2);
function forgivingheals_mark_reaction_point($story_id, $reaction_id) {
    $reaction = get_post($reaction_id);
    if ($reaction) {

        // reaction is valid
        global $user_ID;
        if ($user_ID != $reaction->post_author) {

            /**
             * get site forgivingheals badge point system
             */
            $point = forgivingheals_get_badge_point();

            /**
             * update use point by reaction accepted point
             */
            forgivingheals_update_user_point($reaction->post_author, $point->a_accepted);

            ForgivingHeals_Reactions::update_field($reaction_id, 'et_best_reaction_point', $point->a_accepted);
            /**
             * do action forgivingheals point reaction mark reacted
             * @param $reaction the reaction be mark
             * @param $point
             */
            do_action('forgivingheals_point_reaction_marked', $reaction, $point->a_accepted );
        }
    }
}

/**
 * catch action when an reaction is change from best reaction to normal reaction
 * minus point to reaction owner
 * @package ForgivingHeals
 * @author Dakachi
 */
add_action('forgivingheals_remove_reaction', 'forgivingheals_remove_reaction_point');
function forgivingheals_remove_reaction_point($reaction_id) {

    // get the point added to reaction owner
    $point = get_post_meta($reaction_id, 'et_best_reaction_point', true);
    $reaction = get_post($reaction_id);

    if (!$reaction) return;

    /**
     * update use point by reaction accepted point
     */
    forgivingheals_update_user_point($reaction->post_author, (int)(-$point));

    /**
     * remove no need data
     */
    delete_post_meta($reaction_id, 'et_best_reaction_point');
    /**
     * do action forgivingheals point reaction unmark reacted
     * @param $post the post be unmark
     * @param $point
     */
    do_action('forgivingheals_point_reaction_unmarked', $reaction, (int)(-$point) );
}

/**
 * forgivingheals update user point
 * @package ForgivingHeals
 * @author Dakachi
 * @package ForgivingHeals
 */
function forgivingheals_update_user_point($user_id, $point) {

    /**
     * get current user forgivingheals_point
     */
    $current_point = get_user_meta($user_id, 'forgivingheals_point', true);
    $new_point = $current_point + (int)($point);

    /**
     * reset to 1 if point is lose to 0
     */
    if ($new_point <= 0) $new_point = 1;

    /**
     * update user meta forgivingheals_point
     */
    update_user_meta($user_id, 'forgivingheals_point', $new_point);
}
