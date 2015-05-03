<?php

class ForgivingHeals_Related_Stories_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to single sidebars to display a list of related stories.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('story_related_widget', __('ForgivingHeals Related Stories', ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		if( $new_instance['number'] != $old_instance['number'] ){
			delete_transient( 'related_stories_query' );
		}
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'   => __('RELATED STORIES', ET_DOMAIN) ,
			'number'  => '4',
			'base_on' => 'category',
			) );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>">
				<?php _e('Number of stories to display:', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('base_on'); ?>">
				<?php _e('Stories base on:', ET_DOMAIN) ?>
			</label>
			<select id="<?php echo $this->get_field_id('base_on'); ?>" name="<?php echo $this->get_field_name('base_on'); ?>">
				<option <?php selected( $instance['base_on'], "category" ); ?> value="category">
					<?php _e('Category', ET_DOMAIN) ?>
				</option>
				<option <?php selected( $instance['base_on'], "tag" ); ?> value="tag">
					<?php _e('Tag', ET_DOMAIN) ?>
				</option>
			</select>
		</p>
	<?php
	}

	function widget( $args, $instance ) {

		global $wpdb, $post;
		if(is_singular( 'story' )){
			if(get_transient( 'related_stories_query' ) === false){

				$arrSlug  = array();
				$taxonomy = $instance['base_on'] == "category" ? "story_category" : "forgivingheals_tag";
				$terms    = get_the_terms($post->ID, $taxonomy);

				if(!empty($terms)){
					foreach ($terms as $term) {
						$arrSlug[] = $term->slug;
					}
				}
				$args = array(
						'post_type'    => 'story',
						'showposts'    => $instance['number'],
						'post__not_in' => array($post->ID),
						'order'        => 'DESC'
					);
				if(!empty($arrSlug)){
					$args['tax_query'] = array(
							array(
								'taxonomy' => $taxonomy,
								'field'    => 'slug',
								'terms'    => $arrSlug,
							)
						);
				}
				$query = new WP_Query($args);
				ob_start();
			?>
		    <div class="widget widget-hot-stories">
		        <h3><?php echo esc_attr($instance['title']) ?></h3>
		        <ul>
					<?php
						if($query->have_posts()){
							while ( $query->have_posts() ) {
								$query->the_post();
					?>
		            <li>
		                <a href="<?php echo get_permalink( $post->ID );?>">
		                    <span class="topic-avatar">
		                    	<?php echo et_get_avatar($post->post_author, 30) ?>
		                    </span>
		                    <span class="topic-title"><?php echo $post->post_title ?></span>
		                </a>
		            </li>
		            <?php
		        			}
			        	} else {
			        		echo '<li class="no-related">There are no related stories!</li>';
			        	}
			        	wp_reset_query();
			        ?>
		        </ul>
		    </div><!-- END widget-related-tags -->
			<?php
				$stories = ob_get_clean();
				set_transient( 'related_stories_query', $stories, apply_filters( 'forgivingheals_time_expired_transient', 24*60*60 ));
			} else {
				$stories = get_transient( 'related_stories_query' );
			}
			echo $stories;
			//delete_transient( 'related_stories_query' );
		} else {
		?>
		<div class="widget widget-hot-stories">
			<h3><?php echo esc_attr($instance['title']) ?></h3>
			<ul>
				<li>
					<?php _e('This widget should be placed in Single Story Sidebar', ET_DOMAIN) ?>
				</li>
			</ul>
		</div>
		<?php
		}
	}
}//End Related Stories

class ForgivingHeals_Hot_Stories_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to any sidebars to display a list of hot stories.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('story_hot_widget', __('ForgivingHeals Latest Stories / Hot Stories',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		if($new_instance['normal_story'] != $old_instance['normal_story'] || $new_instance['number'] != $old_instance['number']){
			delete_transient( 'hot_stories_query' );
			delete_transient( 'latest_stories_query' );
		}
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('HOT STORIES',ET_DOMAIN) , 'number' => '8', 'date' => '', 'normal_story' => 0) );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>">
				<?php _e('Number of stories to display:', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('normal_story'); ?>">
				<?php _e('Latest stories (sort by date)', ET_DOMAIN) ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id('normal_story'); ?>" name="<?php echo $this->get_field_name('normal_story'); ?>" value="1" type="checkbox" <?php checked( $instance['normal_story'], 1 ); ?> value="<?php echo esc_attr( $instance['normal_story'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('date'); ?>">
				<?php _e('Date range:', ET_DOMAIN) ?>
			</label>
			<select id="<?php echo $this->get_field_id('date'); ?>" name="<?php echo $this->get_field_name('date'); ?>">
				<option <?php selected( $instance['date'], "all" ); ?> value="all">
					<?php _e('All days', ET_DOMAIN) ?>
				</option>
				<option <?php selected( $instance['date'], "last7days" ); ?> value="last7days">
					<?php _e('Last 7 days', ET_DOMAIN) ?>
				</option>
				<option <?php selected( $instance['date'], "last30days" ); ?> value="last30days">
					<?php _e('Last 30 days', ET_DOMAIN) ?>
				</option>
			</select>
		</p>
	<?php
	}

	function widget( $args, $instance ) {

		global $wpdb;
		if(!isset($instance['normal_story'])){

			if(get_transient( 'hot_stories_query' ) === false){
				$hour       = 12;
				$today      = strtotime("$hour:00:00");
				$last7days  = strtotime('-7 day', $today);
				$last30days = strtotime('-30 day', $today);

				if($instance['date'] == "last7days"){
					$custom = "AND post_date >= '".date("Y-m-d H:i:s", $last7days)."' AND post_date <= '".date("Y-m-d H:i:s", $today)."' ";
				} elseif ($instance['date'] == "last30days") {
					$custom = "AND post_date >= '".date("Y-m-d H:i:s", $last30days)."' AND post_date <= '".date("Y-m-d H:i:s", $today)."' ";
				} else {
					$custom = "";
				}

				$query = "SELECT * FROM $wpdb->posts as post
						INNER JOIN $wpdb->postmeta as meta
						ON post.ID = meta.post_id
						AND meta.meta_key  = 'et_reactions_count'
						WHERE post_status = 'publish'
						AND post_type = 'story'
					";

				$query .= $custom;
				$query .="	ORDER BY CAST(meta.meta_value AS SIGNED) DESC,post_date DESC
					LIMIT ".$instance['number']."
					";
				$stories = $wpdb->get_results($query);
				set_transient( 'hot_stories_query', $stories, apply_filters( 'forgivingheals_time_expired_transient', 24*60*60 ));
			} else {
				$stories = get_transient( 'hot_stories_query' );
			}

		} else {

			if(get_transient( 'latest_stories_query' ) === false){

				$query = "SELECT * FROM $wpdb->posts as post
						WHERE post_status = 'publish'
						AND post_type = 'story'
						ORDER BY post_date DESC
						LIMIT ".$instance['number']."
						";

			$stories = $wpdb->get_results($query);
			set_transient( 'latest_stories_query', $stories, apply_filters( 'forgivingheals_time_expired_transient', 24*60*60 ) );

			} else {
				$stories = get_transient( 'latest_stories_query' );
			}
		}
		// delete_transient( 'latest_stories_query' );
		// delete_transient( 'hot_stories_query' );
	?>
    <div class="widget widget-hot-stories">
        <h3><?php echo esc_attr($instance['title']) ?></h3>
        <ul>
			<?php
				foreach ($stories as $story) {
			?>
            <li>
                <a href="<?php echo get_permalink( $story->ID );?>">
                    <span class="topic-avatar">
                    	<?php echo et_get_avatar($story->post_author, 30) ?>
                    </span>
                    <span class="topic-title"><?php echo $story->post_title ?></span>
                </a>
            </li>
            <?php } ?>
        </ul>
    </div><!-- END widget-related-tags -->
	<?php
	}
}//End Class Hot Stories

class ForgivingHeals_Statistic_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to sidebar to display the statistic of website.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('forgivingheals_statistic_widget', __('ForgivingHeals Statistics',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('STATISTICS WIDGET',ET_DOMAIN)) );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
	<?php
	}

	function widget( $args, $instance ) {
		$stories = wp_count_posts('story');
		$result    = count_users();
	?>
    <div class="widget widget-statistic">
    	<p class="stories-count">
    		<?php _e("Stories",ET_DOMAIN) ?><br>
    		<span><?php echo  $stories->publish; ?></span>
    	</p>
    	<p class="members-count">
    		<?php _e("Members",ET_DOMAIN) ?><br>
    		<span><?php echo $result['total_users']; ?></span>
    	</p>
    </div><!-- END widget-statistic -->
	<?php
	}
}

class ForgivingHeals_Tags_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to sidebar to display the list of tags.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('forgivingheals_tags_widget', __('ForgivingHeals Tags',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('Tags Widget',ET_DOMAIN) , 'number' => '8') );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of tag to display:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>
	<?php
	}

	function widget( $args, $instance ) {
		$tags = get_terms( 'forgivingheals_tag', array(
			'hide_empty' => 0 ,
			'orderby' 	 => 'count',
			'order'		 => 'DESC',
			'number'	 => $instance['number']
			));
	?>
    <div class="widget widget-related-tags">
        <h3><?php echo esc_attr($instance['title']) ?></h3>
        <ul>
        	<?php
        		foreach ($tags as $tag) {
        	?>
            <li>
            	<a class="q-tag" href="<?php echo get_term_link( $tag, 'forgivingheals_tag' ); ?>"><?php echo $tag->name ?></a> x <?php echo $tag->count ?>
            </li>
            <?php } ?>
        </ul>
        <a href="<?php echo et_get_page_link('tags') ?>"><?php _e("See more tags", ET_DOMAIN) ?></a>
    </div><!-- END widget-related-tags -->
	<?php
	}
}

class ForgivingHeals_Top_Users_Widget extends WP_Widget{

	function __construct() {
		$widget_ops = array(
			'classname'   => 'widget',
			'description' => __( 'Drag this widget to sidebar to display the list of top users.',ET_DOMAIN )
		);
		$control_ops = array(
			'width'  => 250,
			'height' => 100
		);
		parent::__construct('top_users_widget', __('ForgivingHeals Top Users',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		if( $new_instance['number'] != $old_instance['number'] || $new_instance['orderby'] != $old_instance['orderby'] || $new_instance['latest_users'] != $old_instance['latest_users'] )
			delete_transient( 'top_users_query' );
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'        => __('TOP USERS',ET_DOMAIN) ,
			'number'       => '8',
			'orderby'      => 'point',
			'latest_users' => 0
		));
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of users to display:', ET_DOMAIN) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('latest_users'); ?>">
				<?php _e('Latest users (sort by date)', ET_DOMAIN) ?>
			</label>
			<input class="widefat latest-checkbox" id="<?php echo $this->get_field_id('latest_users'); ?>" name="<?php echo $this->get_field_name('latest_users'); ?>" value="1" type="checkbox" <?php checked( $instance['latest_users'], 1 ); ?> value="<?php echo esc_attr( $instance['latest_users'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order By:', ET_DOMAIN) ?> </label>
			<select class="widefat" <?php disabled( $instance['latest_users'], 1); ?> id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
				<option value="point" <?php selected( esc_attr( $instance['orderby'] ), "point" ); ?>>
					<?php _e( 'Points', ET_DOMAIN ); ?>
				</option>
				<option value="story" <?php selected( esc_attr( $instance['orderby'] ), "story" ); ?>>
					<?php _e( 'Stories', ET_DOMAIN ); ?>
				</option>
				<option value="reaction" <?php selected( esc_attr( $instance['orderby'] ), "reaction" ); ?>>
					<?php _e( 'Reactions', ET_DOMAIN ); ?>
				</option>
			</select>
		</p>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$(document).on('change', 'input#<?php echo $this->get_field_id('latest_users'); ?>', function () {
					if (!this.checked) {
						$("select#<?php echo $this->get_field_id('orderby'); ?>").prop('disabled', false);
					} else {
						$("select#<?php echo $this->get_field_id('orderby'); ?>").prop('disabled', true);
					}
				});
			});
		</script>
	<?php
	}

	function widget( $args, $instance ) {
		global $wpdb;

		$latest_users = isset($instance['latest_users']) && $instance['latest_users'] == 1 ? 1 : 0;
		$widget_id    = $args['widget_id'];

		//top users
		if( !$latest_users ){
			if(get_transient( 'top_users_query_'.$widget_id ) === false){

				$orderby = $instance['orderby'];

				if($orderby == "point"){
					$str_order = "CAST(points AS SIGNED) DESC, CAST(story_count AS SIGNED) DESC, CAST(reaction_count AS SIGNED) DESC";
				} elseif ($orderby == "story") {
					$str_order = "CAST(story_count AS SIGNED) DESC, CAST(points AS SIGNED) DESC, CAST(reaction_count AS SIGNED) DESC";
				} else {
					$str_order = "CAST(reaction_count AS SIGNED) DESC, CAST(points AS SIGNED) DESC, CAST(story_count AS SIGNED) DESC";
				}

				$query =
					"SELECT  user.ID as uid,
							 display_name,
							 points_count.meta_value as points,
							 reaction.meta_value as reaction_count,
							 story.meta_value as story_count

					FROM $wpdb->users as user

					INNER JOIN $wpdb->usermeta as reaction
						 ON user.ID = reaction.user_id
						 AND reaction.meta_key  = 'et_reaction_count'

					INNER JOIN $wpdb->usermeta as story
						 ON user.ID = story.user_id
						 AND story.meta_key = 'et_story_count'

					INNER JOIN $wpdb->usermeta as points_count
						 ON user.ID = points_count.user_id
						 AND points_count.meta_key = 'forgivingheals_point'

					GROUP BY user.ID

					ORDER BY ".$str_order."

					LIMIT ".$instance['number'];

				$users = $wpdb->get_results($query);
				set_transient( 'top_users_query', $users, apply_filters( 'forgivingheals_time_expired_transient', 24*60*60 ) );
			} else {
				$users = get_transient( 'top_users_query_'.$widget_id );
			}
		} else {
			if(get_transient( 'latest_users_query_'.$widget_id ) === false){
				$users = get_users( array(
					'orderby' => 'registered',
					'number'  => $instance['number'],
					'order'   => 'DESC'
					) );
			} else {
				$users = get_transient( 'latest_users_query_'.$widget_id );
			}
		}
		// delete_transient( 'top_users_query' );
		// delete_transient( 'latest_users_query' );
	?>
	<div class="widget user-widget">
		<h3 class="widgettitle"><?php echo esc_attr($instance['title']) ?></h3>
	    <div class="hot-user-story">
	    	<ul>
            <?php
            	$i = 1;
            	foreach ($users as $user) {
            		$uid = isset($user->uid) ? $user->uid : $user->ID;
            ?>
	        	<li>
                    <span class="number"><?php echo $i ?></span>
                    <span class="username <?php echo $latest_users ? 'latest' : ''; ?>">
                    	<a href="<?php echo get_author_posts_url($uid); ?>" title="<?php echo $user->display_name ?>">
                    		<?php echo $user->display_name ?>
                    	</a>
                    </span>
                    <?php
                    	if( !$latest_users ){
                    		if( $instance['orderby'] == "story" ){
                    ?>
                    <span class="stories-count" title="<?php printf( __('%d Story(s)'), $user->story_count > 0 ? $user->story_count : 0 ) ?>">
                    	<i class="fa fa-story-circle"></i>
                    	<span><?php echo $user->story_count > 0 ? custom_number_format($user->story_count) : 0 ?></span>
                    </span>
                    <?php
	               			} else if( $instance['orderby'] == "reaction" ) {
	                ?>
                    <span class="reactions-count" title="<?php printf( __('%d Reaction(s)'), $user->reaction_count > 0 ? $user->reaction_count : 0 ) ?>">
                    	<i class="fa fa-comments"></i>
                    	<span><?php echo $user->reaction_count > 0 ? custom_number_format($user->reaction_count) : 0 ?></span>
                    </span>
                    <?php 	} else { ?>
                    <span class="points-count" title="<?php printf( __('%d Point(s)'), $user->points > 0 ? $user->points : 0 ) ?>">
                    	<i class="fa fa-star"></i>
                    	<span><?php echo $user->points > 0 ? custom_number_format($user->points) : 0 ?></span>
                    </span>
                    <?php
                			}
                		}//end if latest
                	?>
	            </li>
	        <?php $i++;} ?>
	        </ul>
	    </div>
	</div>
	<?php
	}
}

/**
 * ForgivingHeals_Recent_Activity widget class
 *
 * @since 1.0
 */
class ForgivingHeals_Recent_Activity extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget', 'description' => __( 'Drag this widget to sidebar to display the list of user\'s activities.',ET_DOMAIN) );
		$control_ops = array('width' => 250, 'height' => 100);
		parent::__construct('forgivingheals_recent_activity', __('ForgivingHeals Recent Activities',ET_DOMAIN) , $widget_ops ,$control_ops );
	}

	function update ( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' , 'number' => '8') );

		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', ET_DOMAIN) ?> </label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of activities to display:', ET_DOMAIN) ?> </label>
				<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
			</p>
		<?php

	}

	function widget( $args, $instance ) {
		global $user_ID;
		$param = array();

		if( !$user_ID ) return;

		if(isset($instance['number']) && $instance['number']) {
			$param['showposts']	=	$instance['number'];
		}

		?>
		<div class="widget widget-recent-activity">
			<?php if(esc_attr($instance['title']) != "" ){ ?>
				<h3><?php echo esc_attr($instance['title']) ?></h3>
			<?php }
			if(!get_transient( 'forgivingheals_changelog_'.$user_ID )) {
				ob_start();
				$content	=	forgivingheals_list_changelog($param);
				$content	=	ob_get_clean();
				set_transient( 'forgivingheals_changelog_'.$user_ID , $content, 300 );
			} else {
				$content	=	get_transient( 'forgivingheals_changelog_'.$user_ID );
			}
			echo $content;
		?>
		</div><!-- END widget-recent-activities -->

		<?php
	}
}