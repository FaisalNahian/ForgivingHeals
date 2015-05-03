<?php
/**
 * Template: BLOG LISTING AUTHOR
 * version 1.0
 * @author: ThaiNT
 **/
	et_get_mobile_header();
global $wp_query, $wp_rewrite, $current_user;

$user = get_user_by( 'id', get_query_var( 'author' ) );
$user = ForgivingHeals_Member::convert($user);
?>
<!-- CONTAINER -->
<div class="wrapper-mobile">
	<!-- TOP BAR -->
	<section class="profile-user-wrapper">
    	<div class="container">
            <div class="row">
                <div class="col-md-3 col-xs-3 padding-right-0">
                    <a href="javascript:void(0);" class="profile-avatar">
                        <?php echo et_get_avatar( $user->ID, 65); ?>
                    </a>
                </div>
                <div class="col-md-9 col-xs-9">
                    <div class="profile-wrapper">
                    	<span class="user-name-profile"><?php echo esc_attr( $user->display_name );  ?></span>
                    	<span class="address-profile">
                            <?php
                                if( $user->user_location ) {
                                    echo '<i class="fa fa-map-marker"></i>' .esc_attr( $user->user_location )  ;
                                } else {
                                    echo '<i class="fa fa-globe"></i>' . __("Earth", ET_DOMAIN)  ;
                                }
                            ?>
                        </span>
                        <span class="email-profile">
                            <i class="fa fa-envelope"></i>
                            <?php echo $user->show_email == "on" ? $user->user_email : __('Email is hidden.', ET_DOMAIN); ?>
                        </span>
                        <div class="clearfix"></div>
                        <div class="description">
                            <?php echo nl2br(esc_attr($user->description)); ?>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-8 col-xs-8 padding-right-0">
                	<div class="list-bag-profile-wrapper">
                        <?php forgivingheals_user_badge($user->ID); ?>
                        <span class="point-profile">
                            <span>
                                <?php echo forgivingheals_get_user_point($user->ID) ? forgivingheals_get_user_point($user->ID) : 0 ?>
                                <i class="fa fa-star"></i>
                            </span><?php _e("points", ET_DOMAIN) ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 col-xs-4 padding-left-0">
                	 <div class="list-bag-profile-wrapper text-right">
                         <span class="story-profile">
                            <?php echo et_count_user_posts($user->ID) ?><i class="fa fa-story-circle"></i>
                        </span>
                         <span class="reactions-profile">
                            <?php echo et_count_user_posts($user->ID, "reaction") ?><i class="fa fa-comments"></i>
                        </span>
                     </div>
                </div>
            </div>
        </div>
    </section>
    <!-- TOP BAR / END -->

    <!-- MIDDLE BAR -->
    <section class="middle-bar bg-white">
    	<div class="container">
            <div class="row">
            	<div class="col-md-12">
                	<ul class="menu-middle-bar">
                        <li class="<?php if(!isset($_GET['type'])) echo 'active'; ?>" >
                            <a href="<?php echo get_author_posts_url($user->ID); ?>"><?php _e('Stories',ET_DOMAIN) ?></a>
                        </li>
                        <li class="<?php if(isset($_GET['type']) && $_GET['type'] == "reaction") echo 'active'; ?>" >
                            <a href="<?php echo add_query_arg(array('type'=>'reaction')); ?>"><?php _e('Reactions',ET_DOMAIN) ?></a>
                        </li>
                        <?php if($current_user->ID == $user->ID){ ?>
                        <li class="<?php if(isset($_GET['type']) && $_GET['type'] == "following") echo 'active'; ?>">
                            <a href="<?php echo add_query_arg(array('type'=>'following')); ?>"><?php _e('Following',ET_DOMAIN) ?></a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
    		</div>
        </div>
        <div class="form-search-wrapper">
        	<form id="form-search" class="collapse">
            	<a href="javascript:void(0);" class="clear-text-search"><i class="fa fa-times-circle"></i></a>
                <a href="javascript:void(0);" class="close-form-search"><?php _e('Cancel', ET_DOMAIN) ?></a>
            	<input type="text" name="" id="" placeholder="<?php _e('Enter keyword',ET_DOMAIN) ?>" class="form-input-search">
            </form>
        </div>
    </section>
    <!-- MIDDLE BAR / END -->

    <!-- LIST STORY -->
    <section class="list-story-wrapper">
    	<div class="container">
            <div class="row">
            	<div class="col-md-12">
                	<ul class="list-story <?php if(isset($_GET['type']) && $_GET['type'] == "post") echo 'list-posts'; ?>">
                    <?php
                        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

                        $type = isset($_GET['type']) ? $_GET['type'] : 'story';

                        $args       = array(
                            'post_type' => $type,
                            'paged'     => $paged,
                            'author'    => $user->ID
                        );
                        //show pending story if current is author
                        if($current_user->ID == $user->ID){
                            $args['post_status'] = array('publish', 'pending');
                        }
                        //tab following stories
                        if(isset($_GET['type']) && $_GET['type'] == "following"){
                            $follow_stories  = array_filter( (array) get_user_meta( $user->ID, 'forgivingheals_following_stories', true ) );
                            $args['post_type'] = $type = "story";
                            $args['post__in']  = !empty($follow_stories) ? $follow_stories : array(0);
                            unset($args['author']);
                        }

                        $query = ForgivingHeals_Stories::get_stories($args);

                        if($query->have_posts()){
                            while($query->have_posts()){
                                $query->the_post();
                                get_template_part( 'mobile/template/'.$type, 'loop' );
                            }
                        } else {
                            echo '<li class="no-stories">';
                            echo '<h2>'.__('There are no stories yet.', ET_DOMAIN).'</h2>';
                            echo '</li>';
                        }
                        wp_reset_query();
                    ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- LIST STORY / END -->
    <section class="list-pagination-wrapper">
        <?php
            forgivingheals_template_paginations($query, $paged);
        ?>
    </section>
    <!-- PAGINATIONS STORY / END -->
</div>
<!-- CONTAINER / END -->
<?php
	et_get_mobile_footer();
?>