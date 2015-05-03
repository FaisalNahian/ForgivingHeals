<?php
/**
 * Template Name: Pending Stories Template
 * version 1.0
 * @author: MaxWeb
 **/
get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="row select-category">
            <div class="col-md-6 col-xs-6 current-category">
                <span><?php _e("All Stories", ET_DOMAIN ); ?></span>
            </div>
            <div class="col-md-6 col-xs-6">
                <?php forgivingheals_tax_dropdown() ?>
            </div>            
        </div><!-- END SELECT-CATEGORY -->
        <?php forgivingheals_template_filter_stories(); ?>
        <div class="main-stories-list">
            <ul id="main_stories_list">
                <?php
                    $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
                    
                    $args  = array(
                            'post_type'     => 'story',
                            'paged'         => $paged,
                            'post_status'   => 'pending'
                        );

                    $query = ForgivingHeals_Stories::get_stories($args);

                    if($query->have_posts()){
                        while($query->have_posts()){
                            $query->the_post();
                            get_template_part( 'template/pending-story', 'loop' );
                        }
                    }
                    wp_reset_query();
                ?>                                                                                             
            </ul>
        </div><!-- END MAIN-STORIES-LIST -->
        <div class="row paginations home">
            <div class="col-md-12">
                <?php 
                    forgivingheals_template_paginations($query, $paged);
                ?>                 
            </div>
        </div><!-- END MAIN-PAGINATIONS -->      
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>