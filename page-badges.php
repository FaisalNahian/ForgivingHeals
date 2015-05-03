<?php
/**
 * Template Name: Badges List Template
 * version 1.0
 * @author: MaxWeb
 **/
get_header();

$badge_points = forgivingheals_get_badge_point();
$levels       = forgivingheals_get_privileges();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="row select-category">
            <div class="col-md-12 current-category">
                <span><?php _e("Badges", ET_DOMAIN) ?></span>
            </div>          
        </div><!-- END SELECT-CATEGORY -->
        <div class="row points-system">
            <div class="col-md-12">
                <h3><?php _e("Points System", ET_DOMAIN) ?></h3>
                <p><?php _e("You earn reputation when people vote on your posts", ET_DOMAIN) ?></p>
            </div>
            <div class="clearfix"></div>
            <ul class="points-define">
                    <li class="col-md-3">
                        <div>
                            <span class="points-count">
                                +<?php echo $badge_points->create_story ? $badge_points->create_story : 0  ?>
                            </span>
                            <span class="star">
                                <i class="fa fa-star"></i><br>
                                <?php _e("create a story", ET_DOMAIN) ?>
                            </span>
                        </div>
                    </li>             
                    <li class="col-md-3">
                        <div>
                            <span class="points-count">
                                +<?php echo $badge_points->q_vote_up ? $badge_points->q_vote_up : 0  ?>
                            </span>
                            <span class="star">
                                <i class="fa fa-star"></i><br>
                                <?php _e("story is voted up", ET_DOMAIN) ?>
                            </span>
                        </div>
                    </li>    
                    <li class="col-md-3">    
                        <div>
                            <span class="points-count">
                                +<?php echo $badge_points->a_vote_up ? $badge_points->a_vote_up : 0  ?>
                            </span>
                            <span class="star">
                                <i class="fa fa-star"></i><br>
                                <?php _e("reaction is voted up", ET_DOMAIN) ?>
                            </span>
                        </div>
                    </li> 
                    <li class="col-md-3">    
                        <div>
                            <span class="points-count">
                                +<?php echo $badge_points->a_accepted ? $badge_points->a_accepted : 0  ?>
                            </span>
                            <span class="star">
                                <i class="fa fa-star"></i><br>
                                <?php _e("reaction is accepted", ET_DOMAIN) ?>
                            </span>
                        </div>
                    </li>                                                    
            </ul>
            
        </div><!-- END POINTS-SYSTEM -->
        <div class="row badges-system">
            <div class="col-md-12">
                <h3><?php _e("Badges System", ET_DOMAIN) ?></h3>
                <p><?php _e("You earn reputation when people vote on your posts", ET_DOMAIN) ?></p>
            </div>
            <?php
                $badges     =   ForgivingHeals_Pack::query(array());
                while( $badges->have_posts() ) { $badges->the_post();
                    global $post;
                    $pack        =  ForgivingHeals_Pack::forgivingheals_convert($post);
            ?>
            <div class="col-md-12 badge-content">
                <div class="border">
                    <div class="col-md-3 story-cat">
                        <span class="user-badge" style="background:<?php echo $pack->forgivingheals_badge_color ?>;">
                            <?php echo $pack->post_title ?>
                        </span><br>
                        <span class="points-count">
                            <?php echo $pack->forgivingheals_badge_point ?>
                        </span>
                        <span class="star">
                            <i class="fa fa-star"></i><br>
                            <?php _e("points require", ET_DOMAIN) ?>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <span><?php _e("With you can do:", ET_DOMAIN) ?></span>
                        <p>
                            <i class="fa fa-<?php echo $pack->forgivingheals_badge_point >= $levels->edit_story ? 'check' : 'ban' ?>"></i>
                            <?php _e("Edit other people's stories", ET_DOMAIN) ?>
                        </p>
                        <p>
                            <i class="fa fa-<?php echo $pack->forgivingheals_badge_point >= $levels->add_comment ? 'check' : 'ban' ?>"></i>
                            <?php _e("Vote to close, reopen, or migrate stories", ET_DOMAIN) ?>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p>
                            <i class="fa fa-<?php echo $pack->forgivingheals_badge_point >= $levels->edit_reaction ? 'check' : 'ban' ?>"></i>
                            <?php _e("Edit other people's reactions", ET_DOMAIN) ?>
                        </p>
                        <p>
                            <i class="fa fa-<?php echo $pack->forgivingheals_badge_point >= $levels->vote_down ? 'check' : 'ban' ?>"></i>
                            <?php _e("Vote down (costs 1 point on reactions)", ET_DOMAIN) ?>
                        </p>                    
                    </div>
                    <div class="col-md-2">
                        <p>
                            <i class="fa fa-<?php echo $pack->forgivingheals_badge_point >= $levels->add_comment ? 'check' : 'ban' ?>"></i>
                            <?php _e("Leave comments", ET_DOMAIN) ?>
                        </p>
                        <p>
                            <i class="fa fa-<?php echo $pack->forgivingheals_badge_point >= $levels->vote_up ? 'check' : 'ban' ?>"></i>
                            <?php _e("Vote up", ET_DOMAIN) ?>
                        </p>                  
                    </div>
                </div>
            </div>
            <?php 
                }
                wp_reset_query();
            ?>                        
        </div><!-- END BADGES-SYSTEM -->     
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>